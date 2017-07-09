<?php

require __DIR__ . '/vendor/autoload.php';
include 'config.php';

$client = new \GuzzleHttp\Client();
$response = $client->get($config['youtube_channel']);
preg_match_all("/\/watch\?v=([\w\-]+)/", $response->getBody(), $matches);
$videos = array_reverse(array_unique($matches[1]));

$db = new \App\DatabaseManager($config['mysql_dsn'], $config['mysql_username'], $config['mysql_password']);

foreach ($videos as $video) {
    $db->addVideoIfNotExists($video);
}

$requests = [];
foreach ($db->getVideos() as $video) {
    $requests[$video['id']] = new \GuzzleHttp\Psr7\Request('GET', 'https://www.youtube.com/watch?v=' . $video['v']);
}

$pool = new \GuzzleHttp\Pool(
    $client,
    $requests,
    [
        'concurrency' => $config['guzzle_concurrency'],
        'fulfilled' => function (\GuzzleHttp\Psr7\Response $response, $id) use ($db) {
            if ($response->getStatusCode() != 200) {
                return;
            }

            $finder = new \App\TextFinder($response->getBody());
            $views = $finder->findNumberByClass('watch-view-count');
            $likes = $finder->findNumberByClass('like-button-renderer-like-button-unclicked');
            $dislikes = $finder->findNumberByClass('like-button-renderer-dislike-button-unclicked');

            $db->addVideoStat($id, $views, $likes, $dislikes);
        }
    ]
);

$promise = $pool->promise();
$promise->wait(true);

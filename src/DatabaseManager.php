<?php

namespace App;

class DatabaseManager
{
    private $pdo;

    public function __construct($dsn, $username, $password)
    {
        $this->pdo = new \PDO($dsn, $username, $password);
    }

    public function addVideoIfNotExists($v)
    {
        $query = $this->pdo->prepare("SELECT v FROM videos WHERE v = :v");
        $query->execute(['v' => $v]);

        if ($query->rowCount() === 0) {
            $this
                ->pdo
                ->prepare("INSERT INTO videos (`v`, `created_at`) VALUES (:v, :created)")
                ->execute(['v' => $v, 'created' => date('Y-m-d H:i:s')]);
        }
    }

    public function getVideos()
    {
        $query = $this->pdo->prepare("SELECT id, v FROM videos");
        $query->execute();

        return $query->fetchAll();
    }

    public function addVideoStat($id, $views, $likes, $dislikes)
    {
        $query = $this->pdo->prepare(
            "INSERT INTO stats (`video_id`, `views`, `likes`, `dislikes`, `created_at`) VALUES " .
            "(:video_id, :views, :likes, :dislikes, :created)"
        );

        $query->execute([
            'video_id' => $id,
            'views' => $views,
            'likes' => $likes,
            'dislikes' => $dislikes,
            'created' => date('Y-m-d H:i:s')
        ]);
    }
}

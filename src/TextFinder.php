<?php

namespace App;

class TextFinder
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function findNumberByClass($class)
    {
        $offset = stripos($this->text, $class);

        if ($offset === false) {
            return '';
        }

        $text = substr($this->text, $offset, stripos($this->text, '</', $offset) - $offset);
        $text = substr($text, strripos($text, '>') + 1);

        $text = preg_replace("/[^0-9]/", "", $text);

        return $text;
    }
}
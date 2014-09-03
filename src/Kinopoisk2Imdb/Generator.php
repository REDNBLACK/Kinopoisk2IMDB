<?php
namespace Kinopoisk2Imdb;

class Generator
{
    public $lol;

    public function __construct($lol)
    {
        $this->lol = $lol;
    }

    public function generate()
    {
        return $this->lol;
    }
}

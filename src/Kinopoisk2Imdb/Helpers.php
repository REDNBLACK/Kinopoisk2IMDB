<?php
namespace Kinopoisk2Imdb;

define('DIRECTORY_UP', '..');
class Helpers
{
    protected $dir;

    public function __construct()
    {
        $this->dir = implode(DIRECTORY_SEPARATOR, [__DIR__, DIRECTORY_UP, DIRECTORY_UP]);
    }
} 

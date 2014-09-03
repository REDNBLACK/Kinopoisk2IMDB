<?php
ini_set('error_reporting', -1);
ini_set('display_errors', 'On');
require_once 'vendor/autoload.php';
$url = new \Kinopoisk2Imdb\Generator('well');
var_dump($url->generate());

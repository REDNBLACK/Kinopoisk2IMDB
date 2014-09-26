#!/usr/bin/env php
<?php
require_once '../vendor/autoload.php';

use Kinopoisk2Imdb\Console\Command\Kinopoisk2Imdb;
use Symfony\Component\Console\Application;

$application = new Application('Kinopoisk 2 IMDB Application', '0.3a');
$application->add(new Kinopoisk2Imdb());
$application->run();

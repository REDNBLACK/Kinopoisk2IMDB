#!/usr/bin/env php
<?php
require_once '../vendor/autoload.php';

use Kinopoisk2Imdb\Tool\Kinopoisk2Imdb;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new Kinopoisk2Imdb());
$application->run();

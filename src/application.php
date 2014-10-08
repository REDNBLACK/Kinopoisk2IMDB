#!/usr/bin/env php
<?php
require_once '../vendor/autoload.php';

use Kinopoisk2Imdb\Console\Tool\Kinopoisk2ImdbApplication;

$application = new Kinopoisk2ImdbApplication('Kinopoisk 2 IMDB Application', '0.5a');
$application->run();

<?php
define('BASE_DIR', __DIR__ . '/../');
mb_internal_encoding('UTF-8');

use Kinopoisk2Imdb\Console\Tool\Kinopoisk2ImdbApplication;

$autoloaders = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];

foreach ($autoloaders as $file) {
    if (file_exists($file)) {
        require_once $file;
        break;
    }
}

$application = new Kinopoisk2ImdbApplication('Kinopoisk 2 IMDB Application', '0.5');
$application->run();

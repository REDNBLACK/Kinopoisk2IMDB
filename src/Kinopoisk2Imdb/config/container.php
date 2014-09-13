<?php
use Pimple\Container;

$container = new Container();

$container['fs'] = function ($c) {
    return new \Kinopoisk2Imdb\Filesystem();
};

$container['parser'] = function ($c) {
    return new \Kinopoisk2Imdb\Parser();
};

return $container;

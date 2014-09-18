<?php
ini_set('error_reporting', -1);
ini_set('display_errors', 'On');
require_once 'vendor/autoload.php';
$container = require_once 'src/Kinopoisk2Imdb/config/container.php';

/* Generator */
//$generator = new \Kinopoisk2Imdb\Generator('file.xls');
//var_dump($generator->init());

/* Resource Manager */
//$resource = new \Kinopoisk2Imdb\ResourceManager('file.json');
//$resource->init();
//var_dump($resource->fs->getData());
//
//var_dump($resource->getOneRow());
//var_dump($resource->removeOneRow());
//var_dump($resource->getOneRow());
//var_dump($resource->removeOneRow());
//
//var_dump($resource->getSettings());
//var_dump($resource->getSettings('filesize'));
//
//var_dump($resource->fs->getData());

/* Client */
$client = new \Kinopoisk2Imdb\Client();
$client->searchMovie('Frozen', '2013');
var_dump($client->parser->parseImdbMovieSearchResult($client->data));
var_dump($client->parser->getData());

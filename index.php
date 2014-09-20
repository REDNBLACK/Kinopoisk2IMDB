<?php
ini_set('error_reporting', -1);
ini_set('display_errors', 'On');
require_once 'vendor/autoload.php';

/* Generator */
//$generator = new \Kinopoisk2Imdb\Generator('file.xls');
//var_dump($generator->init());

/* Resource Manager */
//$resource = new \Kinopoisk2Imdb\ResourceManager('file.json');
//$resource->init();
//
//var_dump($resource->getAllRows());
//var_dump($resource->getOneRow(true));
//var_dump($resource->getOneRow(true));
//
//var_dump($resource->getSettings());
//var_dump($resource->getSettings('filesize'));
//
//var_dump($resource->getAllRows());

/* Client */
$params = [
    'auth' => '**REMOVED**',
    'file' => 'file.xls'
];
$client = new \Kinopoisk2Imdb\Client($params);
//var_dump($client->wrapperSubmitMovieRating('Frozen', '2013', 10));
//var_dump($client->addToWatchlist('Law Abiding Citizen', '2009', 'ls075665398'));
var_dump($client->submitRatingAndAddToWatchlist('The Boondock Saints', '1999', 10, 'ls075665398'));

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
    'file' => 'file.xls',
    'list_id' => 'ls075660982',
    'mode' => \Kinopoisk2Imdb\Config\Config::MODE_ALL,
    'compare' => \Kinopoisk2Imdb\Config\Config::COMPARE_SMART,
    'query_format' => \Kinopoisk2Imdb\Config\Config::QUERY_FORMAT_XML
];
//$data = [
//    'title'   => 'The Boondock Saints',
//    'year'    => '1999',
//    'rating'  => 10,
//    'list_id' => 'ls075665398'
//];
$client = new \Kinopoisk2Imdb\Client($params);
$client->init();
//var_dump($client->submit($data, \Kinopoisk2Imdb\Config\Config::MODE_ALL));

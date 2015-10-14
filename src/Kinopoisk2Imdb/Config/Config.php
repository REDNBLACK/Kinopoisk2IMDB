<?php
namespace Kinopoisk2Imdb\Config;

class Config
{
    const SCRIPT_EXECUTION_LIMIT = 0;
    const DELAY_BETWEEN_REQUESTS = 1;

    const QUERY_FORMAT_XML = 'xml';
    const QUERY_FORMAT_JSON = 'json';
    const QUERY_FORMAT_MIXED = 'mixed';

    const MOVIE_TITLE = 'title';
    const MOVIE_YEAR = 'year';
    const MOVIE_RATING = 'rating';
    const MOVIE_LIST_ID = 'list_id';
}

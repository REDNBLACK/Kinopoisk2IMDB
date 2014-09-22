<?php
namespace Kinopoisk2Imdb\Config;

class Config
{
    const BASE_IMDB_URL = 'http://www.imdb.com';
    const CURL_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36';

    const DEFAULT_DIR = 'data';
    const DEFAULT_NEW_FILE_EXT = '.json';

    const MODE_ALL = 3;
    const MODE_LIST_ONLY = 1;
    const MODE_RATING_ONLY = 2;

    const MOVIE_TITLE = 'title';
    const MOVIE_YEAR = 'year';
    const MOVIE_RATING = 'rating';

    public static $imdbLinks = [
        'search_for_movie'       => 'http://www.imdb.com/xml/find?',
        'movie_page'             => 'http://www.imdb.com/title/',
        'change_movie_rating'    => 'http://www.imdb.com/ratings/_ajax/title',
        'add_movie_to_watchlist' => 'http://www.imdb.com/list/_ajax/edit'
    ];
}

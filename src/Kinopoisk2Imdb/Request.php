<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Config\Config;
use Kinopoisk2Imdb\Methods\CurlHttpRequest;

/**
 * Class Request
 * @package Kinopoisk2Imdb
 */
class Request
{
    /**
     * User Agent for cURL
     */
    const CURL_USER_AGENT = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36';

    /**
     * @var array User IMDB auth string
     */
    private $auth;

    /**
     * @var CurlHttpRequest
     */
    private $httpRequest;

    /**
     * Links for requests
     * @var array
     */
    private static $imdbLinks = [
        'search_for_movie'       => 'http://www.imdb.com/xml/find?',
        'movie_page'             => 'http://www.imdb.com/title/',
        'change_movie_rating'    => 'http://www.imdb.com/ratings/_ajax/title',
        'add_movie_to_watchlist' => 'http://www.imdb.com/list/_ajax/edit'
    ];

    /**
     * Constructor
     */
    public function __construct($auth)
    {
        $this->auth = ['id' => $auth];
    }

    /**
     * Basic setup of the class
     * @return CurlHttpRequest
     */
    public function setupHttpRequest()
    {
        $this->httpRequest = new CurlHttpRequest();
        $this->httpRequest
            ->setUserAgent(self::CURL_USER_AGENT)
            ->setOptions([
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HEADER         => false
            ])
        ;

        return $this->httpRequest;
    }

    /**
     * Method for searching movie title with the specified query format
     * @param  string $title
     * @param  string $year
     * @return string
     */
    public function searchMovie($title, $year, $query_format)
    {
        $query_format = ($query_format === Config::QUERY_FORMAT_JSON ? 1 : 0);
        $query = [
            'q'    => $title,      // Запрос
            'tt'   => 'on',        // Поиск только по названиям
            'json' => $query_format, // В каком формате выводить. 1 - JSON, 0 - XML
            'nr'   => 1
        ];

        $response = $this->setupHttpRequest()
            ->setUrl(self::$imdbLinks['search_for_movie'], $query)
            ->execute()
            ->close()
            ->getResponse()
        ;

        return [
            Config::MOVIE_TITLE => $title,
            Config::MOVIE_YEAR  => $year,
            'structure'         => $response
        ];
    }

    /**
     * Get contents of page with specified movie ID
     * @param  string $movie_id
     * @return mixed
     */
    public function openMoviePage($movie_id)
    {
        return $this->setupHttpRequest()
            ->setUrl(self::$imdbLinks['movie_page'], $movie_id)
            ->setCookies($this->auth)
            ->execute()
            ->close()
            ->getResponse()
        ;
    }

    /**
     * Method for changing rating of movie with specified ID to int/string number from 1 to 10
     * @param  string     $movie_id
     * @param  int|string $rating
     * @param  string     $movie_auth
     * @return mixed
     */
    public function changeMovieRating($movie_id, $rating, $movie_auth)
    {
        $post_data = [
            'tconst'       => $movie_id,           // ID фильма
            'rating'       => $rating,             // Рейтинг
            'auth'         => $movie_auth,         // ID авторизации фильма
            'tracking_tag' => 'title-maindetails', // Тэг для трекинга не меняется
            'pageId'       => $movie_id,           // ID страницы (совпадает с ID фильма)
            'pageType'     => 'title',             // Реферер не меняется
            'subpageType'  => 'main'               // Тип страницы не меняется
        ];

        return $this->setupHttpRequest()
            ->setUrl(self::$imdbLinks['change_movie_rating'])
            ->setType('POST', $post_data)
            ->setCookies($this->auth)
            ->execute()
            ->close()
            ->getResponse()
        ;
    }

    /**
     * Add movie with specified ID to the watchlist with specified ID
     * @param  string $movie_id
     * @param  string $list_id
     * @return mixed
     */
    public function addMovieToWatchList($movie_id, $list_id)
    {
        $post_data = [
            'const'   => $movie_id, // ID фильма
            'list_id' => $list_id,  // ID списка для добавления
            'ref_tag' => 'title'    // Реферер не меняется
        ];

        return $this->setupHttpRequest()
            ->setUrl(self::$imdbLinks['add_movie_to_watchlist'])
            ->setType('POST', $post_data)
            ->setCookies($this->auth)
            ->execute()
            ->close()
            ->getResponse()
        ;
    }
}

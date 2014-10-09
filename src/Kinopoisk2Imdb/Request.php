<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Config\Config;
use Kinopoisk2Imdb\Methods\HttpRequestMethods;

/**
 * Class Request
 * @package Kinopoisk2Imdb
 */
class Request
{
    /**
     * @var array User IMDB auth string
     */
    private $auth;

    /**
     * @var HttpRequestMethods
     */
    private $httpRequest;

    /**
     * Constructor
     */
    public function __construct($auth)
    {
        $this->auth = ['id' => $auth];
    }

    /**
     * Basic setup of the class
     * @return HttpRequestMethods
     */
    public function setupHttpRequest()
    {
        $this->httpRequest = new HttpRequestMethods();
        $this->httpRequest
            ->setUserAgent(Config::CURL_USER_AGENT)
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
     * @param string $title
     * @param string $year
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
            ->setUrl(Config::$imdbLinks['search_for_movie'], $query)
            ->execute()
            ->close()
            ->getResponse()
        ;

        return [
            Config::MOVIE_TITLE => $title,
            Config::MOVIE_YEAR  => $year,
            'structure' => $response
        ];
    }

    /**
     * Get contents of page with specified movie ID
     * @param string $movie_id
     * @return mixed
     */
    public function openMoviePage($movie_id)
    {
        return $this->setupHttpRequest()
            ->setUrl(Config::$imdbLinks['movie_page'], $movie_id)
            ->setCookies($this->auth)
            ->execute()
            ->close()
            ->getResponse()
        ;
    }

    /**
     * Method for changing movie rating
     * @param string $movie_id
     * @param string $rating
     * @return mixed
     */
    public function changeMovieRating($movie_id, $rating, $auth)
    {
        $post_data = [
            'tconst'       => $movie_id,           // ID фильма
            'rating'       => $rating,             // Рейтинг
            'auth'         => $auth,               // Куки авторизации
            'tracking_tag' => 'title-maindetails', // Тэг для трекинга не меняется
            'pageId'       => $movie_id,           // ID страницы (совпадает с ID фильма)
            'pageType'     => 'title',             // Реферер не меняется
            'subpageType'  => 'main'               // Тип страницы не меняется
        ];

        return $this->setupHttpRequest()
            ->setUrl(Config::$imdbLinks['change_movie_rating'])
            ->setType('POST', $post_data)
            ->setCookies($this->auth)
            ->execute()
            ->close()
            ->getResponse()
        ;
    }

    /**
     * Add movie with specified ID to the watchlist with specified ID
     * @param string $movie_id
     * @param string $list_id
     * @return mixed
     */
    public function addMovieToWatchList($movie_id, $list_id)
    {
        $post_data = [
            'const' => $movie_id,   // ID фильма
            'list_id' => $list_id,  // ID списка для добавления
            'ref_tag' => 'title'    // Реферер не меняется
        ];

        return $this->setupHttpRequest()
            ->setUrl(Config::$imdbLinks['add_movie_to_watchlist'])
            ->setType('POST', $post_data)
            ->setCookies($this->auth)
            ->execute()
            ->close()
            ->getResponse()
        ;
    }
}

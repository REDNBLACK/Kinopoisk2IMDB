<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Config\Config;

/**
 * Class Request
 * @package Kinopoisk2Imdb
 */
class Request
{
    /**
     * cURL GET method constant
     */
    const CURL_METHOD_GET = 'GET';
    /**
     * cURL POST method constant
     */
    const CURL_METHOD_POST = 'POST';

    /**
     * @var array User IMDB auth string
     */
    private $auth;

    /**
     * Constructor
     */
    public function __construct($auth)
    {
        $this->auth = ['id' => $auth];
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
        $query = http_build_query([
            'q'    => $title,      // Запрос
            'tt'   => 'on',        // Поиск только по названиям
            'json' => $query_format, // В каком формате выводить. 1 - JSON, 0 - XML
            'nr'   => 1
        ]);

        $data = [
            Config::MOVIE_TITLE => $title,
            Config::MOVIE_YEAR  => $year,
            'structure' => $this->fetchUrlByCurl(Config::$imdbLinks['search_for_movie'] . $query)
        ];

        return $data;
    }

    /**
     * Get contents of page with specified movie ID
     * @param string $movie_id
     * @return mixed
     */
    public function openMoviePage($movie_id)
    {
        return $this->fetchUrlByCurl(
            Config::$imdbLinks['movie_page'] . $movie_id, self::CURL_METHOD_GET, $this->auth
        );
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

        return $this->fetchUrlByCurl(
            Config::$imdbLinks['change_movie_rating'], self::CURL_METHOD_POST, $this->auth, $post_data
        );
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

        return $this->fetchUrlByCurl(
            Config::$imdbLinks['add_movie_to_watchlist'], self::CURL_METHOD_POST, $this->auth, $post_data
        );
    }

    /**
     * Method for sending request and getting response using cURL
     * @param string $url
     * @param string $method
     * @param array $cookies
     * @param array $post_data
     * @param array $add_headers
     * @param string $user_agent
     * @return mixed
     */
    public function fetchUrlByCurl(
        $url,
        $method = self::CURL_METHOD_GET,
        array $cookies = [],
        array $post_data = [],
        array $add_headers = [],
        $user_agent = Config::CURL_USER_AGENT
    ) {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER         => false,
            CURLOPT_USERAGENT      => $user_agent,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $add_headers,
        ];

        // Добавляем куки
        if (!empty($cookies)) {
            $options[CURLOPT_COOKIE] = $this->httpBuildCookie($cookies);
        }

        // Добавляем POST данные
        if ($method === self::CURL_METHOD_POST) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($post_data);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    /**
     * Build cookie string from the specified associative array
     * @param array $data
     * @return string
     */
    public function httpBuildCookie(array $data)
    {
        $string = '';
        foreach ($data as $k => $v) {
            $string .= "{$k}={$v};";
        }

        return $string;
    }
}

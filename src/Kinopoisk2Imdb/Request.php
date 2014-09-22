<?php
namespace Kinopoisk2Imdb;

/**
 * Class Request
 * @package Kinopoisk2Imdb
 */
class Request
{
    /**
     *
     */
    const CURL_METHOD_GET = 'GET';
    /**
     *
     */
    const CURL_METHOD_POST = 'POST';

    /**
     * @var array
     */
    private $auth;

    /**
     *
     */
    public function __construct($auth)
    {
        $this->auth = ['id' => $auth];
    }

    /**
     * @param string $title
     * @param string $year
     * @return bool
     */
    public function searchMovie($title, $year)
    {
        $query = http_build_query([
                'q'    => $title, // Запрос
                'tt'   => 'on',   // Поиск только по названиям
                'json' => 1,      // Выводить в формате JSON
                'nr'   => 1
            ]);

        $data = [
            Config::MOVIE_TITLE => $title,
            Config::MOVIE_YEAR  => $year,
            'json' => $this->fetchUrlByCurl(Config::$imdbLinks['search_for_movie'] . $query)
        ];

        return $data;
    }

    /**
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
     * @param string $movie_id
     * @param int $rating
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
     * Метод отправки запроса и получения ответа на определенный url через cURL
     * @param string $url Ссылка
     * @param string $method Метод запроса, GET или POST
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

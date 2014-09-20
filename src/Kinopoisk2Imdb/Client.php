<?php
namespace Kinopoisk2Imdb;

/**
 * Class Client
 * @package Kinopoisk2Imdb
 */
class Client
{
    /**
     * @var Parser
     */
    public $parser;

    /**
     * @var Filesystem
     */
    public $fs;

    /**
     * @var string
     */
    private $auth;

    /**
     *
     */
    public function __construct($auth)
    {
        $this->parser = new Parser();
        $this->fs = new Filesystem();
        $this->auth = $auth;
    }

    /**
     * @param $title
     * @param $year
     * @param $rating
     * @return mixed
     */
    public function wrapperSubmitMovieRating($title, $year, $rating)
    {
        $movie_id = $this->parser->parseMovieId(
            $this->searchMovie($title, $year)
        );
        $movie_auth = $this->parser->parseMovieAuthString(
            $this->openMoviePage($movie_id)
        );

        return $this->changeMovieRating($movie_id, $rating, $movie_auth);
    }

    public function wrapperAddMovieToWatchlist($title, $year, $list_id)
    {
        $movie_id = $this->parser->parseMovieId(
            $this->searchMovie($title, $year)
        );

        return $this->addMovieToWatchList($movie_id, $list_id);
    }

    /**
     * @param string $title
     * @param string $year
     * @return bool
     */
    public function searchMovie($title, $year)
    {
        $url = 'http://www.imdb.com/xml/find?';
        $query = http_build_query([
            'q'    => $title, // Запрос
            'tt'   => 'on',   // Поиск только по названиям
            'json' => 1,      // Выводить в формате JSON
            'nr'   => 1
        ]);

        $this->fs->setData($this->fetchUrlByCurl($url . $query));
        $this->fs->decodeJson();

        $data = [
            'title' => $title,
            'year' => $year,
            'json' => $this->fs->getData()
        ];

        return $data;
    }

    /**
     * @param string $movie_id
     * @return mixed
     */
    public function openMoviePage($movie_id)
    {
        $url = 'http://www.imdb.com/title/';

        return $this->fetchUrlByCurl($url . $movie_id, 'GET', ['id' => $this->auth]);
    }

    /**
     * @param string $movie_id
     * @param int $rating
     * @return mixed
     */
    public function changeMovieRating($movie_id, $rating, $auth)
    {
        $url = 'http://www.imdb.com/ratings/_ajax/title';
        $post_data = [
            'tconst'       => $movie_id,           // ID фильма
            'rating'       => $rating,             // Рейтинг
            'auth'         => $auth,               // Куки авторизации
            'tracking_tag' => 'title-maindetails', // Тэг для трекинга не меняется
            'pageId'       => $movie_id,           // ID страницы (совпадает с ID фильма)
            'pageType'     => 'title',             // Реферер не меняется
            'subpageType'  => 'main'               // Тип страницы не меняется
        ];

        return $this->fetchUrlByCurl($url, 'POST', ['id' => $this->auth], $post_data);
    }

    /**
     * @param string $movie_id
     * @param string $list_id
     * @param string $hidden_field
     * @return mixed
     */
    public function addMovieToWatchList($movie_id, $list_id, $hidden_field = null)
    {
        $url = 'http://www.imdb.com/list/_ajax/edit';
        $post_data = [
            'const' => $movie_id,   // ID фильма
            'list_id' => $list_id,  // ID списка для добавления
            'ref_tag' => 'title'    // Реферер не меняется
        ];
        if (is_null($hidden_field) === false) {
            $post_data['list_class'] = 'WATCHLIST';
            $post_data['49e6c'] = $hidden_field;
        }

        return $this->fetchUrlByCurl($url, 'POST', ['id' => $this->auth], $post_data);
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
        $method = 'GET',
        array $cookies = [],
        array $post_data = [],
        array $add_headers = [],
        $user_agent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36'
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
        if ($method === 'POST') {
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

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
     *
     */
    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * @param $title
     * @param $year
     * @return bool
     */
    public function searchMovie($title, $year)
    {
        $url = 'http://www.imdb.com/find?';
        $query = http_build_query([
            'q'     => "{$title} ({$year})", // Запрос
            's'     => 'tt',                 // Поиск только по названиям
            'exact' => 'true'                // Искать по точному совпадению
        ]);

        return $this->fetchUrlByCurl($url . $query);
    }

    /**
     * @param $movie_id
     * @param $list_id
     * @return mixed
     */
    public function addMovieToWatchList($movie_id, $list_id)
    {
        /*        При добавлении первого фильма в дефолтный WatchList */
        //        tconst:tt2294629      // ID фильма
        //        lcn:title             // Реферер не меняется
        //        49e6c:4ed2            // Неизвестная хуйта, которая меняется при каждом логине

        /*        При добавлении всех последующих фильмов в дефолтный WatchList */
        //        const:tt1049413       // ID фильма
        //        list_id:ls075660982   // ID списка для добавления
        //        ref_tag:title         // Реферер не меняется
        //        list_class:WATCHLIST  // Класс списка для добавления
        //        49e6c:4ed2            // Неизвестная хуйта, которая меняется при каждом логине

        /*        При добавлении фильма в уже созданный свой WatchList */
        //        const:tt0831387       // ID фильма
        //        list_id:ls075665398   // ID списка для добавления
        //        ref_tag:title         // Реферер не меняется

        $url = 'http://www.imdb.com/list/_ajax/edit';
        $post_data = [
            'const'   => $movie_id, // ID фильма
            'list_id' => $list_id,  // ID списка для добавления
            'ref_tag' => 'title'    // Реферер не меняется
        ];

        return $this->fetchUrlByCurl($url, 'POST', $post_data);
    }

    /**
     * @param $movie_id
     * @param $rating
     * @return mixed
     */
    public function setMovieRating($movie_id, $rating)
    {
        /*        При изменении рейтинга фильма */
        //        tconst:tt1049413                  // ID фильма
        //        rating:10                         // Целочисленный рейтинг
        //        auth:xxx                          // Куки авторизации
        //        tracking_tag:title-maindetails    // Тэг для трекинга не меняется
        //        pageId:tt1049413                  // ID страницы (совпадает с ID фильма)
        //        pageType:title                    // Реферер не меняется
        //        subpageType:main                  // Тип страницы не меняется

        $url = 'http://www.imdb.com/ratings/_ajax/title';
        $post_data = [
            'tconst'       => $movie_id,           // ID фильма
            'rating'       => $rating,             // Рейтинг
            'auth'         => 'xxx',               // Куки авторизации
            'tracking_tag' => 'title-maindetails', // Тэг для трекинга не меняется
            'pageId'       => $movie_id,           // ID страницы (совпадает с ID фильма)
            'pageType'     => 'title',             // Реферер не меняется
            'subpageType'  => 'main'               // Тип страницы не меняется
        ];

        return $this->fetchUrlByCurl($url, 'POST', $post_data);
    }

    /**
     * Метод отправки запроса и получения ответа на определенный url через cURL
     * @param string $url Ссылка
     * @param string $method Метод запроса, GET или POST
     * @param array $post_data
     * @param array $add_headers
     * @param string $user_agent
     * @return mixed
     */
    public function fetchUrlByCurl(
        $url,
        $method = 'GET',
        array $post_data = [],
        array $add_headers = [],
        $user_agent = 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36'
    ) {
        $options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_0,
            CURLOPT_USERAGENT      => $user_agent,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $add_headers,
        ];
        if ($method === 'POST') {
            $options[CURLOPT_POSTFIELDS] = http_build_query($post_data);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}

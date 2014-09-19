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
        $movie_id = $this->extractMovieId(
            $this->searchMovie($title, $year)
        );
        $movie_auth = $this->extractMovieAuthString(
            $this->openMoviePage($movie_id)
        );

        return $this->changeMovieRating($movie_id, $rating, $movie_auth);
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

        return $this->fetchUrlByCurl(
            $url, 'POST', ['id' => $this->auth], $post_data
        );
    }

    /**
     * @param string $movie_id
     * @param string $list_id
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
            'const' => $movie_id, // ID фильма
            'list_id' => $list_id,  // ID списка для добавления
            'ref_tag' => 'title'    // Реферер не меняется
        ];

        return $this->fetchUrlByCurl($url, 'POST', $post_data);
    }

    /**
     * @param string $data
     * @return bool|string
     */
    public function extractMovieId($data)
    {
        try {
            // Ищем и устанавливаем доступную категорию (чем выше в массиве - тем выше приоритет) и если не найдено - кидам Exception
            $categories = [
                'title_popular',
                'title_exact',
                'title_substring'
            ];

            foreach ($categories as $category) {
                if (isset($data['json'][$category])) {
                    $type = $category;
                    break;
                }
            }

            if (!isset($type)) {
                throw new \Exception('Пустые категории в результатах поиска');
            }

            // Ищем фильм и вовзращаем его ID, а если не найден - возвращаем false
            foreach ($data['json'][$type] as $movie) {
                if ($movie['title'] === $data['title']) {
                    if (strpos($movie['title_description'], $data['year']) !== false) {
                        $movie_id = $movie['id'];
                        break;
                    }
                }
            }

            if (!isset($movie_id)) {
                return false;
            }

            return $movie_id;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $data
     * @return bool
     */
    public function extractMovieAuthString($data)
    {
        if (preg_match('/data-auth="(.*?)"/is', $data, $matches)) {
            $auth = $matches[1];

            if (empty($auth)) {
                return false;
            }

            return $auth;
        } else {
            return false;
        }
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
        if (!is_null($cookies)) {
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

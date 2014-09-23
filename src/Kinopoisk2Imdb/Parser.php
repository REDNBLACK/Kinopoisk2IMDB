<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Config\Config;

/**
 * Class Parser
 * @package Kinopoisk2Imdb
 */
class Parser
{
    /**
     * @var Filesystem
     */
    private $fs;

    /**
     *
     */
    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    /**
     * @param $data
     * @return string
     */
    public function parseKinopoiskTable($data)
    {
        return $this->executeQuery(
            $data,
            "//table//tr",
            function ($query) {
                $data = [];
                $index = 0;

                foreach ($query as $tr) {
                    /** @var \DomDocument $tr */
                    foreach ($tr->getElementsByTagName('td') as $td) {
                        $data[$index][] = $td->nodeValue;
                    }
                    $index++;
                }

                return $data;
            }
        );
    }

    /**
     * @param string $data
     * @param int $mode
     * @return bool|string
     */
    public function parseMovieId($data, $mode)
    {
        try {
            // Декодируем строку json в массив
            $data['json'] = $this->fs->setData($data['json'])->decodeJson()->getData();

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
                if ($this->compareStrings($movie[Config::MOVIE_TITLE], $data[Config::MOVIE_TITLE], $mode)) {
                    if (strpos($movie['title_description'], $data[Config::MOVIE_YEAR]) !== false) {
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
     * @param $string1
     * @param $string2
     * @param $mode
     * @return bool
     */
    public function compareStrings($string1, $string2, $mode)
    {
        switch ($mode) {
            case Config::COMPARE_STRICT:
                return $string1 === $string2;
                break;
            case Config::COMPARE_BY_LEFT_SIDE:
                return strpos($string1, $string2) === 0 ? true : false;
                break;
            case Config::COMPARE_IS_IN_STRING:
                return strpos($string1, $string2) !== false ? true : false;
                break;
            case Config::COMPARE_SMART:
                return false;
                break;
            default:
                return false;
        }
    }

    /**
     * @param string $data
     * @return string
     */
    public function parseMovieAuthString($data)
    {
        return $this->executeQuery(
            $data,
            '//*[@data-auth]/@data-auth',
            function ($query) {
                $data = '';
                foreach ($query as $v) {
                    /** @var \DomDocument $v */
                    $node_value = $v->nodeValue;
                    if (!empty($node_value)) {
                        $data = $node_value;
                        break;
                    }
                }

                return $data;
            }
        );
    }

    /**
     * @param $data
     * @param bool $disable_errors
     * @return \DomXPath
     */
    public function loadDom($data, $disable_errors = true)
    {
        if ($disable_errors === true) {
            libxml_use_internal_errors(true);
        }

        $dom = new \DomDocument;
        $dom->loadHTML($data);
        $xpath = new \DomXPath($dom);

        if ($disable_errors === true) {
            libxml_clear_errors();
        }

        return $xpath;
    }

    /**
     * @param $data
     * @param $query
     * @param callable $callback
     * @return string
     */
    public function executeQuery($data, $query, \Closure $callback)
    {
        try {
            $query = $this->loadDom($data)->query($query);

            return $callback($query);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

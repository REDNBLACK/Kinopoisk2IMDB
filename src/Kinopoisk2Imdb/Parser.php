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
     * @param string $data
     * @param int $mode
     * @return bool|string
     */
    public function parseMovieId($data, $mode, $query_type)
    {
        try {
            if ($query_type === Config::QUERY_FORMAT_JSON) {
                // Декодируем строку json в массив
                $data['structure'] = $this->fs->setData($data['structure'])->decodeJson()->getData();
            } elseif ($query_type === Config::QUERY_FORMAT_XML) {
                // Декодируем строку xml в массив
                $data['structure'] = $this->parseMovieSearchXMLResult($data['structure']);
            }

            // Ищем и устанавливаем доступную категорию (чем выше в массиве - тем выше приоритет) и если не найдено - кидам Exception
            $categories = [
                'title_popular',
                'title_exact',
                'title_substring'
            ];

            foreach ($categories as $category) {
                if (isset($data['structure'][$category])) {
                    $type = $category;
                    break;
                }
            }

            if (!isset($type)) {
                throw new \Exception('Пустые категории в результатах поиска');
            }

            // Ищем фильм и вовзращаем его ID, а если не найден - возвращаем false
            foreach ($data['structure'][$type] as $movie) {
                if ($this->compareStrings($movie[Config::MOVIE_TITLE], $data[Config::MOVIE_TITLE], $mode)) {
                    if (strpos($movie['description'], $data[Config::MOVIE_YEAR]) !== false) {
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
                $result = $string1 === $string2;
                break;
            case Config::COMPARE_BY_LEFT_SIDE:
                $result = strpos($string1, $string2) === 0 ? true : false;
                break;
            case Config::COMPARE_IS_IN_STRING:
                $result = strpos($string1, $string2) !== false ? true : false;
                break;
            case Config::COMPARE_SMART:
                $result = ($string1 !== $string2 ? $this->smartStringsCompare($string1, $string2) : true);
                break;
            default:
                $result = false;
        }

        return $result;
    }

    /**
     * @param $string1
     * @param $string2
     * @param array $additional_methods
     * @return bool
     */
    public function smartStringsCompare($string1, $string2, array $additional_methods = [])
    {
        // Методы по умолчанию для первой строки
        $default_methods['first_string'] = [
            function ($s) {
                return $s;
            },
            function ($s) {
                return preg_replace(
                    '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
                    '$1',
                    htmlentities($s, ENT_QUOTES, 'UTF-8')
                );
            }
        ];

        // Методы по умолчанию для второй строки
        $default_methods['second_string'] = [
            function ($s) {
                return $s;
            },
            function ($s) {
                return "The {$s}";
            }
        ];

        $methods = array_merge_recursive($default_methods, $additional_methods);

        foreach ($methods['first_string'] as $first) {
            foreach ($methods['second_string'] as $second) {
                if ($first($string1) === $second($string2)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $data
     * @return array
     */
    public function parseMovieSearchXMLResult($data) {
        return $this->executeQuery(
            $data,
            Config::PARSER_DOCUMENT_XML,
            '//ResultSet',
            function ($query) {
                $data = [];

                foreach ($query as $result_set) {
                    /** @var \DomDocument $result_set */
                    foreach ($result_set->getElementsByTagName('ImdbEntity') as $entity) {
                        /** @var \DomDocument $entity */
                        $data[$result_set->getAttribute('type')][] = [
                            'id' => $entity->getAttribute('id'),
                            'title' => $entity->firstChild->nodeValue,
                            'description' => $entity->getElementsByTagName('Description')->item(0)->nodeValue
                        ];
                    }
                }

                return $data;
            }
        );
    }

    /**
     * @param string $data
     * @return string
     */
    public function parseMovieAuthString($data)
    {
        return $this->executeQuery(
            $data,
            Config::PARSER_DOCUMENT_HTML,
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
     * @return array
     */
    public function parseKinopoiskTable($data)
    {
        return $this->executeQuery(
            $data,
            Config::PARSER_DOCUMENT_HTML,
            '//table//tr',
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
     * @param $data
     * @param bool $disable_errors
     * @return \DomXPath
     */
    public function loadDom($data, $document_type, $disable_errors = true)
    {
        if ($disable_errors === true) {
            libxml_use_internal_errors(true);
        }

        $dom = new \DomDocument;
        if ($document_type === Config::PARSER_DOCUMENT_HTML) {
            $dom->loadHTML($data);
        } elseif ($document_type = Config::PARSER_DOCUMENT_XML) {
            $dom->loadXML($data);
        }
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
    public function executeQuery($data, $document_type, $query, \Closure $callback)
    {
        try {
            $query = $this->loadDom($data, $document_type)->query($query);

            return $callback($query);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Methods\DomDocument;
use Kinopoisk2Imdb\Methods\Compare;
use Kinopoisk2Imdb\Config\Config;

/**
 * Class Parser
 * @package Kinopoisk2Imdb
 */
class Parser
{
    /**
     * @var FileManager Container
     */
    private $fileManager;

    /**
     * @var DomDocument Container
     */
    private $domDocumentMethods;

    /**
     * @var Compare Container
     */
    private $compareMethods;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fileManager = new FileManager();
        $this->domDocumentMethods = new DomDocument();
        $this->compareMethods = new Compare();
    }

    /**
     * Method for searching and extracting a single movie id from XML or JSON structure
     * @param string $data
     * @param string $mode
     * @return bool|string
     */
    public function parseMovieId($data, $mode, $query_type)
    {
        try {
            if (empty($data['structure'])) {
                return false;
            }

            if ($query_type === Config::QUERY_FORMAT_JSON) {
                // Декодируем строку json в массив
                $data['structure'] = $this->fileManager->setData($data['structure'])->decodeJson()->getData();
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
                return false;
            }

            // Ищем фильм и вовзращаем его ID, а если не найден - возвращаем false
            foreach ($data['structure'][$type] as $movie) {
                if ($this->compareMethods->compare($movie[Config::MOVIE_TITLE], $data[Config::MOVIE_TITLE], $mode)) {
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
     * Parse movie search XML response to array
     * @param string $data
     * @return array
     */
    public function parseMovieSearchXMLResult($data) {
        return $this->domDocumentMethods->executeQuery($data, 'XML', '//ResultSet', function ($query) {
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
        });
    }

    /**
     * Parse movie auth from HTML response to string
     * @param string $data
     * @return string
     */
    public function parseMovieAuthString($data)
    {
        return $this->domDocumentMethods->executeQuery($data, 'HTML', '//*[@data-auth]/@data-auth', function ($query) {
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
        });
    }

    /**
     * Parse HTML data from Kinopoisk table to array
     * @param string $data
     * @return array
     */
    public function parseKinopoiskTable($data)
    {
        return $this->domDocumentMethods->executeQuery($data, 'HTML', '//table//tr', function ($query) {
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
        });
    }
}

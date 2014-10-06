<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Config\Config;

/**
 * Class Generator
 * @package Kinopoisk2Imdb
 */
class Generator
{
    /**
     * @var Parser Container
     */
    private $parser;

    /**
     * @var FileManager Container
     */
    private $resourceManager;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parser = new Parser();
        $this->resourceManager = new ResourceManager();
    }

    /**
     * Method for main setup of current class
     * @param string $file
     * @return bool|string
     */
    public function init($file)
    {
        $data = $this->filterData(
            $this->parser->parseKinopoiskTable($this->resourceManager->setFileName($file, false)->readFile()->getData())
        );

        $settings = ['status' => 'untouched'];

        // Возвращаем имя только что созданного файла
        return $this->resourceManager->saveFormattedData($data, $file, $settings);
    }

    /**
     * Method for filtering parsed data from Kinopoisk table
     * @param $data
     * @return string
     */
    public function filterData($data)
    {
        $replace_data = [
            'оригинальное название' => Config::MOVIE_TITLE,
            'год'                   => Config::MOVIE_YEAR,
            'моя оценка'            => Config::MOVIE_RATING
        ];

        // Формируем заголовок и заменяем в нем значения
        $header = array_shift($data);
        foreach ($header as &$row) {
            $search_key = array_search($row, array_keys($replace_data), true);
            if ($search_key !== false) {
                $row = array_values($replace_data)[$search_key];
            }
        }
        unset($row);

        // Делаем ключами массива данные данные из заголовка и затем убираем все ненужные значения
        foreach ($data as &$column) {
            $column = array_intersect_key(array_combine($header, $column), array_flip($replace_data));
        }
        unset($column);

        $data = $this->filterYear($data);

        return $data;
    }

    /**
     * Method for replacing year range to single value (Example: 2013 - 2014 to 2013)
     * @param $data
     * @return mixed
     */
    public function filterYear($data)
    {
        foreach ($data as &$column) {
            $date_exploded = explode(' ', $column['year']);
            if (count($date_exploded) > 1) {
                $column['year'] = $date_exploded[0];
            }
        }
        unset($column);

        return $data;
    }
}

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
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var string
     */
    public $newFileName;

    /**
     *
     */
    public function __construct($file)
    {
        $this->fs = new Filesystem();
        $this->parser = new Parser();
        $this->fs->setFile($file);
    }

    /**
     * @return bool|string
     */
    public function init()
    {
        try {
            $this->fs->readFile();
            $this->fs->setData(
                $this->parser->parseKinopoiskTable($this->fs->getData())
            );
            $this->fs->setData(
                $this->filterData($this->fs->getData())
            );
            $this->fs->setData(
                $this->addSettingsArray(
                    $this->fs->getData(),
                    [
                        'filesize' => filesize($this->fs->getFile())
                    ]
                )
            );
            $this->fs->encodeJson();
            $this->newFileName = $this->fs->writeToFile();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $data
     * @return string
     */
    public function filterData($data)
    {
        try {
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
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
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

    /**
     * @param $data
     * @param array $settings
     * @return bool
     */
    public function addSettingsArray($data, array $settings)
    {
        if (array_unshift($data, $settings)) {
            return $data;
        }
        return false;
    }
}

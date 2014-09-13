<?php
namespace Kinopoisk2Imdb;

/**
 * Class Generator
 * @package Kinopoisk2Imdb
 */
class Generator
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var Parser
     */
    public $parser;

    /**
     *
     */
    public function __construct($file, Filesystem $fs, Parser $parser)
    {
        $this->fs = $fs;
        $this->parser = $parser;
        $this->fs->setFile($this->fs->getDir() . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * @return bool|string
     */
    public function init()
    {
        try {
            $this->fs->readFile();
            $this->parser->parseKinopoiskTable();
            $this->filterData();
            $this->addSettingsArray();
            $this->fs->encodeJson();
            $this->fs->writeToFile();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string
     */
    public function filterData()
    {
        try {
            $replace_data = [
                'оригинальное название' => 'title_orig',
                'год' => 'year',
                'моя оценка' => 'my_rating'
            ];
            $data = $this->fs->getData();

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

            $this->fs->setData($data);

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string
     */
    public function addSettingsArray()
    {
        $data = $this->fs->getData();
        if (array_unshift($data, ['filesize' => filesize($this->fs->getFile())])) {
            $this->fs->setData($data);
            return true;
        }
        return false;
    }
}

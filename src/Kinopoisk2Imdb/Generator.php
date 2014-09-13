<?php
namespace Kinopoisk2Imdb;

use DOMDocument;
use DOMXPath;

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
     * @param string $file
     * @param Filesystem $fs
     */
    public function __construct($file, Filesystem $fs)
    {
        $this->fs = $fs;
        $this->fs->setFile($this->fs->getDir() . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * @return bool|string
     */
    public function init()
    {
        try {
            $this->fs->readFile();
            $this->parseHtml();
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
    public function parseHtml()
    {
        try {
            // TODO. Переместить в класс Parser
            $dom = new DomDocument;
            $dom->loadHTML($this->fs->getData());
            $xpath = new DomXPath($dom);

            $query = $xpath->query("//table//tr");
            $data = [];
            $index = 0;

            foreach ($query as $tr) {
                /** @var DomDocument $tr */
                foreach ($tr->getElementsByTagName('td') as $td) {
                    $data[$index][] = $td->nodeValue;
                }

                $index++;
            }

            $this->fs->setData($data);

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

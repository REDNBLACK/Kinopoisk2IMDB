<?php
namespace Kinopoisk2Imdb;
use phpQuery;

/**
 * Class Generator
 * @package Kinopoisk2Imdb
 */
class Generator extends Filesystem
{
    /**
     * @var string
     */
    protected $file;
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        parent::__construct();
        $this->file = $this->dir . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @return bool|string
     */
    public function init()
    {
        try {
            $this->readFile();
            $this->parseHtml();
            $this->filterData();
            $this->addSettingsArray();
            $this->encodeJson();
            $this->writeToFile();

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
            $html = phpQuery::newDocumentHTML($this->data);
            $index = 0;
            unset($this->data);

            $table = $html["table tr"];
            foreach ($table as $tr) {
                foreach (pq($tr)->find('td') as $td) {
                    $this->data[$index][] = pq($td)->text();
                }
                $index++;
            }

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

            // Формируем заголовок и заменяем в нем значения
            $header = array_shift($this->data);
            foreach ($header as &$row) {
                $search_key = array_search($row, array_keys($replace_data), true);
                if ($search_key !== false) {
                    $row = array_values($replace_data)[$search_key];
                }
            }
            unset($row);

            // Делаем ключами массива данные данные из заголовка и затем убираем все ненужные значения
            foreach ($this->data as &$column) {
                $column = array_intersect_key(array_combine($header, $column), array_flip($replace_data));
            }
            unset($column);

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
        if (array_unshift($this->data, ['filesize' => filesize($this->file)])) {
            return true;
        }
        return false;
    }
}

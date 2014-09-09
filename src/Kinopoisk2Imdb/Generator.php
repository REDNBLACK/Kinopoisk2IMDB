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
        return $this->parseHtml()
            ->filterData()
            ->addSettingsArray()
            ->encodeJson()
            ->writeToFile();
    }

    /**
     * @return $this
     */
    public function parseHtml()
    {
        $html = phpQuery::newDocumentFileHTML($this->file);
        $index = 0;

        $table = $html["table tr"];
        foreach ($table as $tr) {
            foreach (pq($tr)->find('td') as $td) {
                $this->data[$index][] = pq($td)->text();
            }
            $index++;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function filterData()
    {
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

        return $this;
    }

    /**
     * @return $this
     */
    public function addSettingsArray()
    {
        array_unshift($this->data, ['filesize' => filesize($this->file)]);
        return $this;
    }
}

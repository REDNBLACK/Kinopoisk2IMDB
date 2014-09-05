<?php
namespace Kinopoisk2Imdb;

use phpQuery;

class Generator
{
    public $path;
    public $file;
    public $fileSize;

    public function __construct($file)
    {
        $this->file = __DIR__ . '/../../' . $file;
        $this->fileSize = filesize($this->file);
    }

    public function generate()
    {
//        return $this->filterData($this->parseHtml());
        $this->saveToFile($this->filterData($this->parseHtml()));
    }

    public function parseHtml()
    {
        $html = phpQuery::newDocumentFileHTML($this->file);
        $data = [];
        $index = 0;

        $table = $html["table tr"];
        foreach ($table as $tr) {
            foreach (pq($tr)->find('td') as $td) {
                $data[$index][] = pq($td)->text();
            }
            $index++;
        }

        return $data;
    }

    public function filterData($data)
    {
        $replace_data = [
            'оригинальное название' => 'title_orig',
            'год' => 'year',
            'моя оценка' => 'my_rating'
        ];

        // Формируем заголовок и заменяем в нем значения
        $header = array_shift($data);
        foreach ($header as $row_k => $row_v) {
            $search_key = array_search($row_v, array_keys($replace_data), true);
            if ($search_key !== false) {
                $header[$row_k] = array_values($replace_data)[$search_key];
            }
        }

        // Делаем ключами массива данные данные из заголовка
        foreach ($data as &$column) {
            $column = array_combine($header, $column);
        }
        unset($column);

        // Убираем все ненужные значения
        foreach ($data as &$column) {
            $column = array_intersect_key($column, array_flip(array_values($replace_data)));
        }
        unset($column);

        return $data;
    }

    public function generateJson($data)
    {
        return json_encode($data);
    }

    public function saveToFile($data)
    {
        array_unshift($data, ['filesize' => $this->fileSize]);
        $data = $this->generateJson($data);
        $file_name = basename($this->file, ".xls") . '.json';
        file_put_contents(__DIR__ . '/../../' . $file_name, $data, LOCK_EX);
    }
}

<?php
namespace Kinopoisk2Imdb;
use phpQuery;

const DIRECTORY_UP = '..';
class Generator
{
    protected $file;
    protected $data;

    public function __construct($file)
    {
        $this->file = implode(DIRECTORY_SEPARATOR, [__DIR__, DIRECTORY_UP, DIRECTORY_UP, $file]);
    }

    public function generate()
    {
        return $this->parseHtml()
            ->filterData()
            ->addSettingsArray()
            ->generateJson()
            ->saveToFile();
    }

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

        // Делаем ключами массива данные данные из заголовка
        foreach ($this->data as &$column) {
            $column = array_combine($header, $column);
        }
        unset($column);

        // Убираем все ненужные значения
        foreach ($this->data as &$column) {
            $column = array_intersect_key($column, array_flip($replace_data));
        }
        unset($column);

        return $this;
    }

    public function addSettingsArray()
    {
        array_unshift($this->data, ['filesize' => filesize($this->file)]);
        return $this;
    }

    public function generateJson()
    {
        $this->data = json_encode($this->data);
        return $this;
    }

    public function saveToFile($extension = '.json')
    {
        try {
            $path_parts = pathinfo($this->file);
            file_put_contents(
                $path_parts['dirname'] . DIRECTORY_SEPARATOR . $path_parts['filename'] . $extension,
                $this->data,
                LOCK_EX
            );
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

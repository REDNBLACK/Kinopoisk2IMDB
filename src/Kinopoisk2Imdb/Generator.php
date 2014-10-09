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
     * Method for main setup of current class
     * @param array $data
     * @return bool|string
     */
    public function init($data)
    {
        return $this->filterData($data);
    }

    /**
     * Method for filtering parsed data from Kinopoisk table
     * @param array $data
     * @return string
     */
    public function filterData($data)
    {
        // Проверка в норме ли данные
        if (empty($data)) {
            return false;
        }

        $replace_data = [
            'оригинальное название' => Config::MOVIE_TITLE,
            'год'                   => Config::MOVIE_YEAR,
            'моя оценка'            => Config::MOVIE_RATING
        ];

        // Формируем заголовок и заменяем в нем значения
        $header = $this->arrayMap(array_shift($data), function ($value) use ($replace_data) {
            $search_key = array_search($value, array_keys($replace_data), true);
            if ($search_key !== false) {
                $value = array_values($replace_data)[$search_key];
            }

            return $value;
        });

        // Делаем ключами массива данные из заголовка и затем убираем все ненужные значения
        $data = $this->arrayMap($data, function ($value) use ($header, $replace_data) {
            return array_intersect_key(array_combine($header, $value), array_flip($replace_data));
        });

        // Фильтруем год для каждого фильма
        $data = $this->arrayMap($data, function ($value) {
            $value['year'] = $this->filterYear($value['year']);

            return $value;
        });

        return $data;
    }

    /**
     * Method for replacing year range to single value (Example: 2013 - 2014 to 2013)
     * @param string $year
     * @return string
     */
    public function filterYear($year)
    {
        $year_range = explode(' ', $year);
        if (count($year_range) > 1) {
            $year = $year_range[0];
        }

        return $year;
    }

    /**
     * array_map with reversed arguments position
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public function arrayMap(array $array, callable $callback)
    {
        return array_map($callback, $array);
    }
}

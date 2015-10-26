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
     * @param  array       $data
     * @return bool|string
     */
    public function init($data)
    {
        return $this->filterData($data);
    }

    /**
     * Method for filtering parsed data from Kinopoisk table
     * @param  array  $data
     * @return string
     */
    public function filterData($data)
    {
        // Проверка в норме ли данные
        if (empty($data)) {
            return false;
        }

        $replace_data = [
            'русскоязычное название' => Config::MOVIE_TITLE_LOCALIZED,
            'оригинальное название'  => Config::MOVIE_TITLE,
            'год'                    => Config::MOVIE_YEAR,
            'моя оценка'             => Config::MOVIE_RATING
        ];

        // Формируем заголовок и заменяем в нем значения
        $header = array_map(
            function ($value) use ($replace_data) {
                $search_key = array_search($value, array_keys($replace_data), true);
                if ($search_key !== false) {
                    $value = array_values($replace_data)[$search_key];
                }

                return $value;
            },
            array_shift($data)
        );

        // Делаем ключами массива данные из заголовка и затем убираем все ненужные значения
        $data = array_map(
            function ($value) use ($header, $replace_data) {
                return array_intersect_key(array_combine($header, $value), array_flip($replace_data));
            },
            $data
        );

        // Назначаем название фильма оригинальное или локализованное в зависимости от наличия
        $data = array_map(
            function ($value) {
                if (empty($value[Config::MOVIE_TITLE])) {
                    $value[Config::MOVIE_TITLE] = $value[Config::MOVIE_TITLE_LOCALIZED];
                }
                unset($value[Config::MOVIE_TITLE_LOCALIZED]);

                return $value;
            },
            $data
        );

        // Фильтруем год для каждого фильма
        $data = array_map(
            function ($value) {
                $value['year'] = $this->filterYear($value['year']);

                return $value;
            },
            $data
        );

        return $data;
    }

    /**
     * Method for replacing year range to single value (Example: 2013 - 2014 to 2013)
     * @param  string $year
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
}

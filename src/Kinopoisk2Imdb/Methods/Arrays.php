<?php
namespace Kinopoisk2Imdb\Methods;

/**
 * Class Arrays
 * @package Kinopoisk2Imdb
 */
class Arrays
{
    /**
     * Get element from start of the array
     * @param $array
     * @return mixed
     */
    public function getFirst($array)
    {
        return !is_array($array) ? false : array_shift($array);
    }

    /**
     * Add element to start of the array
     * @param $array
     * @param  mixed $data
     * @return mixed
     */
    public function addFirst($array, $data)
    {
        if (is_array($array)) {
            array_unshift($array, $data);

            return ['reference' => $array];
        }

        return false;
    }

    /**
     * Remove first element from array
     * @param $array
     * @return mixed
     */
    public function removeFirst($array)
    {
        if (is_array($array)) {
            array_shift($array);

            return ['reference' => $array];
        }

        return false;
    }

    /**
     * Get last element from array
     * @param $array
     * @return mixed|string
     */
    public function getLast($array)
    {
        return !is_array($array) ? false : array_pop($array);
    }

    /**
     * Remove last element from array
     * @param $array
     * @return mixed
     */
    public function removeLast($array)
    {
        if (is_array($array)) {
            array_pop($array);

            return ['reference' => $array];
        }

        return false;
    }

    /**
     * Count elements in array
     * @param $array
     * @param  bool     $recursive
     * @return int|bool
     */
    public function count($array, $recursive = false)
    {
        return !is_array($array) ? false : count($array, (int) $recursive);
    }
}

<?php
namespace Kinopoisk2Imdb;

/**
 * Class ArraysMethods
 * @package Kinopoisk2Imdb
 */
class ArraysMethods
{
    /**
     * @param $array
     * @return mixed
     */
    public function getFirst($array)
    {
        return array_shift($array);
    }

    /**
     * Add array to start of the current data
     * @param array $array
     * @param mixed $data
     * @return mixed
     */
    public function addFirst($array, $data)
    {
        array_unshift($array, $data);

        return ['reference' => $array];
    }

    /**
     * Remove first element from current data array
     * @param array $array
     * @return mixed
     */
    public function removeFirst($array)
    {
        array_shift($array);

        return ['reference' => $array];
    }

    /**
     * Get last element from current data array
     * @param array $array
     * @return mixed|string
     */
    public function getLast($array)
    {
        return array_pop($array);
    }

    /**
     * Remove last element from current data array
     * @param array $array
     * @return mixed
     */
    public function removeLast($array)
    {
        array_pop($array);

        return ['reference' => $array];
    }

    /**
     * Count elements in current data
     * @param array $array
     * @param bool $recursive
     * @return int
     */
    public function count($array, $recursive = false)
    {
        return count($array, (int) $recursive);
    }
} 

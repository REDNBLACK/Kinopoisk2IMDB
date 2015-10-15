<?php
namespace Kinopoisk2Imdb\Methods;

/**
 * Class Compare
 * @package Kinopoisk2Imdb\Methods
 */
class Compare
{
    /**
     * Init class method with parameters
     * @param $string1
     * @param $string2
     * @param $mode
     * @throws \Exception
     * @return mixed
     */
    public function compare($string1, $string2, $mode)
    {
        $mode = lcfirst(implode('', array_map('ucfirst', explode('_', $mode))));

        if (!method_exists($this, $mode)) {
            throw new \Exception(sprintf("Несуществующий метод(%1s) класса(%2s)", $mode, __CLASS__));
        }

        return call_user_func_array([$this, $mode], [$string1, $string2]);
    }

    /**
     * Determine if string1 equal to string2
     * @param $string1
     * @param $string2
     * @return bool
     */
    public function strict($string1, $string2)
    {
        return $string1 === $string2;
    }

    /**
     * Determine of string2 at the start and inside of string 1
     * @param $string1
     * @param $string2
     * @return bool
     */
    public function byLeft($string1, $string2)
    {
        return mb_strpos($string1, $string2) === 0;
    }

    /**
     * Determine is string2 inside string1
     * @param $string1
     * @param $string2
     * @return bool
     */
    public function isInString($string1, $string2)
    {
        return mb_strpos($string1, $string2) !== false;
    }

    /**
     * Smart method for comparing two strings
     * @param  string $string1
     * @param  string $string2
     *
     * @return bool
     */
    public function smart($string1, $string2)
    {
        $comparators_first = new Comparators();
        $comparators_second = new Comparators();

        // Выполняем сравнение
        foreach ($comparators_first as $first_comparator) {
            foreach ($comparators_second as $second_comparator) {
                if ($first_comparator($string1) === $second_comparator($string2)) {
                    return true;
                }
            }
        }

        return false;
    }
}

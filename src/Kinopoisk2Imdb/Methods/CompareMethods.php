<?php

namespace Kinopoisk2Imdb\Methods;

/**
 * Class CompareMethods
 * @package Kinopoisk2Imdb\Methods
 */
class CompareMethods
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
        return strpos($string1, $string2) === 0 ? true : false;
    }

    /**
     * Determine is string2 inside string1
     * @param $string1
     * @param $string2
     * @return bool
     */
    public function isInString($string1, $string2)
    {
        return strpos($string1, $string2) !== false ? true : false;
    }

    /**
     * Smart, extendable method for comparing two strings
     * @param string $string1
     * @param string $string2
     * @param array $additional_methods
     * @return bool
     */
    public function smart($string1, $string2, array $additional_methods = [])
    {
        // Методы по умолчанию для первой строки
        $default_methods['first_string'] = [
            // Original string
            function ($s) {
                return $s;
            },
            // Original string with replaced foreign characters
            function ($s) {
                return preg_replace(
                    '~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i',
                    '$1',
                    htmlentities($s, ENT_QUOTES, 'UTF-8')
                );
            }
        ];

        // Методы по умолчанию для второй строки
        $default_methods['second_string'] = [
            // Original string
            function ($s) {
                return $s;
            },
            // The + Original string
            function ($s) {
                return "The {$s}";
            }
        ];

        // Мерджим методы по умолчанию с пользовательскими
        $methods = array_merge_recursive($default_methods, $additional_methods);

        // Выполняем сравнение
        foreach ($methods['first_string'] as $first) {
            foreach ($methods['second_string'] as $second) {
                if ($first($string1) === $second($string2)) {
                    return true;
                }
            }
        }

        return false;
    }
} 
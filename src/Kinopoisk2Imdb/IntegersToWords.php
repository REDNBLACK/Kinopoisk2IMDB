<?php
namespace Kinopoisk2Imdb;

class IntegersToWords
{
    /**
     * @var array
     */
    private static $units = [
        'Zero',
        'One',
        'Two',
        'Three',
        'Four',
        'Five',
        'Six',
        'Seven',
        'Eight',
        'Nine',
        'Ten',
        'Eleven',
        'Twelve',
        'Thirteen',
        'Fourteen',
        'Fifteen',
        'Sixteen',
        'Seventeen',
        'Eightteen',
        'Nineteen'
    ];

    /**
     * @var array
     */
    private static $tens = [
        '',
        '',
        'Twenty',
        'Thirty',
        'Fourty',
        'Fifty',
        'Sixty',
        'Seventy',
        'Eigthy',
        'Ninety'
    ];

    /**
     * @param int|string $number
     * @return string
     */
    public static function convert($number)
    {
        $number = (int) $number;
        $result = [];
        $tens = (int) floor($number / 10);
        $units = $number % 10;

        if ($tens < 2) {
            $result[] = self::$units[(int) ($tens * 10 + $units)];
        } else {
            $result[] = (array_key_exists($tens, self::$tens) ? self::$tens[$tens] : '');

            if ($units > 0) {
                $result[count($result) - 1] .= '-' . self::$units[$units];
            }
        }

        return trim(implode(' ', $result));
    }

    /**
     * @param string $string
     * @return string
     */
    public static function convertInsideString($string)
    {
        $numbers = array_filter(explode(' ', preg_replace("/[^0-9]/", " ", $string)), function ($number) {
            return ($number !== null && $number !== '' && $number !== false);
        });
        $numbers_as_words = array_map(function ($number) {
            return self::convert($number);
        }, $numbers);

        foreach ($numbers as $index => $number) {
            $string = str_replace($number, $numbers_as_words[$index], $string);
        }

        return $string;
    }
} 
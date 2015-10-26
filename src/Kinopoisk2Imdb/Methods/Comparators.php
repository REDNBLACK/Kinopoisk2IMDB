<?php
namespace Kinopoisk2Imdb\Methods;

use Kinopoisk2Imdb\IntegersToWords;

final class Comparators implements \IteratorAggregate
{
    /**
     * @var array
     */
    private static $comparators;

    public function __construct()
    {
        self::setComparators();
    }

    /**
     * Init
     */
    private function setComparators()
    {
        self::$comparators = [
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
            },
            // Original string without commas
            function ($s) {
                return str_replace(',', '', $s);
            },
            // Original string without apostrophes
            function ($s) {
                return preg_replace('/([\'\x{0027}]|&#39;|&#x27;)/u', '', $s);
            },
            // Original string without special symbols like unicode etc
            function ($s) {
                return preg_replace('/\\\u([0-9a-z]{4})/', '', $s);
            },
            // Original string without colon
            function ($s) {
                return str_replace(':', '', $s);
            },
            // Original string with part before dash symbol
            function ($s) {
                $s = array_map('trim', explode('-', $s));
                return reset($s);
            },
            // Original string with part after dash symbol
            function ($s) {
                $s = array_map('trim', explode('-', $s));
                return end($s);
            },
            // The + Original string
            function ($s) {
                return "The {$s}";
            },
            // Original string with replaced "&" symbol on "and"
            function ($s) {
                return str_replace('&', 'and', $s);
            },
            // Original string with replaced "and" on "&" symbol
            function ($s) {
                return str_replace('and', '&', $s);
            },
            // Original string with replaced "&" symbol on "et"
            function ($s) {
                return str_replace('&', 'et', $s);
            },
            // Original string with replaced "et" on symbol "&"
            function ($s) {
                return str_replace('et', '&', $s);
            },
            // Original string with all whitespace characters replaced with plain backspace
            function ($s) {
                return preg_replace('/\s+/', ' ', $s);
            },
            // Original string with XML symbols replaced with backspace
            function ($s) {
                return str_replace(['&#xB;', '&#xC;', '&#x1A;', '&#x1B;'], ' ', $s);
            },
            // Original string with replaced numeric to words
            function ($s) {
                return IntegersToWords::convertInsideString($s);
            }
        ];
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator(self::$comparators);
    }
}

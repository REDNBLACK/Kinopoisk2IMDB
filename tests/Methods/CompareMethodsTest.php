<?php

use Kinopoisk2Imdb\Methods\CompareMethods;

class CompareMethodsTest extends PHPUnit_Framework_TestCase
{

    public function testCompare()
    {
        $compare = new CompareMethods();

        try {
            $compare->compare('PHP MySQL', 'PHP MySQL', 'not_existing_mode');
        } catch (\Exception $e) {
            $this->assertEquals(
                'Несуществующий метод(notExistingMode) класса(Kinopoisk2Imdb\Methods\CompareMethods)',
                $e->getMessage()
            );
        }
    }

    public function testStrict()
    {
        $compare = new CompareMethods();

        $this->assertTrue($compare->compare('PHP MySQL', 'PHP MySQL', 'strict'));
        $this->assertFalse($compare->compare('PHP Mysql', 'PHP MySQL', 'strict'));
    }

    public function testByLeft()
    {
        $compare = new CompareMethods();

        $this->assertTrue($compare->compare('PHP MySQL', 'PHP', 'by_left'));
        $this->assertFalse($compare->compare('MySQL PHP', 'PHP', 'by_left'));
    }

    public function testIsInString()
    {
        $compare = new CompareMethods();

        $this->assertTrue($compare->compare('JavaScript PHP MySQL', 'PHP', 'is_in_string'));
        $this->assertTrue($compare->compare('JavaScriptPHPMySQL', 'PHP', 'is_in_string'));
        $this->assertFalse($compare->compare('JavaScript PHP MySQL', 'Git', 'is_in_string'));
    }


    public function testSmart()
    {
        $parser = new CompareMethods();

        $this->assertTrue($parser->smart('Sin City', 'Sin City'));
        $this->assertTrue($parser->smart('The Intouchables', 'Intouchables'));
        $this->assertTrue(
            $parser->smart(
                'Kôkaku kidôtai: Stand Alone Complex',
                'Kokaku kidotai: Stand Alone Complex'
            )
        );

        $this->assertFalse($parser->smart('Sin City', 'SinCity'));
        $this->assertTrue(
            $parser->smart('Sin City', 'SinCity', [
                    'second_string' => [
                        function ($s) {
                            return implode(' ', preg_split(
                                    '#([A-Z][^A-Z]*)#', $s, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
                                ));
                        }
                    ]
                ])
        );
    }
} 
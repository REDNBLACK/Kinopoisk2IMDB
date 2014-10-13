<?php

use Kinopoisk2Imdb\Methods\Compare;

class CompareTest extends PHPUnit_Framework_TestCase
{

    public function testCompare()
    {
        $compare = new Compare();

        try {
            $compare->compare('PHP MySQL', 'PHP MySQL', 'not_existing_mode');
        } catch (\Exception $e) {
            $this->assertEquals(
                'Несуществующий метод(notExistingMode) класса(' . (new ReflectionClass($compare))->getName() .')',
                $e->getMessage()
            );
        }
    }

    public function testStrict()
    {
        $compare = new Compare();

        $this->assertTrue($compare->compare('PHP MySQL', 'PHP MySQL', 'strict'));
        $this->assertFalse($compare->compare('PHP Mysql', 'PHP MySQL', 'strict'));
    }

    public function testByLeft()
    {
        $compare = new Compare();

        $this->assertTrue($compare->compare('PHP MySQL', 'PHP', 'by_left'));
        $this->assertFalse($compare->compare('MySQL PHP', 'PHP', 'by_left'));
    }

    public function testIsInString()
    {
        $compare = new Compare();

        $this->assertTrue($compare->compare('JavaScript PHP MySQL', 'PHP', 'is_in_string'));
        $this->assertTrue($compare->compare('JavaScriptPHPMySQL', 'PHP', 'is_in_string'));
        $this->assertFalse($compare->compare('JavaScript PHP MySQL', 'Git', 'is_in_string'));
    }


    public function testSmart()
    {
        $parser = new Compare();

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
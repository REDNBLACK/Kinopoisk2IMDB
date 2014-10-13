<?php

use Kinopoisk2Imdb\Methods\Arrays;

class ArraysTest extends PHPUnit_Framework_TestCase
{
    protected $arrayStack;

    protected function setUp()
    {
        $this->arrayStack = [
            [
                'movie_title_1' => 'title_1',
                'movie_year_1' => 'year_1',
                'movie_rating_1' => 'rating_1'
            ],
            [
                'movie_title_2' => 'title_2',
                'movie_year_2' => 'year_2',
                'movie_rating_2' => 'rating_2'
            ],
            [
                'movie_title_3' => 'title_3',
                'movie_year_3' => 'year_3',
                'movie_rating_3' => 'rating_3'
            ]
        ];
    }

    public function testGetFirst()
    {
        $arr_methods = new Arrays();

        $this->assertFalse($arr_methods->getFirst('string'));

        $this->assertEquals(
            [
                'movie_title_1' => 'title_1',
                'movie_year_1' => 'year_1',
                'movie_rating_1' => 'rating_1'
            ],
            $arr_methods->getFirst($this->arrayStack)
        );
    }

    public function testAddFirst()
    {
        $arr_methods = new Arrays();

        $this->assertFalse($arr_methods->addFirst('string', 'string'));

        $new_arr = $arr_methods->addFirst($this->arrayStack, [
            'movie_title_new' => 'title_new',
            'movie_year_new' => 'year_new',
            'movie_rating_new' => 'rating_new'
        ]);
        $this->assertEquals(
            [
                'movie_title_new' => 'title_new',
                'movie_year_new' => 'year_new',
                'movie_rating_new' => 'rating_new'
            ],
            $new_arr['reference'][0]
        );
    }

    public function testRemoveFirst()
    {
        $arr_methods = new Arrays();

        $this->assertFalse($arr_methods->removeFirst('string'));

        $new_arr = $arr_methods->removeFirst($this->arrayStack);
        $this->assertEquals(
            [
                'movie_title_2' => 'title_2',
                'movie_year_2' => 'year_2',
                'movie_rating_2' => 'rating_2'
            ],
            $new_arr['reference'][0]
        );
    }

    public function testGetLast()
    {
        $arr_methods = new Arrays();

        $this->assertFalse($arr_methods->getLast('string'));

        $new_arr = $arr_methods->getLast($this->arrayStack);
        $this->assertEquals(
            [
                'movie_title_3' => 'title_3',
                'movie_year_3' => 'year_3',
                'movie_rating_3' => 'rating_3'
            ],
            $new_arr
        );
    }

    public function testRemoveLast()
    {
        $arr_methods = new Arrays();

        $this->assertFalse($arr_methods->removeLast('string'));

        $new_arr = $arr_methods->removeLast($this->arrayStack);
        $this->assertEquals(
            [
                'movie_title_2' => 'title_2',
                'movie_year_2' => 'year_2',
                'movie_rating_2' => 'rating_2'
            ],
            $new_arr['reference'][count($new_arr['reference']) - 1]
        );
    }

    public function testCount()
    {
        $arr_methods = new Arrays();

        $this->assertFalse($arr_methods->count('string'));

        $this->assertEquals(3, count($this->arrayStack));
    }
}

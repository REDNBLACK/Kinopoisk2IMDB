<?php
use Kinopoisk2Imdb\Generator;

class GeneratorTest extends PHPUnit_Framework_TestCase
{
    protected $arrayStack;

    protected function setUp()
    {
        $this->arrayStack = [
            [
                'русскоязычное название',
                'оригинальное название',
                'год',
                'страны',
                'режисcёр',
                'актёры',
                'жанры',
                'возраст',
                'время',
                'моя оценка',
                'рейтинг КиноПоиска',
                'число оценок',
                'рейтинг IMDb',
                'число оценок IMDb',
                'рейтинг MPAA',
                'мировая премьера',
                'премьера в РФ',
                'релиз на DVD',
                'мой комментарий',
                'бюджет$',
                'сборы США$',
                'сборы МИР$',
                'сборы РФ$',
            ],
            [
                'Город грехов',
                'Sin City',
                '2005',
                'США',
                'Фрэнк Миллер, Роберт Родригес, Квентин Тарантино',
                'Брюс Уиллис, Микки Рурк, Клайв Оуэн',
                'боевик, триллер, криминал',
                '16+',
                '124',
                '10',
                '7,955',
                '114 137',
                '8,20',
                '517 693',
                'R',
                '2005-03-28',
                '2005-05-26',
                '2005-08-04',
                '',
                '40000000',
                '74103820',
                '158733820',
                '2385000'
            ],
            [
                'Призрак в доспехах: Синдром одиночки',
                'Kokaku kidotai: Stand Alone Complex',
                '2002 – 2005',
                'Япония',
                'Кэндзи Камияма, Масаки Тачибана, Ицуро Кавасаки',
                'Ацуко Танака, Осаму Сака, Дино Андраде',
                'аниме, мультфильм, фантастика',
                '16+',
                '25',
                '10',
                '8,128',
                '3 015',
                '8,50',
                '7 805',
                '',
                '2002-10-0',
                '',
                '2008-09-16',
                '',
                '',
                '',
                '',
                ''
            ]
        ];
    }

    public function testFilterYear()
    {
        $generator = new Generator();

        $this->assertEquals('2005', $generator->filterYear('2005 - 2012'));
        $this->assertEquals('2005', $generator->filterYear('2005 - ...'));
        $this->assertEquals('2005', $generator->filterYear('2005 -'));
    }

    public function testFilterData()
    {
        $generator = new Generator();
        $result_data = [
            [
                'title' => 'Sin City',
                'year' => '2005',
                'rating' => '10'
            ],
            [
                'title' => 'Kokaku kidotai: Stand Alone Complex',
                'year' => '2002',
                'rating' => '10'
            ]
        ];

        $this->assertEquals($result_data, $generator->filterData($this->arrayStack));

    }
} 

<?php
use Kinopoisk2Imdb\Parser;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParseMovieId()
    {
        $parserMock = $this->getMockBuilder('Kinopoisk2Imdb\Parser')
            ->setMethods(['compareStrings', 'parseMovieSearchXMLResult'])
            ->getMock()
        ;
        $data_full = [
            'title_popular' => [
                [
                    'id' => 'tt0346314',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex',
                    'description' => '2002 TV series'
                ]
            ],
            'title_exact' => [
                [
                    'id' => 'tt0442800',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex',
                    'description' => '2004 video game,     Junichi Fujisaku'
                ]
            ],
            'title_substring' => [
                [
                    'id' => 'tt0856797',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex Solid State Society',
                    'description' => '2006 TV movie,     Kenji Kamiyama'
                ],
                [
                    'id' => 'tt1024215',
                    'title' => 'Ghost in the Shell: Stand Alone Complex - The Laughing Man',
                    'description' => '2005 video,     Kenji Kamiyama'
                ],
                [
                    'id' => 'tt1308130',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex - Kariudo no ryôiki',
                    'description' => '2005 video game'
                ]
            ]
        ];
        $data_from_exact = [
            'title_exact' => [
                [
                    'id' => 'tt0442800',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex',
                    'description' => '2004 video game,     Junichi Fujisaku'
                ]
            ],
            'title_substring' => [
                [
                    'id' => 'tt0856797',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex Solid State Society',
                    'description' => '2006 TV movie,     Kenji Kamiyama'
                ],
                [
                    'id' => 'tt1024215',
                    'title' => 'Ghost in the Shell: Stand Alone Complex - The Laughing Man',
                    'description' => '2005 video,     Kenji Kamiyama'
                ],
                [
                    'id' => 'tt1308130',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex - Kariudo no ryôiki',
                    'description' => '2005 video game'
                ]
            ]
        ];
        $data_from_substring = [
            'title_substring' => [
                [
                    'id' => 'tt0856797',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex Solid State Society',
                    'description' => '2006 TV movie,     Kenji Kamiyama'
                ],
                [
                    'id' => 'tt1024215',
                    'title' => 'Ghost in the Shell: Stand Alone Complex - The Laughing Man',
                    'description' => '2005 video,     Kenji Kamiyama'
                ],
                [
                    'id' => 'tt1308130',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex - Kariudo no ryôiki',
                    'description' => '2005 video game'
                ]
            ]
        ];

        $parserMock
            ->expects($this->any())
            ->method('compare')
            ->will($this->returnValue(true))
        ;
        $parserMock
            ->expects($this->any())
            ->method('parseMovieSearchXMLResult')
            ->will($this->onConsecutiveCalls($data_full, $data_from_exact, $data_from_substring))
        ;

        $this->assertEquals(
            'tt0346314',
            $parserMock->parseMovieId(
                [
                    'title' => 'Kokaku kidotai: Stand Alone Complex',
                    'year' => '2002',
                    'structure' => $data_full
                ],
                'smart',
                'xml'
            )
        );

        $this->assertEquals(
            'tt0442800',
            $parserMock->parseMovieId(
                [
                    'title' => 'Kokaku kidotai: Stand Alone Complex',
                    'year' => '2004',
                    'structure' => $data_from_exact
                ],
                'smart',
                'xml'
            )
        );

        $this->assertEquals(
            'tt1024215',
            $parserMock->parseMovieId(
                [
                    'title' => 'Ghost in the Shell: Stand Alone Complex - The Laughing Man',
                    'year' => '2005',
                    'structure' => $data_from_substring
                ],
                'smart',
                'xml'
            )
        );
    }

    public function testParseMovieSearchXMLResult()
    {
        $parser = new Parser();
        $xml = <<<EOF
<IMDbResults>
    <ResultSet type="title_popular">
        <ImdbEntity id="tt0346314">K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex<Description>2002 TV series</Description>
        </ImdbEntity>
    </ResultSet>
    <ResultSet type="title_exact">
        <ImdbEntity id="tt0442800">K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex<Description>2004 video game,     <a href='/name/nm1825288/'>Junichi Fujisaku</a></Description>
        </ImdbEntity>
    </ResultSet>
    <ResultSet type="title_substring">
        <ImdbEntity id="tt0856797">K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex Solid State Society<Description>2006 TV movie,     <a href='/name/nm0436784/'>Kenji Kamiyama</a></Description>
        </ImdbEntity>
        <ImdbEntity id="tt1024215">Ghost in the Shell: Stand Alone Complex - The Laughing Man<Description>2005 video,     <a href='/name/nm0436784/'>Kenji Kamiyama</a></Description>
        </ImdbEntity>
        <ImdbEntity id="tt1308130">K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex - Kariudo no ry&#xF4;iki<Description>2005 video game</Description>
        </ImdbEntity>
    </ResultSet>
</IMDbResults>
EOF;
        $result = [
            'title_popular' => [
                [
                    'id' => 'tt0346314',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex',
                    'description' => '2002 TV series'
                ]
            ],
            'title_exact' => [
                [
                    'id' => 'tt0442800',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex',
                    'description' => '2004 video game,     Junichi Fujisaku'
                ]
            ],
            'title_substring' => [
                [
                    'id' => 'tt0856797',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex Solid State Society',
                    'description' => '2006 TV movie,     Kenji Kamiyama'
                ],
                [
                    'id' => 'tt1024215',
                    'title' => 'Ghost in the Shell: Stand Alone Complex - The Laughing Man',
                    'description' => '2005 video,     Kenji Kamiyama'
                ],
                [
                    'id' => 'tt1308130',
                    'title' => 'Kôkaku kidôtai: Stand Alone Complex - Kariudo no ryôiki',
                    'description' => '2005 video game'
                ]
            ]
        ];

        $this->assertEquals($result, $parser->parseMovieSearchXMLResult($xml));

    }

    public function testParseMovieAuthString()
    {
        $parser = new Parser();
        $html = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'movie_page.html');
        $result = 'BCYn8l3-tqHDVAWOcqRjRoadV62ALHDdXVop5sCvrhqdEsrmJU4TZfSBBaQCPea32qh6sNfEIFIU%0D%0A9Q7Z1ASMr-gm0wjnTVShwKlO0xS_2mqPo85ASiwueHV3nV2UtFW8zbwW%0D%0A';

        $this->assertEquals($result, $parser->parseMovieAuthString($html));
    }

    public function testParseKinopoiskTable()
    {
        $parser = new Parser();
        $html = <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>оценки</title>
    <meta http-equiv="content-type" content="text/html; charset=utf8"/>
</head>
<body>
<table>
    <tr>
        <td bgcolor="#f2f2f2" height="40" valign="top"><i>русскоязычное название</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>оригинальное название</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>год</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>страны</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>режисcёр</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>актёры</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>жанры</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>возраст</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>время</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>моя оценка</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>рейтинг КиноПоиска</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>число оценок</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>рейтинг IMDb</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>число оценок IMDb</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>рейтинг MPAA</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>мировая премьера</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>премьера в РФ</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>релиз на DVD</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>мой комментарий</i></td>
        <td bgcolor="#f2f2f2" valign="top"><i>бюджет</i><br/>$</td>
        <td bgcolor="#f2f2f2" valign="top"><i>сборы США</i><br/>$</td>
        <td bgcolor="#f2f2f2" valign="top"><i>сборы МИР</i><br/>$</td>
        <td bgcolor="#f2f2f2" valign="top"><i>сборы РФ</i><br/>$</td>
    </tr>
    <tr>
        <td align="left" nowrap><b>Город грехов</b></td>
        <td align="left" nowrap>Sin City</td>
        <td align="left">2005</td>
        <td align="left" nowrap>США</td>
        <td align="left" nowrap>Фрэнк Миллер, Роберт Родригес, Квентин Тарантино</td>
        <td align="left">Брюс Уиллис, Микки Рурк, Клайв Оуэн</td>
        <td align="left" nowrap>боевик, триллер, криминал</td>
        <td align="left" nowrap>16+</td>
        <td align="left">124</td>
        <td align="left">10</td>
        <td align="left">7,955</td>
        <td align="left">114&nbsp;137</td>
        <td align="left">8,20</td>
        <td align="left">517&nbsp;693</td>
        <td align="left">R</td>
        <td align="left">2005-03-28</td>
        <td align="left">2005-05-26</td>
        <td align="left">2005-08-04</td>
        <td align="left" nowrap></td>
        <td align="left">40000000</td>
        <td align="left">74103820</td>
        <td align="left">158733820</td>
        <td align="left">2385000</td>
    </tr>
    <tr>
        <td align="left" nowrap><b>Призрак в доспехах: Синдром одиночки</b></td>
        <td align="left" nowrap>Kokaku kidotai: Stand Alone Complex</td>
        <td align="left">2002 &ndash; 2005</td>
        <td align="left" nowrap>Япония</td>
        <td align="left" nowrap>Кэндзи Камияма, Масаки Тачибана, Ицуро Кавасаки</td>
        <td align="left">Ацуко Танака, Осаму Сака, Дино Андраде</td>
        <td align="left" nowrap>аниме, мультфильм, фантастика</td>
        <td align="left" nowrap>16+</td>
        <td align="left">25</td>
        <td align="left">10</td>
        <td align="left">8,128</td>
        <td align="left">3&nbsp;015</td>
        <td align="left">8,50</td>
        <td align="left">7&nbsp;805</td>
        <td align="left"></td>
        <td align="left">2002-10-01</td>
        <td align="left"></td>
        <td align="left">2008-09-16</td>
        <td align="left" nowrap></td>
        <td align="left"></td>
        <td align="left"></td>
        <td align="left"></td>
        <td align="left"></td>
    </tr>
</table>
</body>
</html>
EOF;
        $result = [
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
                'сборы РФ$'
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
                '114 137',
                '8,20',
                '517 693',
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
                '3 015',
                '8,50',
                '7 805',
                '',
                '2002-10-01',
                '',
                '2008-09-16',
                '',
                '',
                '',
                '',
                '',
            ]
        ];

        $this->assertEquals($result, $parser->parseKinopoiskTable($html));
    }
}

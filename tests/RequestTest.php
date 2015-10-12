<?php

use Kinopoisk2Imdb\Request;

class RequestTest extends PHPUnit_Framework_TestCase
{
    protected $auth;

    protected function setUp()
    {
        $this->auth = 'random_auth';
    }

    public function testSearchMovie()
    {
        $request = new Request($this->auth);

        $map = [
            'xml' => [
                'title'=> 'Kokaku kidotai: Stand Alone Complex',
                'year' => '2002',
                'structure' => <<<EOF
                    <IMDbResults>
                    <ResultSet type="title_popular"><ImdbEntity id="tt0346314">K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex<Description>2002 TV series</Description></ImdbEntity></ResultSet><ResultSet type="title_exact"><ImdbEntity id="tt0442800">K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex<Description>2004 video game,     <a href='/name/nm1825288/'>Junichi Fujisaku</a></Description></ImdbEntity></ResultSet><ResultSet type="title_substring"><ImdbEntity id="tt0856797">K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex Solid State Society<Description>2006 TV movie,     <a href='/name/nm0436784/'>Kenji Kamiyama</a></Description></ImdbEntity><ImdbEntity id="tt1024215">Ghost in the Shell: Stand Alone Complex - The Laughing Man<Description>2005 video,     <a href='/name/nm0436784/'>Kenji Kamiyama</a></Description></ImdbEntity><ImdbEntity id="tt1308130">K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex - Kariudo no ry&#xF4;iki<Description>2005 video game</Description></ImdbEntity></ResultSet>
                    </IMDbResults>
EOF
            ],
            'json' => [
                'title'=> 'Kokaku kidotai: Stand Alone Complex',
                'year' => '2002',
                'structure' => <<<EOF
                    {"title_popular": [{ "id":"tt0346314", "title":"Ghost in the Shell: Stand Alone Complex", "name":"","title_description":"2002 TV series","episode_title":"","description":"2002 TV series"}],"title_exact": [{ "id":"tt0442800", "title":"Ghost in the Shell: Stand Alone Complex", "name":"","title_description":"2004 video game,     <a href='/name/nm1825288/'>Junichi Fujisaku</a>","episode_title":"","description":"2004 video game,     <a href='/name/nm1825288/'>Junichi Fujisaku</a>"}],"title_substring": [{ "id":"tt0856797", "title":"K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex Solid State Society", "name":"","title_description":"2006 TV movie,     <a href='/name/nm0436784/'>Kenji Kamiyama</a>","episode_title":"","description":"2006 TV movie,     <a href='/name/nm0436784/'>Kenji Kamiyama</a>"},{ "id":"tt1024215", "title":"Ghost in the Shell: Stand Alone Complex - The Laughing Man", "name":"","title_description":"2005 video,     <a href='/name/nm0436784/'>Kenji Kamiyama</a>","episode_title":"","description":"2005 video,     <a href='/name/nm0436784/'>Kenji Kamiyama</a>"},{ "id":"tt1308130", "title":"K&#xF4;kaku kid&#xF4;tai: Stand Alone Complex - Kariudo no ry&#xF4;iki", "name":"","title_description":"2005 video game","episode_title":"","description":"2005 video game"}]}
EOF
            ],
            'empty' => [
                'title'=> '',
                'year' => '',
                'structure' => ''
            ]
        ];

        $requestXml = $request->searchMovie('Kokaku kidotai: Stand Alone Complex', '2002', 'xml');
        $this->assertEquals($map['xml']['title'], $requestXml['title']);
        $this->assertEquals($map['xml']['year'], $requestXml['year']);
        $this->assertXmlStringEqualsXmlString(
            $map['xml']['structure'],
            $requestXml['structure']
        );

        $requestJson = $request->searchMovie('Kokaku kidotai: Stand Alone Complex', '2002', 'json');
        $this->assertEquals($map['json']['title'], $requestJson['title']);
        $this->assertEquals($map['json']['year'], $requestJson['year']);
        $this->assertJsonStringEqualsJsonString(
            $map['json']['structure'],
            $requestJson['structure']
        );

        $this->assertEquals($map['empty'], $request->searchMovie(null, null, 'xml'));

        $this->assertEquals($map['empty'], $request->searchMovie(null, null, 'json'));
    }

    public function testOpenMoviePage()
    {
        $requestMock = $this->getMockBuilder('Kinopoisk2Imdb\Request')
            ->setMethods(['openMoviePage'])
            ->disableOriginalConstructor()
            ->getMock();

        $requestMock
            ->expects($this->once())
            ->method('openMoviePage')
            ->will($this->returnValue('html'))
        ;

        $this->assertEquals('html', $requestMock->openMoviePage('tt0346314'));

    }

    public function testChangeMovieRating()
    {
        $request = new Request($this->auth);

        $this->assertJsonStringEqualsJsonString(
            '{"status":403}', $request->changeMovieRating('tt0346314', 10, 'random')
        );
    }

    public function testAddMovieToWatchList()
    {
        $request = new Request($this->auth);

        $this->assertJsonStringEqualsJsonString(
            '{"status":404,"status_message":"Invalid List ID"}',
            $request->addMovieToWatchList('tt0346314', 'ls00000')
        );
    }
} 

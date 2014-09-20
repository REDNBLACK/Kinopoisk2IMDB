<?php
namespace Kinopoisk2Imdb;

/**
 * Class Client
 * @package Kinopoisk2Imdb
 */
class Client
{
    /**
     * @var array
     */
    public $params;

    /**
     * @var Filesystem
     */
    public $fs;

    /**
     * @var Parser
     */
    public $parser;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var Generator
     */
    public $generator;

    /**
     * @var ResourceManager
     */
    public $resourceManager;

    /**
     *
     */
    public function __construct($params)
    {
        $this->params = $params;

        $this->fs = new Filesystem();

        $this->parser = new Parser();

        $this->request = new Request($this->params['auth']);

        $this->generator = new Generator($this->params['file']);
        $this->generator->init();

        $this->resourceManager = new ResourceManager($this->generator->newFileName);
        $this->resourceManager->init();
    }

    /**
     * @param $title
     * @param $year
     * @param $rating
     * @return mixed
     */
    public function submitRating($title, $year, $rating)
    {
        $movie_id = $this->parser->parseMovieId(
            $this->request->searchMovie($title, $year)
        );
        $movie_auth = $this->parser->parseMovieAuthString(
            $this->request->openMoviePage($movie_id)
        );

        $response[] = $this->request->changeMovieRating($movie_id, $rating, $movie_auth);

        return $this->validateResponse($response);
    }

    /**
     * @param $title
     * @param $year
     * @param $list_id
     * @return array
     */
    public function addToWatchlist($title, $year, $list_id)
    {
        $movie_id = $this->parser->parseMovieId(
            $this->request->searchMovie($title, $year)
        );

        $response[] = $this->request->addMovieToWatchList($movie_id, $list_id);

        return $this->validateResponse($response);
    }

    /**
     * @param $title
     * @param $year
     * @param $rating
     * @param $list_id
     * @return array
     */
    public function submitRatingAndAddToWatchlist($title, $year, $rating, $list_id)
    {
        $movie_id = $this->parser->parseMovieId(
            $this->request->searchMovie($title, $year)
        );
        $movie_auth = $this->parser->parseMovieAuthString(
            $this->request->openMoviePage($movie_id)
        );

        $response = [
            $this->request->addMovieToWatchList($movie_id, $list_id),
            $this->request->changeMovieRating($movie_id, $rating, $movie_auth)
        ];

        return $this->validateResponse($response);
    }

    /**
     * @param array $response
     * @return array
     */
    public function validateResponse(array $response)
    {
        foreach ($response as $v) {
            $this->fs->setData($v);
            $this->fs->decodeJson();
            $json = $this->fs->getData();
            if ($json['status'] != 200) {
                return false;
            }
        }

        return true;
    }
}

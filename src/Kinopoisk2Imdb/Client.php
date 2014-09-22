<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Config\Config;

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
     * @param $mode
     * @param $movie_params
     * @return array
     */
    public function submit(array $movie_params, $mode)
    {
        $response = [];
        $movie_id = $this->parser->parseMovieId(
            $this->request->searchMovie($movie_params['title'], $movie_params['year'])
        );

        if ($mode === Config::MODE_ALL || $mode === Config::MODE_LIST_ONLY) {
            $response[] = $this->request->addMovieToWatchList($movie_id, $movie_params['list_id']);
        }
        if ($mode === Config::MODE_ALL || $mode === Config::MODE_RATING_ONLY) {
            $movie_auth = $this->parser->parseMovieAuthString(
                $this->request->openMoviePage($movie_id)
            );

            $response[] = $this->request->changeMovieRating($movie_id, $movie_params['rating'], $movie_auth);
        }

        return $this->validateResponse($response);
    }

    /**
     * @param array $response
     * @return array
     */
    public function validateResponse(array $response)
    {
        if (empty($response)) {
            return false;
        }

        var_dump($response);

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

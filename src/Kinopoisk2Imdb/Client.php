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
     * @var array
     */
    public $errors;

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
    }

    public function setResourceManager($file)
    {
        $this->resourceManager = new ResourceManager($file);
        $this->resourceManager->init();
    }

    public function getResourceManager()
    {
        return $this->resourceManager;
    }

    public function init()
    {
        if ($this->isNewFile($this->params['file'])) {
            $this->generator = new Generator($this->params['file']);
            $this->generator->init();

            $this->setResourceManager($this->generator->newFileName);
        }

        $total_elements = $this->getResourceManager()->countTotalRows();
        for ($element = 0; $element < $total_elements; $element++) {
            $movie_params = array_merge($this->resourceManager->getOneRow(), ['list_id' => $this->params['list_id']]);

            if (!$this->submit($movie_params, $this->params['mode'])) {
                $this->errors[] = $movie_params;
            }
            $this->resourceManager->removeOneRow();
        }
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
     * @param $file
     * @return bool
     */
    public function isNewFile($file)
    {
        $this->fs->setFile($file);
        $old_file_name = $this->fs->getFile();

        $path_parts = pathinfo($old_file_name);
        $new_file_name = $path_parts['filename'] . Config::DEFAULT_NEW_FILE_EXT;
        $this->fs->setFile($new_file_name);

        if ($this->fs->isFileExists()) {
            $this->setResourceManager($new_file_name);

            $new_file_size = $this->getResourceManager()->getSettings('filesize');
            if (is_int($new_file_size) && $new_file_size !== filesize($old_file_name)) {
                unset($this->resourceManager);
                return true;
            }

            return false;
        } else {
            return true;
        }
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

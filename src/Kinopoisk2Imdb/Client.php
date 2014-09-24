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
    public $settings;

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
        $this->settings = $params;

        $this->errors = [];

        $this->fs = new Filesystem();

        $this->parser = new Parser();

        $this->request = new Request($this->settings['auth']);

        set_time_limit(Config::SCRIPT_EXECUTION_LIMIT);
    }

    /**
     *
     */
    public function __destruct()
    {
        $file = $this->fs->setFile($this->settings['file'])->getFile();
        $data = array_merge($this->getResourceManager()->getAllRows(), $this->getErrors());

        $this->fs->setData($data)
            ->addSettingsArray(['filesize' => filesize($file)])
            ->encodeJson()
            ->writeToFile()
        ;
    }

    /**
     * @param $file
     */
    public function setResourceManager($file)
    {
        $this->resourceManager = new ResourceManager($file);
        $this->resourceManager->init();
    }

    /**
     * @return ResourceManager
     */
    public function getResourceManager()
    {
        return $this->resourceManager;
    }

    /**
     * @param array $data
     * @param array $error
     */
    public function setErrors(array $data, array $error)
    {
        $this->errors[] = array_merge($data, ['errors' => $error]);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     *
     */
    public function init()
    {
        if ($this->isNewFile($this->settings['file'])) {
            $this->generator = new Generator($this->settings['file']);
            $this->generator->init();

            $this->setResourceManager($this->generator->newFileName);
        }

        $total_elements = $this->getResourceManager()->countTotalRows();
        for ($element = 0; $element < $total_elements; $element++) {
            sleep(Config::DELAY_BETWEEN_REQUESTS);

            $movie_info = array_merge(
                $this->resourceManager->getOneRow(), [Config::MOVIE_LIST_ID => $this->settings['list_id']]
            );

            $this->submit($movie_info, $this->settings['mode']);
            $this->resourceManager->removeOneRow();
        }
        var_dump($this->errors);
    }

    /**
     * @param $mode
     * @param $movie_info
     * @return bool
     */
    public function submit(array $movie_info, $mode)
    {
        $response = [];
        $movie_id = $this->parser->parseMovieId(
            $this->request->searchMovie(
                $movie_info[Config::MOVIE_TITLE], $movie_info[Config::MOVIE_YEAR], $this->settings['query_format']
            ),
            $this->settings['compare'],
            $this->settings['query_format']
        );

        if ($movie_id !== false) {
            if ($mode === Config::MODE_ALL || $mode === Config::MODE_LIST_ONLY) {
                $response[] = $this->request->addMovieToWatchList($movie_id, $movie_info[Config::MOVIE_LIST_ID]);
            }
            if ($mode === Config::MODE_ALL || $mode === Config::MODE_RATING_ONLY) {
                $movie_auth = $this->parser->parseMovieAuthString(
                    $this->request->openMoviePage($movie_id)
                );

                $response[] = $this->request->changeMovieRating(
                    $movie_id, $movie_info[Config::MOVIE_RATING], $movie_auth
                );
            }

            $validated_response = $this->validateResponse($response);
            if ($validated_response !== true) {
                $this->setErrors($movie_info, ['network_problem' => $validated_response]);

                return false;
            }

            return true;
        } else {
            $this->setErrors($movie_info, ['title_not_found' => 1]);

            return false;
        }
    }

    /**
     * @param $file
     * @return bool
     */
    public function isNewFile($file)
    {
        $old_file_name = $this->fs->setFile($file)->getFile();

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
            return 'empty';
        }

        foreach ($response as $v) {
            $json = $this->fs->setData($v)->decodeJson()->getData();
            if ($json['status'] != 200) {
                return $json['status'];
            }
        }

        return true;
    }
}

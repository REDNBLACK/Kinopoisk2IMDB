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
    private $errors;

    /**
     * @var string
     */
    private $file;

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var ResourceManager
     */
    private $resourceManager;

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
    public function __construct()
    {
        $this->errors = [];

        $this->fs = new Filesystem();

        $this->parser = new Parser();

        set_time_limit(Config::SCRIPT_EXECUTION_LIMIT);
    }

    /**
     *
     */
    public function __destruct()
    {
        if (is_object($this->getResourceManager())) {
            $file = $this->fs->setFile($this->file)->getFile();
            $data = array_merge($this->getResourceManager()->getAllRows(), $this->getErrors());

            $this->fs->setData($data)
                ->addSettingsArray(['filesize' => filesize($file)])
                ->encodeJson()
                ->writeToFile()
            ;
        }
    }

    public function init($request_auth, $file)
    {
        // Устанавливаем Request
        $this->request = new Request($request_auth);

        // Устанавлиаем файл
        $this->file = $file;

        // Проверяем новый ли это файл и устанавливаем Resource Manager
        if ($this->isNewFile($this->file)) {
            $this->generator = new Generator($this->file);
            $this->generator->init();

            $this->setResourceManager($this->generator->newFileName);
        }
    }

    /**
     * @param array $movie_info
     * @param array $options
     * @return bool
     */
    public function submit(array $movie_info, array $options)
    {

        $response = [];

        // Получаем ID фильма
        $movie_id = $this->parser->parseMovieId(
            $this->request->searchMovie(
                $movie_info[Config::MOVIE_TITLE], $movie_info[Config::MOVIE_YEAR], $options['query_format']
            ),
            $options['compare'],
            $options['query_format']
        );

        // Проверям что ID фильма успешно получен
        if ($movie_id !== false) {
            if ($options['list']
                && ($options['mode'] === Config::MODE_ALL || $options['mode'] === Config::MODE_LIST_ONLY)
            ) {
                $response[] = $this->request->addMovieToWatchList($movie_id, $options['list']);
            }

            if ($options['mode'] === Config::MODE_ALL || $options['mode'] === Config::MODE_RATING_ONLY) {
                $movie_auth = $this->parser->parseMovieAuthString(
                    $this->request->openMoviePage($movie_id)
                );

                $response[] = $this->request->changeMovieRating(
                    $movie_id, $movie_info[Config::MOVIE_RATING], $movie_auth
                );
            }

            // Проверяем что ответ true, если нет то наполняем errors ошибками
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

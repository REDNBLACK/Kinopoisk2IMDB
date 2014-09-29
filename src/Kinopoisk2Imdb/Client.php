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
     * @var array Array with errors
     */
    private $errors;

    /**
     * @var string Path to file
     */
    private $file;

    /**
     * @var Filesystem Container
     */
    private $fs;

    /**
     * @var Parser Container
     */
    private $parser;

    /**
     * @var Request Container
     */
    private $request;

    /**
     * @var Generator Container
     */
    private $generator;

    /**
     * @var ResourceManager Container
     */
    private $resourceManager;

    /**
     * Fill the errors array with new error
     * @param array $data
     * @param array $error
     */
    public function setErrors(array $data, array $error)
    {
        $this->errors[] = array_merge($data, ['errors' => $error]);
    }

    /**
     * Get the current array of errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set the resourceManager container
     * @param string $file
     */
    public function setResourceManager($file)
    {
        $this->resourceManager = new ResourceManager($file);
        $this->resourceManager->init();
    }

    /**
     * Get the resourceManager container
     * @return ResourceManager
     */
    public function getResourceManager()
    {
        return $this->resourceManager;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->errors = [];

        $this->fs = new Filesystem();

        $this->parser = new Parser();

        set_time_limit(Config::SCRIPT_EXECUTION_LIMIT);
    }

    /**
     * Destructor
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

    /**
     * Method for main setup of current class
     * @param $request_auth
     * @param $file
     */
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
     * Submit ratings and/or add movies to watchlist
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
        if ($movie_id === false) {
            $this->setErrors($movie_info, ['title_not_found' => 1]);

            return false;
        }

        // Проверяем режим работы и выполняем
        if ($options['mode'] === Config::MODE_ALL || $options['mode'] === Config::MODE_LIST_ONLY) {
            // Проверка что список для добавления указан
            if (!empty($options['list'])) {
                $response[] = $this->request->addMovieToWatchList($movie_id, $options['list']);
            }
        }
        if ($options['mode'] === Config::MODE_ALL || $options['mode'] === Config::MODE_RATING_ONLY) {
            // Проверка что рейтинг содержит в себе число и что оно больше чем 0 и меньше чем 10
            $movie_rating = $movie_info[Config::MOVIE_RATING];
            if (is_numeric($movie_rating) && $movie_rating > 0 && $movie_rating <= 10) {
                $movie_auth = $this->parser->parseMovieAuthString(
                    $this->request->openMoviePage($movie_id)
                );

                $response[] = $this->request->changeMovieRating(
                    $movie_id, $movie_rating, $movie_auth
                );
            }
        }

        // Проверяем что ответ true, если нет то наполняем errors ошибками
        $validated_response = $this->validateResponse($response);
        if ($validated_response !== true) {
            $this->setErrors($movie_info, ['network_problem' => $validated_response]);

            return false;
        }

        return true;
    }

    /**
     * Check if file is new
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
     * Validate response from server
     * @param array $response
     * @return array
     */
    public function validateResponse(array $response)
    {
        if (empty($response)) {
            return 'empty';
        }

        foreach ($response as $v) {
            if (empty($v)) {
                return 'empty';
            }

            $json = $this->fs->setData($v)->decodeJson()->getData();
            if ($json['status'] != 200) {
                return $json['status'];
            }
        }

        return true;
    }
}

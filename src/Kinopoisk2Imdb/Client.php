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
     * @var array Array with options
     */
    private $options;

    /**
     * @var string Path to file
     */
    private $file;

    /**
     * @var FileManager Container
     */
    private $fileManager;

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
        $this->resourceManager = new ResourceManager();
        $this->resourceManager->init($file);
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

        $this->fileManager = new FileManager();

        $this->parser = new Parser();

        set_time_limit(Config::SCRIPT_EXECUTION_LIMIT);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (is_object($this->getResourceManager())) {
            $errors = $this->getErrors();
            if (empty($errors)) {
                $settings = ['status' => 'completed'];
            } else {
                $settings = ['status' => 'with errors'];
            }

            $data = array_merge($this->getResourceManager()->getData(), $this->getErrors());
            $this->resourceManager->saveFormattedData($data, $this->file, $settings);
        }
    }

    /**
     * Method for main setup of current class
     * @param $request_auth
     * @param $options
     * @param $file
     */
    public function init($request_auth, $options, $file)
    {
        // Устанавливаем Request
        $this->request = new Request($request_auth);

        // Устанавливаем настройки
        $this->options = $options;

        // Устанавлиаем файл
        $this->file = $file;

        // Проверяем файл и устанавливаем Resource Manager
        $this->checkFileAndSetup($this->file);
    }

    /**
     * Submit ratings and/or add movies to watchlist
     * @param array $movie_info
     * @return bool
     */
    public function submit(array $movie_info)
    {
        $response = [];

        // Получаем ID фильма
        $movie_id = $this->parser->parseMovieId(
            $this->request->searchMovie(
                $movie_info[Config::MOVIE_TITLE], $movie_info[Config::MOVIE_YEAR], $this->options['query_format']
            ),
            $this->options['compare'],
            $this->options['query_format']
        );

        // Проверям что ID фильма успешно получен
        if ($movie_id === false) {
            $this->setErrors($movie_info, ['title_not_found' => 1]);

            return false;
        }

        // Проверяем режим работы и выполняем
        if ($this->options['mode'] === Config::MODE_ALL || $this->options['mode'] === Config::MODE_LIST_ONLY) {
            // Проверка что список для добавления указан
            if (!empty($this->options['list'])) {
                $response[] = $this->request->addMovieToWatchList($movie_id, $this->options['list']);
            }
        }
        if ($this->options['mode'] === Config::MODE_ALL || $this->options['mode'] === Config::MODE_RATING_ONLY) {
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
     * Check file for various things and setup the Resource Manager
     * @param $file
     * @throws /Exception
     * @return bool
     */
    /* TODO Move to resource manager */
    public function checkFileAndSetup($file)
    {
        if (!$this->fileManager->setFileName($file, false)->files('isFileAndExists')) {
            throw new \Exception('Файл не существует');
        }

        $processed_file_size = $this->fileManager->files('size');
        $generated_file_name = $this->fileManager->files('replaceExtension', false);

        if ($this->fileManager->setFileName($generated_file_name)->files('isFileAndExists')) {
            $this->setResourceManager($generated_file_name);

            $generated_file_size = $this->getResourceManager()->getSettings('filesize');

            if ($generated_file_size !== $processed_file_size) {
                $is_new = true;
            } else {
                $is_new = false;
            }
        } else {
            $is_new = true;
        }

        if ($is_new === true) {
            $this->generator = new Generator();
            $generated_file = $this->generator->init($file);

            $this->setResourceManager($generated_file);
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

            $json = $this->fileManager->setData($v)->decodeJson()->getData();
            if ($json['status'] != 200) {
                return $json['status'];
            }
        }

        return true;
    }
}

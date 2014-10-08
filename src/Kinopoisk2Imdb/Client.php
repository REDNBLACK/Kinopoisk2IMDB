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
    public function setResourceManager($file = null)
    {
        $this->resourceManager = new ResourceManager();

        if ($file !== null) {
            $this->resourceManager->init($file);
        }
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
            $data = $this->getResourceManager()->getData();
            $old_status = $this->getResourceManager()->getSettings('status');
            $errors = $this->getErrors();

            if (empty($errors) && empty($data) && $old_status === 'broken') {
                $settings = ['status' => 'broken'];
            } elseif (empty($errors) && empty($data)) {
                $settings = ['status' => 'completed'];
            } elseif (empty($errors) && !empty($data)) {
                $settings = ['status' => 'uncompleted'];
            } else {
                $settings = ['status' => 'with errors'];
            }

            $this->resourceManager->saveFormattedData(array_merge($data, $errors), $this->file, $settings);
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
        $this->request = new Request($request_auth);
        $this->options = $options;
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

        // Для режимов работы: Добавление только в список или Полный
        if ($this->options['mode'] === Config::MODE_ALL || $this->options['mode'] === Config::MODE_LIST_ONLY) {
            // Проверка что список для добавления указан
            if (!empty($this->options['list'])) {
                $response[] = $this->request->addMovieToWatchList($movie_id, $this->options['list']);
            }
        }

        // Для режимов работы: Только установка рейтинга или Полный
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
        // Проверка существует ли файл
        if (!$this->fileManager->setFileName($file, false)->files('isFileAndExists')) {
            throw new \Exception('Файл не существует');
        }

        $processed_file_size = $this->fileManager->files('size');
        $generated_file_name = $this->fileManager->files('replaceExtension', false);

        // Проверка новый ли файл
        if ($this->fileManager->setFileName($generated_file_name)->files('isFileAndExists')) {
            $this->setResourceManager($generated_file_name);

            $generated_file_size = $this->getResourceManager()->getSettings('filesize');

            // Сравниваем размер указанного файла и файла с JSON схемой, с таким же имененем
            if ($generated_file_size !== $processed_file_size) {
                $is_new = true;
            } else {
                $is_new = false;
            }
        } else {
            $is_new = true;
        }

        // Если файл новый то генерируем для него JSON схему
        if ($is_new === true) {
            $this->generator = new Generator();

            $generated_data = $this->generator->init(
                $this->fileManager->setFileName($file, false)->files('read')->getData()
            );

            if (!empty($generated_data)) {
                $settings = ['status' => 'untouched'];
            } else {
                $settings = ['status' => 'broken'];
            }

            $this->setResourceManager();
            $new_generated_file = $this->getResourceManager()->saveFormattedData($generated_data, $file, $settings);

            $this->setResourceManager($new_generated_file);
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

<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Methods\Arrays;
use Kinopoisk2Imdb\Methods\File;

/**
 * Class FileManager
 * @package Kinopoisk2Imdb
 */
class FileManager
{
    /**
     * Directory up constant
     */
    const DIRECTORY_UP = '..';

    /**
     * Default dir
     */
    const DEFAULT_DIR = 'data';

    /**
     * @var string Current dir
     */
    private $dir;

    /**
     * @var string Current file
     */
    private $fileName;

    /**
     * @var mixed Current data
     */
    private $data;

    /**
     * @var Arrays
     */
    private $arraysMethods;

    /**
     * @var File
     */
    private $filesMethods;


    /**
     * Set the data
     * @param mixed $data
     * @return FileManager
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the data
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set path to file
     * @param string $file Path to file
     * @param bool $relative_path If true - will setup path from the default dir
     * @return FileManager
     */
    public function setFileName($file, $relative_path = true)
    {
        $this->fileName = ($relative_path === true ? $this->dir : '') . $file;

        return $this;
    }

    /**
     * Get path to file
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->dir = implode(
            DIRECTORY_SEPARATOR,
            [__DIR__, self::DIRECTORY_UP, self::DIRECTORY_UP, self::DEFAULT_DIR]
        ) . DIRECTORY_SEPARATOR;
        $this->arraysMethods = new Arrays();
        $this->filesMethods = new File();
    }

    /**
     * Encode the current data to JSON
     * @return mixed
     */
    public function encodeJson()
    {
        $data = $this->getData();
        if (!empty($data)) {
            $this->setData(json_encode($data));

            return $this;
        }

        return false;
    }

    /**
     * Decode the current data from JSON
     * @param bool $to_array If true - decode to array, false - decode to object
     * @return mixed
     */
    public function decodeJson($to_array = true)
    {
        $data = $this->getData();
        if (!empty($data) && is_string($data)) {
            $this->setData(json_decode($data, $to_array));

            return $this;
        }

        return false;
    }

    /**
     * Execute array method from Arrays class and return/setup result
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    public function arrays($method)
    {
        $parameters = array_merge([$this->getData()], array_slice(func_get_args(), 1));
        $result = $this->callMethod($this->arraysMethods, $method, $parameters);

        if (isset($result['reference'])) {
            $this->setData($result['reference']);

            return $this;
        } else {
            return $result;
        }
    }

    /**
     * Execute file method from File class and return/setup result
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    public function files($method)
    {
        $parameters = array_merge([$this->getFileName()], array_slice(func_get_args(), 1));
        $result = $this->callMethod($this->filesMethods, $method, $parameters);

        if (isset($result['reference'])) {
            $this->setData($result['reference']);

            return $this;
        } else {
            return $result;
        }
    }

    /**
     * Method call wrapper
     * @param string $class
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    public function callMethod($class, $method, array $parameters)
    {
        if (!method_exists($class, $method)) {
            throw new \Exception(
                sprintf("Несуществующий метод(%1s) класса(%2s)", $method, is_object($class) ? get_class($class) : $class)
            );
        }

        return call_user_func_array([$class, $method], $parameters);
    }
} 

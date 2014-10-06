<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Config\Config;

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
     * @var ArraysMethods
     */
    private $arraysMethods;

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
            [__DIR__, self::DIRECTORY_UP, self::DIRECTORY_UP, Config::DEFAULT_DIR]
        ) . DIRECTORY_SEPARATOR;
        $this->arraysMethods = new ArraysMethods();
    }

    /**
     * Encode the current data to JSON
     * @return mixed
     */
    public function encodeJson()
    {
        if (!$this->isEmpty()) {
            $this->setData(json_encode($this->getData()));

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
        if (!$this->isEmpty() && $this->isString()) {
            $this->setData(json_decode($this->getData(), $to_array));

            return $this;
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function fileSize()
    {
        if ($this->isFileExists()) {
            return filesize($this->getFileName());
        }

        return false;
    }

    /**
     * Read current file and put to data
     * @return mixed
     */
    public function readFile()
    {
        if ($this->isFileExists()) {
            $this->setData(file_get_contents($this->getFileName()));

            return $this;
        }

        return false;
    }

    /**
     * Write current data to file
     * @param $file_name
     * @param $relative
     * @return mixed
     */
    public function writeToFile($file_name = '', $relative = false)
    {
        if ($this->isFileExists()) {
            $new_file_name = $this->replaceFileExtension();
            file_put_contents($this->setFileName($new_file_name)->getFileName(), $this->getData(), LOCK_EX);

            return $new_file_name;
        }

        return false;
    }

    /**
     * @param string $extension
     * @return string
     */
    public function replaceFileExtension($extension = Config::DEFAULT_NEW_FILE_EXT)
    {
        return pathinfo($this->getFileName())['filename'] . $extension;
    }

    /**
     * Execute array method from ArraysMethods class and return/setup result
     * @param string $method
     * @return mixed
     * @throws \Exception
     */
    public function arrays($method)
    {
        if (method_exists($this->arraysMethods, $method)) {
            $parameters = array_merge([$this->getData()], array_slice(func_get_args(), 1));
            $result = call_user_func_array([$this->arraysMethods, $method], $parameters);

            if (isset($result['reference'])) {
                $this->setData($result['reference']);

                return $this;
            } else {
                return $result;
            }
        } else {
            throw new \Exception('Несуществующий метод массива');
        }
    }

    /**
     * Check if current file is exists
     * @return bool
     */
    public function isFileExists()
    {
        return file_exists($this->getFileName());
    }

    /**
     * Check if current data is empty
     * @return bool
     */
    public function isEmpty()
    {
        $data = $this->getData();

        return empty($data);
    }

    /**
     * Check if current data is string
     * @return bool
     */
    public function isString()
    {
        return is_string($this->getData());
    }

    /**
     * Check if current data is array
     * @return bool
     */
    public function isArray()
    {
        return is_array($this->getData());
    }
} 

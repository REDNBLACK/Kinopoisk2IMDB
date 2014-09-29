<?php
namespace Kinopoisk2Imdb;

use Kinopoisk2Imdb\Config\Config;

/**
 * Class Filesystem
 * @package Kinopoisk2Imdb
 */
class Filesystem
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
    private $file;

    /**
     * @var mixed Current data
     */
    private $data;

    /**
     * Set the data
     * @param mixed $data
     * @return Filesystem
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
     * @param string $file
     * @return Filesystem
     */
    public function setFile($file)
    {
        $this->file = $this->dir . $file;

        return $this;
    }

    /**
     * Get path to file
     * @return string
     */
    public function getFile()
    {
        return $this->file;
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
    }

    /**
     * Check if current file is exists
     * @return bool
     */
    public function isFileExists()
    {
        return file_exists($this->getFile());
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
     * Read current file and put to data
     * @return mixed
     */
    public function readFile()
    {
        if ($this->isFileExists()) {
            $this->setData(file_get_contents($this->getFile()));

            return $this;
        }

        return false;
    }

    /**
     * Write current data to file
     * @return mixed
     */
    public function writeToFile()
    {
        if ($this->isFileExists()) {
            $path_parts = pathinfo($this->getFile());
            $new_file_name = $path_parts['filename'] . Config::DEFAULT_NEW_FILE_EXT;
            file_put_contents(
                $path_parts['dirname'] . DIRECTORY_SEPARATOR . $new_file_name,
                $this->getData(),
                LOCK_EX
            );

            return $new_file_name;
        }

        return false;
    }


    /**
     * Add array to start of the current data
     * @param array $settings
     * @return mixed
     */
    public function addSettingsArray(array $settings)
    {
        $data = $this->getData();
        if (array_unshift($data, $settings)) {
            $this->setData($data);

            return $this;
        }

        return false;
    }

    /**
     * Remove first element from current data array
     * @return mixed
     */
    public function removeFirstArrayElement()
    {
        $data = $this->getData();
        array_shift($data);
        $this->setData($data);

        return $this;
    }

    /**
     * Get last element from current data array
     * @return mixed|string
     */
    public function getOneArrayElement()
    {
        $data = $this->getData();

        return array_pop($data);
    }

    /**
     * Remove last element from current data array
     * @return mixed
     */
    public function removeOneArrayElement()
    {
        $data = $this->getData();
        array_pop($data);
        $this->setData($data);

        return $this;
    }

    /**
     * Count elements in current data
     * @param int $recursive
     * @return int
     */
    public function countElements($recursive = 0)
    {
        return count($this->getData(), $recursive);
    }
} 

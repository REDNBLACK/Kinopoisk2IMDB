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
    private $file;

    /**
     * @var mixed Current data
     */
    private $data;

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
    public function setFile($file, $relative_path = true)
    {
        $this->file = ($relative_path === true ? $this->dir : '') . $file;

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
     * Check if current data is array
     * @return bool
     */
    public function isArray()
    {
        return is_array($this->getData());
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

    public function fileSize()
    {
        if ($this->isFileExists()) {
            return filesize($this->getFile());
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
    public function writeToFile($file_name = '', $relative = false)
    {
        if ($this->isFileExists()) {
            $new_file_name = $this->replaceFileExtension();
            file_put_contents($this->setFile($new_file_name)->getFile(), $this->getData(), LOCK_EX);

            return $new_file_name;
        }

        return false;
    }

    public function replaceFileExtension($extension = Config::DEFAULT_NEW_FILE_EXT)
    {
        return pathinfo($this->getFile())['filename'] . $extension;
    }


    /**
     * Add array to start of the current data
     * @param array $settings
     * @return mixed
     */
    public function addFirstArrayElement(array $settings)
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
    public function getLastArrayElement()
    {
        $data = $this->getData();

        return array_pop($data);
    }

    /**
     * Remove last element from current data array
     * @return mixed
     */
    public function removeLastArrayElement()
    {
        $data = $this->getData();
        array_pop($data);
        $this->setData($data);

        return $this;
    }

    /**
     * Count elements in current data
     * @param bool $recursive
     * @return int
     */
    public function countElements($recursive = false)
    {
        return count($this->getData(), (int) $recursive);
    }
} 
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
     * @const string
     */
    const DIRECTORY_UP = '..';
    /**
     * @var string
     */
    private $dir;
    /**
     * @var string
     */
    private $file;
    /**
     * @var mixed
     */
    private $data;

    /**
     * @param mixed $data
     * @return bool
     */
    public function setData($data)
    {
        $this->data = $data;

        return true;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function setFile($file)
    {
        $this->file = $this->dir . $file;

        return true;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     *
     */
    public function __construct()
    {
        $this->dir = implode(
            DIRECTORY_SEPARATOR,
            [__DIR__, self::DIRECTORY_UP, self::DIRECTORY_UP, Config::DEFAULT_DIR]
        ) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return bool
     */
    public function isFileExists()
    {
        return file_exists($this->getFile());
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        $data = $this->getData();
        return empty($data);
    }

    /**
     * @return bool
     */
    public function isString()
    {
        return is_string($this->getData());
    }

    /**
     * @return bool|string
     */
    public function encodeJson()
    {
        try {
            if (!$this->isEmpty()) {
                $this->setData(json_encode($this->getData()));

                return true;
            }

            return false;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param bool $to_array
     * @return bool|string
     */
    public function decodeJson($to_array = true)
    {
        try {
            if (!$this->isEmpty() && $this->isString()) {
                $this->setData(json_decode($this->getData(), $to_array));

                return true;
            }

            return false;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string
     */
    public function readFile()
    {
        try {
            if ($this->isFileExists()) {
                $this->setData(file_get_contents($this->getFile()));

                return true;
            }

            return false;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string
     */
    public function writeToFile()
    {
        try {
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
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * @return bool|string
     */
    public function removeFirstArrayElement()
    {
        try {
            $data = $this->getData();
            array_shift($data);
            $this->setData($data);

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return mixed|string
     */
    public function getOneArrayElement()
    {
        try {
            $data = $this->getData();

            return array_pop($data);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return bool|string
     */
    public function removeOneArrayElement()
    {
        try {
            $data = $this->getData();
            array_pop($data);
            $this->setData($data);

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param int $recursive
     * @return int
     */
    public function countElements($recursive = 0)
    {
        return count($this->getData(), $recursive);
    }
} 

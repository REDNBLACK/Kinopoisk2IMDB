<?php
namespace Kinopoisk2Imdb;

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
     * @param string $dir
     * @return bool
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return true;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param string $file
     * @return bool
     */
    public function setFile($file)
    {
        $this->file = $file;

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
        $this->setDir(implode(DIRECTORY_SEPARATOR, [__DIR__, self::DIRECTORY_UP, self::DIRECTORY_UP]));
    }

    /**
     * @return bool|string
     */
    public function encodeJson()
    {
        try {
            $this->setData(json_encode($this->getData()));

            return true;
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
            $this->setData(json_decode($this->getData(), $to_array));

            return true;
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
            $this->setData(file_get_contents($this->getFile()));

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param string $extension
     * @return bool|string
     */
    public function writeToFile($extension = '.json')
    {
        try {
            $path_parts = pathinfo($this->file);
            file_put_contents(
                $path_parts['dirname'] . DIRECTORY_SEPARATOR . $path_parts['filename'] . $extension,
                $this->getData(),
                LOCK_EX
            );

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
} 

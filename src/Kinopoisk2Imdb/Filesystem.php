<?php
namespace Kinopoisk2Imdb;

/**
 *
 */
define('DIRECTORY_UP', '..');
/**
 * Class Filesystem
 * @package Kinopoisk2Imdb
 */
class Filesystem
{
    /**
     * @var string
     */
    protected $dir;
    /**
     * @var string
     */
    protected $file;
    /**
     * @var mixed
     */
    protected $data;

    /**
     *
     */
    public function __construct()
    {
        $this->dir = implode(DIRECTORY_SEPARATOR, [__DIR__, DIRECTORY_UP, DIRECTORY_UP]);
    }

    /**
     * @return bool|string
     */
    public function encodeJson()
    {
        try {
            $this->data = json_encode($this->data);

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
            $this->data = json_decode($this->data, $to_array);

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
            $this->data = file_get_contents($this->file);

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
                $this->data,
                LOCK_EX
            );

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
} 

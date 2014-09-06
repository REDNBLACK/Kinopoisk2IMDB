<?php
namespace Kinopoisk2Imdb;

define('DIRECTORY_UP', '..');
class Filesystem
{
    protected $dir;
    protected $file;
    protected $data;

    public function __construct()
    {
        $this->dir = implode(DIRECTORY_SEPARATOR, [__DIR__, DIRECTORY_UP, DIRECTORY_UP]);
    }

    /**
     * @return $this
     */
    public function encodeJson()
    {
        $this->data = json_encode($this->data);
        return $this;
    }

    public function decodeJson($to_array = true)
    {
        $this->data = json_decode($this->data, $to_array);
        return $this;
    }

    public function readFile()
    {
        try {
            $this->data = file_get_contents($this->file);
            return $this;
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
} 

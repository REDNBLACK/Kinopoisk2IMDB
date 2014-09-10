<?php
namespace Kinopoisk2Imdb;

/**
 * Class ResourceManager
 * @package Kinopoisk2Imdb
 */
class ResourceManager extends Filesystem
{
    /**
     * @var string
     */
    protected $file;
    /**
     * @var mixed
     */
    protected $data;
    /**
     * @var array
     */
    protected $settings;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        parent::__construct();
        $this->file = $this->dir . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @return bool
     */
    public function init()
    {
        try {
            $this->readFile();
            $this->decodeJson();
            $this->setSettings();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return mixed
     */
    public function getOneRow()
    {
        return array_shift($this->data);
    }

    /**
     * @return bool
     */
    public function setSettings()
    {
        if (!isset($this->settings)) {
            $this->settings = array_shift($this->data);
            return true;
        }
        return false;
    }

    /**
     * @param null $param
     * @return mixed
     */
    public function getSettings($param = null)
    {
        if (is_null($param) === false) {
            return $this->settings[$param];
        }
        return $this->settings;
    }
} 

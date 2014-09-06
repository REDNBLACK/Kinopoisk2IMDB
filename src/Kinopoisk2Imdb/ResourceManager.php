<?php
namespace Kinopoisk2Imdb;

class ResourceManager extends Filesystem
{
    protected $file;
    protected $data;
    protected $settings;

    public function __construct($file)
    {
        parent::__construct();
        $this->file = $this->dir . DIRECTORY_SEPARATOR . $file;
    }

    public function init()
    {
        return $this->readFile()
            ->decodeJson()
            ->setSettings();
    }

    public function getCurrentData()
    {
        return $this;
    }

    public function getOneRow()
    {
        $row = array_shift($this->data);
        return $row;
    }

    public function setSettings()
    {
        if (!isset($this->settings)) {
            $this->settings = array_shift($this->data);
            return $this;
        }
        return $this;
    }

    public function getSettings($param = null)
    {
        if (is_null($param) === false) {
            return $this->settings[$param];
        }
        return $this->settings;
    }
} 

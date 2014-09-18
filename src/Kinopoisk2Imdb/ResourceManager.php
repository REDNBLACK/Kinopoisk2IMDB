<?php
namespace Kinopoisk2Imdb;

/**
 * Class ResourceManager
 * @package Kinopoisk2Imdb
 */
class ResourceManager
{
    /**
     * @var array
     */
    protected $settings;
    /**
     * @var Filesystem
     */
    public $fs;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->fs = new Filesystem();
        $this->fs->setFile($this->fs->getDir() . DIRECTORY_SEPARATOR . $file);
    }

    /**
     * @return bool
     */
    public function setSettings()
    {
        $data = $this->fs->getData();
        if (!isset($this->settings)) {
            $this->settings = array_shift($data);
            $this->removeOneRow();

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

    /**
     * @return bool
     */
    public function init()
    {
        try {
            $this->fs->readFile();
            $this->fs->decodeJson();
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
        $data = $this->fs->getData();
        $row = array_shift($data);

        return $row;
    }

    /**
     * @return bool
     */
    public function removeOneRow()
    {
        try {
            $data = $this->fs->getData();
            array_shift($data);
            $this->fs->setData($data);

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
} 

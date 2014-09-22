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
    protected $fs;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->fs = new Filesystem();
        $this->fs->setFile($file);
    }

    /**
     * @return bool
     */
    public function setSettings($data)
    {
        if (!isset($this->settings)) {
            $this->settings = array_shift($data);

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
            $this->setSettings($this->fs->getData());
            $this->fs->removeFirstArrayElement();

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
        return $this->fs->getOneArrayElement();
    }

    /**
     * @return bool|string
     */
    public function removeOneRow()
    {
        return $this->fs->removeOneArrayElement();
    }

    /**
     * @return mixed
     */
    public function getAllRows()
    {
        return $this->fs->getData();
    }

    /**
     * @return int
     */
    public function countTotalRows()
    {
        return $this->fs->countElements();
    }
}

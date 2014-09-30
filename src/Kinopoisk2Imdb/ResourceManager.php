<?php
namespace Kinopoisk2Imdb;

/**
 * Class ResourceManager
 * @package Kinopoisk2Imdb
 */
class ResourceManager
{
    /**
     * @var array Current settings
     */
    private $settings;

    /**
     * @var Filesystem Container
     */
    private $fs;

    /**
     * Set current settings
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
     * Get current settings
     * @param string $param
     * @return mixed
     */
    public function getSettings($param = null)
    {
        if ($param !== null) {
            return $this->settings[$param];
        }
        return $this->settings;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fs = new Filesystem();
    }

    /**
     * Method for main setup of current class
     * @param string $file
     * @return bool
     */
    public function init($file)
    {
        // Устанавливаем файл
        $this->fs->setFile($file);
        $this->setSettings(
            $this->fs->readFile()->decodeJson()->getData()
        );
        $this->fs->removeFirstArrayElement();

        return true;
    }

    /**
     * Get last array element
     * @return mixed
     */
    public function getOneRow()
    {
        return $this->fs->getOneArrayElement();
    }

    /**
     * Remove last array element
     * @return bool|string
     */
    public function removeOneRow()
    {
        return $this->fs->removeOneArrayElement();
    }

    /**
     * Get all array elements
     * @return mixed
     */
    public function getAllRows()
    {
        return $this->fs->getData();
    }

    /**
     * Count total array elements
     * @return int
     */
    public function countTotalRows()
    {
        return $this->fs->countElements();
    }
}

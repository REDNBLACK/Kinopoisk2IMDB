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
     * @param string $file
     */
    public function __construct($file)
    {
        $this->fs = new Filesystem();
        $this->fs->setFile($file);
    }

    /**
     * Method for main setup of current class
     * @return bool
     */
    public function init()
    {
        try {
            $this->setSettings(
                $this->fs->readFile()->decodeJson()->getData()
            );
            $this->fs->removeFirstArrayElement();

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
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

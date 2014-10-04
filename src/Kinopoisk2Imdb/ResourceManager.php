<?php
namespace Kinopoisk2Imdb;

/**
 * Class ResourceManager
 * @package Kinopoisk2Imdb
 */
class ResourceManager extends FileManager
{
    /**
     * @var array Current settings
     */
    private $settings;

    /**
     * @var FileManager Container
     */
    private $fileManager;

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
        $this->fileManager = new FileManager();
    }

    /**
     * Method for main setup of current class
     * @param string $file
     * @return bool
     */
    public function init($file)
    {
        // Устанавливаем файл
        $this->fileManager->setFile($file);
        $this->setSettings(
            $this->fileManager->readFile()->decodeJson()->getData()
        );
        $this->fileManager->removeFirstArrayElement();

        return true;
    }

    /**
     * Get last array element
     * @return mixed
     */
    public function getOneRow()
    {
        return $this->fileManager->getOneArrayElement();
    }

    /**
     * Remove last array element
     * @return bool|string
     */
    public function removeOneRow()
    {
        return $this->fileManager->removeOneArrayElement();
    }

    /**
     * Get all array elements
     * @return mixed
     */
    public function getAllRows()
    {
        return $this->fileManager->getData();
    }

    /**
     * Count total array elements
     * @return int
     */
    public function countTotalRows()
    {
        return $this->fileManager->countElements();
    }
}

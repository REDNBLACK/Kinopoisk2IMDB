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
        parent::__construct();
    }

    /**
     * Method for main setup of current class
     * @param string $file
     * @return bool
     */
    public function init($file)
    {
        // Устанавливаем файл
        $this->setFile($file);
        $this->setSettings(
            $this->readFile()->decodeJson()->getData()
        );
        $this->removeFirstArrayElement();

        return true;
    }

    /**
     * @param mixed $data
     * @param string $file
     * @param array $settings
     * @return string
     */
    public function saveFormattedData($data, $file, $settings = [])
    {
        // Устанавливаем файл
        $this->setFile($file, false);

        // Добавляем доп. настройки
        $setting = array_merge(['filesize' => $this->fileSize()], $settings);

        return $this->setData($data)
            ->addFirstArrayElement($setting)
            ->encodeJson()
            ->writeToFile()
        ;
    }
}

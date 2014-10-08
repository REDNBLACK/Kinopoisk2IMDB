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
    public function setSettings()
    {
        if (!isset($this->settings)) {
            $this->settings = $this->arrays('getFirst');
            $this->arrays('removeFirst');

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
        $this->setFileName($file);

        // Устанавливаем настройки
        $this->files('read')->decodeJson()->setSettings();

        return true;
    }

    /**
     * Save data from selected file to json
     * @param mixed $data
     * @param string $file
     * @param array $settings
     * @return string
     */
    public function saveFormattedData($data, $file, $settings = [])
    {
        // Устанавливаем файл
        $this->setFileName($file, false);

        // Если дата пустая то ставим пустой массив в качестве значения
        if (empty($data)) {
            $data = [];
        }

        // Добавляем доп. настройки
        $settings = array_merge(['filesize' => $this->files('size')], $settings);

        return $this
            ->setFileName($this->files('baseName'))
            ->setData($data)
            ->arrays('addFirst', $settings)
            ->encodeJson()
            ->files('write', $this->getData())
        ;
    }
}

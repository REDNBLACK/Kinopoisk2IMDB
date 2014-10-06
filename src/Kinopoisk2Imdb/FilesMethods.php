<?php
namespace Kinopoisk2Imdb;

/**
 * Class FilesMethods
 * @package Kinopoisk2Imdb
 */
class FilesMethods
{
    /**
     * Check if path exists and it's file
     * @param string $file
     * @return int|bool
     */
    public function isFileAndExists($file)
    {
        if (file_exists($file) && is_file($file)) {
            return true;
        }

        return false;
    }

    /**
     * Determine the file size
     * @param string $file
     * @return int|bool
     */
    public function size($file)
    {
        if ($this->isFileAndExists($file)) {
            return filesize($file);
        }

        return false;
    }

    /**
     * Get the file basename (Example: /etc/sources.list to sources.list)
     * @param string $file
     * @return int|string
     */
    public function baseName($file)
    {
        if ($this->isFileAndExists($file)) {
            return basename($file);
        }

        return false;
    }

    /**
     * Read file and return data
     * @param string $file
     * @return string|bool
     */
    public function read($file)
    {
        if ($this->isFileAndExists($file)) {
            return ['reference' => @file_get_contents($file)];
        }

        return false;
    }

    /**
     * Rename file
     * @param string $file
     * @param string $rename_to
     * @return bool
     */
    public function rename($file, $rename_to)
    {
        if ($this->isFileAndExists($file)) {
            return @rename($file, $rename_to);
        }

        return false;
    }

    /**
     * Delete file
     * @param string $file
     * @return bool
     */
    public function delete($file)
    {
        if ($this->isFileAndExists($file)) {
            return @unlink($file);
        }

        return false;
    }

    /**
     * Write current data to file
     * @param string $file
     * @param mixed $data
     * @param bool $is_new_file
     * @return string|bool
     */
    public function write($file, $data, $is_new_file = true)
    {
        if ($is_new_file) {
            $file_name = $this->replaceExtension($file);
        } else {
            $file_name = $file;
        }

        if (file_put_contents($file_name, $data, LOCK_EX)) {
           return basename($file_name);
        }

        return false;
    }

    /**
     * Helper method returning same file name with chosen extension
     * @param string $file
     * @param bool $full_path
     * @param string $extension
     * @return string
     */
    public function replaceExtension($file, $full_path = true, $extension = '.json')
    {
        $path_parts = pathinfo($file);
        $directory = ($full_path === true ? $path_parts['dirname'] . DIRECTORY_SEPARATOR : '');
        $file_name = $path_parts['filename'] . $extension;

        return $directory . $file_name;
    }
}

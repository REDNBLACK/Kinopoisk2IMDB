<?php
namespace Kinopoisk2Imdb;

/**
 * Kinopoisk2Imdb class autoloader
 */
class Autoloader
{

    private $baseDir;

    /**
     * Autoloader constructor.
     *
     * @param string $baseDir Kinopoisk2Imdb library base directory (default: dirname(__FILE__).'/..')
     */
    public function __construct($baseDir = null)
    {
        if ($baseDir === null) {
            $this->baseDir = dirname(__FILE__) . '/..';
        } else {
            $this->baseDir = rtrim($baseDir, '/');
        }
    }

    /**
     * Register a new instance as an SPL autoloader.
     *
     * @param string $baseDir Kinopoisk2Imdb library base directory (default: dirname(__FILE__).'/..')
     *
     * @return Autoloader Registered Autoloader instance
     */
    public static function register($baseDir = null)
    {
        $loader = new self($baseDir);
        spl_autoload_register(array($loader, 'autoload'));

        return $loader;
    }

    /**
     * Autoload Kinopoisk2Imdb classes.
     *
     * @param string $class
     */
    public function autoload($class)
    {
        if ($class[0] === '\\') {
            $class = substr($class, 1);
        }

        if (strpos($class, 'Kinopoisk2Imdb') !== 0) {
            return;
        }

        $file = sprintf('%s/%s.php', $this->baseDir, str_replace('\\', DIRECTORY_SEPARATOR, $class));
        if (is_file($file)) {
            require $file;
        }
    }
}

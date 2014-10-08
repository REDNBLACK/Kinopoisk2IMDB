<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../vendor/autoload.php';

use Kinopoisk2Imdb\Client;
use Kinopoisk2Imdb\Config\Config;

// Настройки
$auth = '**REMOVED**';
$file = '/home/www/rb/kinopoisk2imdb/file.xls';
$options = [
    'mode' => 'all',
    'list' => 'ls075660982',
    'compare' => 'smart',
    'query_format' => 'json'
];

try {
    // Инициализируем клиент
    $client = new Client();
    $client->init($auth, $options, $file);

    // Всего элементов считаем
    $total_elements = $client->getResourceManager()->arrays('count');

    // Включаем очистку буфера
    ob_implicit_flush(true);
    ob_end_flush();

    // Выполняем
    for ($i = 0; $i < $total_elements; $i++) {
        // Задержка
        sleep(Config::DELAY_BETWEEN_REQUESTS);

        // Выполняем колбэк
        $client->submit($client->getResourceManager()->arrays('getLast'));
        $client->getResourceManager()->arrays('removeLast');

        // Выводим
        echo '<script type="text/javascript">document.body.innerHTML = "";</script>';
        printf('<h1 align="center">Обработано %1$d из %2$d фильмов</h1>', $i + 1, $total_elements);
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

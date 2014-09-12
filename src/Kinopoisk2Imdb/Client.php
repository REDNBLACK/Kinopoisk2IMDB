<?php
namespace Kinopoisk2Imdb;

use DOMDocument;
use DOMXPath;

class Client
{
    public $data;
    protected $url;

    /**
     *
     */
    public function __construct()
    {
        $this->url = 'http://www.imdb.com';
    }

    /**
     * Метод отправки запроса и получения ответа на определенный url через cURL
     * @param string $url Ссылка
     * @param string $method Метод запроса, GET или POST
     * @param mixed $additional_headers
     * @return mixed
     */
    public function fetchUrlByCurl($url, $method = 'GET', array $additional_headers = [])
    {
        $options = [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_0,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $additional_headers
        ];
        $ch = curl_init($url);
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    public function searchMovie($title = 'Up', $year = '2009')
    {
        $url_path = '/find?';
        $query = $this->url . $url_path . http_build_query(
            [
                // Запрос
                'q'  => "{$title} ({$year})",
                // Поиск только по названиям
                's' => 'tt',
                // Искать по точному совпадению
                'exact' => 'true'
            ],
            true
        );

        $this->data = $this->fetchUrlByCurl($query);

        return true;
    }

    public function parseHtml()
    {
        try {
            // TODO. Переместить в класс Parser
            libxml_use_internal_errors(true);
            $dom = new DomDocument;
            $dom->loadHTML($this->data);
            libxml_clear_errors();
            $xpath = new DomXPath($dom);

            $query = $xpath->query('//table[@class="findList"]/tr');
            $index = 0;
            unset($this->data);

            foreach ($query as $tr) {
                /** @var DomDocument $tr */
                foreach ($tr->getElementsByTagName('a') as $a) {
                    $this->data[$index][] = $a->getAttribute('href');
                }
                $index++;
            }

            return $this->data;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

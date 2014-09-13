<?php
namespace Kinopoisk2Imdb;

/**
 * Class Parser
 * @package Kinopoisk2Imdb
 */
class Parser
{
    /**
     * @var
     */
    private $data;

    /**
     * @param $data
     * @return bool
     */
    public function setData($data)
    {
        $this->data = $data;
        return true;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return bool
     */
    public function resetData()
    {
        unset($this->data);
        return true;
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function parseKinopoiskTable($data)
    {
        return $this->executeQuery(
            $data,
            "//table//tr",
            function ($query) {
                $data = [];
                $index = 0;

                foreach ($query as $tr) {
                    /** @var \DomDocument $tr */
                    foreach ($tr->getElementsByTagName('td') as $td) {
                        $data[$index][] = $td->nodeValue;
                    }
                    $index++;
                }

                return $data;
            }
        );
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function parseImdbMovieSearchResult($data)
    {
        return $this->executeQuery(
            $data,
            '//table[@class="findList"]/tr',
            function ($query) {
                $data = [];
                $index = 0;

                foreach ($query as $tr) {
                    /** @var \DomDocument $tr */
                    foreach ($tr->getElementsByTagName('a') as $a) {
                        /** @var \DomDocument $a */
                        $data[$index][] = $a->getAttribute('href');
                    }
                    $index++;
                }

                return $data;
            }
        );
    }

    /**
     * @param $data
     * @param bool $disable_errors
     * @return \DomXPath
     */
    public function loadDom($data, $disable_errors = true)
    {
        if ($disable_errors === true) {
            libxml_use_internal_errors(true);
        }

        $dom = new \DomDocument;
        $dom->loadHTML($data);
        $xpath = new \DomXPath($dom);

        if ($disable_errors === true) {
            libxml_clear_errors();
        }

        return $xpath;
    }

    /**
     * @param $data
     * @param $query
     * @param callable $callback
     * @return string
     */
    public function executeQuery($data, $query, \Closure $callback)
    {
        try {
            $query = $this->loadDom($data)->query($query);

            return $callback($query);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}

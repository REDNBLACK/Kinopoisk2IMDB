<?php
namespace Kinopoisk2Imdb\Methods;

/**
 * Class DomDocument
 * @package Kinopoisk2Imdb\Methods
 */
class DomDocument
{
    /**
     * HTML type of document
     */
    const DOCUMENT_HTML = 'HTML';
    /**
     * XML tye of document
     */
    const DOCUMENT_XML = 'XML';

    /**
     * Load string to DomDocument and enable XPath
     * @param  string    $data
     * @param  string    $document_type
     * @param  bool      $xpath
     * @param  bool      $disable_errors
     * @return \DomXPath
     */
    public function loadDom($data, $document_type, $xpath = true, $disable_errors = true)
    {
        if ($disable_errors === true) {
            libxml_use_internal_errors(true);
        }

        $dom = new \DomDocument();
        if ($document_type === self::DOCUMENT_HTML) {
            $dom->loadHTML($data);
        } elseif ($document_type = self::DOCUMENT_XML) {
            $dom->loadXML($data);
        }

        if ($xpath === true) {
            $dom = new \DomXPath($dom);
        }

        if ($disable_errors === true) {
            libxml_clear_errors();
        }

        return $dom;
    }

    /**
     * Execute XPath query on data with specified callback
     * @param  string   $data
     * @param  string   $document_type
     * @param  string   $query
     * @param  callable $callback
     * @return mixed
     */
    public function executeQuery($data, $document_type, $query, \Closure $callback)
    {
        if (empty($data)) {
            return false;
        }

        if ($query === null) {
            $dom = $this->loadDom($data, $document_type, false);
        } else {
            $dom = $this->loadDom($data, $document_type)->query($query);
        }

        return $callback($dom);
    }
}

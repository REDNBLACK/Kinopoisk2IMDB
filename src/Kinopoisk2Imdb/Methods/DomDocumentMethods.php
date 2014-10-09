<?php
namespace Kinopoisk2Imdb\Methods;

/**
 * Class DomDocumentMethods
 * @package Kinopoisk2Imdb\Methods
 */
class DomDocumentMethods
{
    /**
     *
     */
    const DOCUMENT_HTML = 'HTML';
    /**
     *
     */
    const DOCUMENT_XML = 'XML';

    /**
     * Load string to DomDocument and enable XPath
     * @param string $data
     * @param bool $disable_errors
     * @return \DomXPath
     */
    public function loadDom($data, $document_type, $disable_errors = true)
    {
        if ($disable_errors === true) {
            libxml_use_internal_errors(true);
        }

        $dom = new \DomDocument;
        if ($document_type === self::DOCUMENT_HTML) {
            $dom->loadHTML($data);
        } elseif ($document_type = self::DOCUMENT_XML) {
            $dom->loadXML($data);
        }
        $xpath = new \DomXPath($dom);

        if ($disable_errors === true) {
            libxml_clear_errors();
        }

        return $xpath;
    }

    /**
     * Execute XPath query on data with specified callback
     * @param string $data
     * @param string $document_type
     * @param string $query
     * @param callable $callback
     * @return mixed
     */
    public function executeQuery($data, $document_type, $query, \Closure $callback)
    {
        try {
            if (empty($data)) {
                return false;
            }

            $dom = $this->loadDom($data, $document_type);

            if ($query !== null) {
                $dom = $dom->query($query);
            }

            return $callback($dom);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
} 

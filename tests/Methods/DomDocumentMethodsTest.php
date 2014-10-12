<?php

use Kinopoisk2Imdb\Methods\DomDocumentMethods;

class DomDocumentMethodsTest extends PHPUnit_Framework_TestCase
{
    public function testLoadDom()
    {
        $dom = new DomDocumentMethods();

        $this->assertInstanceOf('\DomXPath', $dom->loadDom('<Entity><Element>Simple string</Element></Entity>', 'html'));
        $this->assertInstanceOf('\DomXPath', $dom->loadDom('<html><body>Simple string</body></html>', 'xml'));

        $this->assertInstanceOf(
            '\DomXPath',
            $dom->loadDom('<html><body><span>Simple string</body></html>', 'html', true)
        );

        $this->assertInstanceOf(
            '\DomDocument',
            $dom->loadDom('<html><body><span>Simple string</body></html>', 'html', false)
        );
    }

    public function testExecuteQuery()
    {
        $dom = new DomDocumentMethods();

        $this->assertFalse($dom->executeQuery('', 'HTML', null, function () {}));

        $this->assertEquals(
            'Simple string',
            $dom->executeQuery(
                '<html><body><span>Simple string</span></body></html>',
                'HTML',
                null,
                function ($query) {
                    $result = '';
                    foreach ($query->getElementsByTagName('span') as $span) {
                        $result = $span->nodeValue;
                    }

                    return $result;
                }
            )
        );

        $this->assertEquals(
            'Simple string',
            $dom->executeQuery(
                '<html><body><span>Simple string</span></body></html>',
                'HTML',
                '//span',
                function ($query) {
                    $result = '';
                    foreach ($query as $span) {
                        $result = $span->nodeValue;
                    }

                    return $result;
                }
            )
        );
    }
}

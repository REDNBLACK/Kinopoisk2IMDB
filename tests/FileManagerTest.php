<?php

use Kinopoisk2Imdb\FileManager;

class FileManagerTest extends PHPUnit_Framework_TestCase
{
    public function testSetFileName()
    {
        $fs = new FileManager();

        $this->assertEquals(
            dirname((new ReflectionClass($fs))->getFileName()) . '/../../data/' . 'files/file.log',
            $fs->setFileName('files/file.log')->getFileName()
        );

        $this->assertEquals(
            'files/file.log',
            $fs->setFileName('files/file.log', false)->getFileName()
        );
    }

    public function testSetData()
    {
        $fs = new FileManager();

        $this->assertEquals('string', $fs->setData('string')->getData());
        $this->assertEquals(['array' => ['1', 2, 3.3]], $fs->setData(['array' => ['1', 2, 3.3]])->getData());
    }

    public function testDecodeJson()
    {
        $fs = new FileManager();

        $this->assertFalse($fs->setData('')->decodeJson());

        $this->assertEquals(
            ['simple_array' => '1'],
            $fs->setData('{"simple_array":"1"}')->decodeJson()->getData()
        );

        $obj1 = new stdClass();
        $obj1->simple_array = '1';
        $this->assertEquals(
            $obj1,
            $fs->setData('{"simple_array":"1"}')->decodeJson(false)->getData()
        );

        $this->assertEquals(
            [['simple_multidimensional_array' => 2]],
            $fs->setData('[{"simple_multidimensional_array":2}]')->decodeJson()->getData()
        );

        $obj2 = new stdClass();
        $obj2->simple_multidimensional_array = 2;
        $this->assertEquals(
            [$obj2],
            $fs->setData('[{"simple_multidimensional_array":2}]')->decodeJson(false)->getData()
        );
    }

    public function testEncodeJson()
    {
        $fs = new FileManager();

        $this->assertFalse($fs->setData('')->encodeJson());

        $this->assertEquals(
            '{"simple_array":"1"}',
            $fs->setData(['simple_array' => '1'])->encodeJson()->getData()
        );

        $this->assertEquals(
            '[{"simple_multidimensional_array":2}]',
            $fs->setData([['simple_multidimensional_array' => 2]])->encodeJson()->getData()
        );
    }

    public function testCallMethod()
    {
        $fs = new FileManager();

        $fs->setFileName('someFile.json', false);
        $this->assertEquals('someFile.json', $fs->callMethod($fs, 'getFileName', []));

        $this->setExpectedException('Exception');
        $fs->callMethod('NonExistingClas', 'nonExistingMethod', []);
    }
}

<?php

use Kinopoisk2Imdb\Methods\FilesMethods;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

class FilesMethodsTest extends PHPUnit_Framework_TestCase
{
    public $dir;
    public $file;
    public $data;

    public function setUp()
    {
        $this->file = 'someFile.text';
        $this->dir = 'testDir';
        $this->data = 'Test content of the someFile.text';
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory($this->dir));
    }

    public function testIsFileAndExists()
    {
        $files_methods = new FilesMethods();
        $file = vfsStream::url($this->dir . '/' . $this->file);

        $this->assertFalse($files_methods->isFileAndExists($file));

        vfsStream::newFile($this->file)->at(vfsStreamWrapper::getRoot())->setContent($this->data);

        $this->assertTrue($files_methods->isFileAndExists($file));
    }

    public function testSize()
    {
        $files_methods = new FilesMethods();
        $file = vfsStream::url($this->dir . '/' . $this->file);

        $this->assertFalse($files_methods->size($file));

        vfsStream::newFile($this->file)->at(vfsStreamWrapper::getRoot())->setContent($this->data);

        $this->assertEquals('33', $files_methods->size($file));

    }

    public function testBaseName()
    {
        $files_methods = new FilesMethods();
        $file = vfsStream::url($this->dir . '/' . $this->file);

        $this->assertFalse($files_methods->basename($file));

        vfsStream::newFile($this->file)->at(vfsStreamWrapper::getRoot())->setContent($this->data);

        $this->assertEquals('someFile.text', $files_methods->basename($file));
    }

    public function testRead()
    {
        $files_methods = new FilesMethods();
        $file = vfsStream::url($this->dir . '/' . $this->file);

        $this->assertFalse($files_methods->read($file));

        vfsStream::newFile($this->file)->at(vfsStreamWrapper::getRoot())->setContent($this->data);

        $this->assertEquals(['reference' => $this->data], $files_methods->read($file));
    }

    public function testRename()
    {
        $files_methods = new FilesMethods();
        $file = vfsStream::url($this->dir . '/' . $this->file);
        $new_file = vfsStream::url($this->dir . '/' . 'newFileName.json');

        $this->assertFalse($files_methods->rename($file, $new_file));

        vfsStream::newFile($this->file)->at(vfsStreamWrapper::getRoot())->setContent($this->data);

        $this->assertTrue($files_methods->rename($file, $new_file));

        $this->assertFalse(file_exists($file));

        $this->assertFalse(is_file($file));

        $this->assertTrue(file_exists($new_file) && is_file($new_file));

    }

    public function testDelete()
    {
        $files_methods = new FilesMethods();
        $file = vfsStream::url($this->dir . '/' . $this->file);

        $this->assertFalse($files_methods->delete($file));

        vfsStream::newFile($this->file)->at(vfsStreamWrapper::getRoot())->setContent($this->data);

        $this->assertTrue($files_methods->delete($file));

        $this->assertFalse(file_exists($file));

        $this->assertFalse(is_file($file));
    }

    public function testWrite()
    {
        $files_methods = new FilesMethods();
        $file = vfsStream::url($this->dir . '/' . $this->file);
        $new_data = 'New data for file';

        $this->assertFalse($files_methods->write('', $new_data));

        vfsStream::newFile($this->file)->at(vfsStreamWrapper::getRoot())->setContent($this->data);

        $this->assertEquals('Test content of the someFile.text', file_get_contents($file));

        $this->assertEquals('someFile.json', $files_methods->write($file, $new_data));
        $this->assertEquals('New data for file', file_get_contents(vfsStream::url($this->dir . '/' . 'someFile.json')));

        $this->assertEquals('someFile.text', $files_methods->write($file, $new_data, false));
        $this->assertEquals('New data for file', file_get_contents($file));
    }

    public function testReplaceExtension()
    {
        $files_methods = new FilesMethods();
        $file = $this->dir . '/' . $this->file;

        $this->assertFalse($files_methods->replaceExtension(null));

        $this->assertEquals('testDir/someFile.ext', $files_methods->replaceExtension($file, true, '.ext'));

        $this->assertEquals('./someFile.ext', $files_methods->replaceExtension(basename($file), true, '.ext'));

        $this->assertEquals('someFile.ext', $files_methods->replaceExtension($file, false, '.ext'));
    }
} 

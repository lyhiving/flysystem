<?php

namespace League\Flysystem;

class UtilTests extends \PHPUnit_Framework_TestCase
{
    public function testEmulateDirectories()
    {
        $input = [['dirname' => '', 'filename' => 'dummy'], ['dirname' => 'something', 'filename' => 'dummy']];
        $output = Util::emulateDirectories($input);
        $this->assertCount(3, $output);
    }

    public function testContentSize()
    {
        $this->assertEquals(5, Util::contentSize('12345'));
        $this->assertEquals(3, Util::contentSize('135'));
    }

    public function mapProvider()
    {
        return [
            [['from.this' => 'value'], ['from.this' => 'to.this'], ['to.this' => 'value']],
            [['from.this' => 'value', 'no.mapping' => 'lost'], ['from.this' => 'to.this'], ['to.this' => 'value']],
        ];
    }

    /**
     * @dataProvider  mapProvider
     */
    public function testMap($from, $map, $expected)
    {
        $result = Util::map($from, $map);
        $this->assertEquals($expected, $result);
    }

    public function dirnameProvider()
    {
        return [
            ['filename.txt', ''],
            ['dirname/filename.txt', 'dirname'],
            ['dirname/subdir', 'dirname'],
        ];
    }

    /**
     * @dataProvider  dirnameProvider
     */
    public function testDirname($input, $expected)
    {
        $result = Util::dirname($input);
        $this->assertEquals($expected, $result);
    }

    public function testEnsureConfig()
    {
        $this->assertInstanceOf('League\Flysystem\Config', Util::ensureConfig([]));
        $this->assertInstanceOf('League\Flysystem\Config', Util::ensureConfig('string'));
        $this->assertInstanceOf('League\Flysystem\Config', Util::ensureConfig(null));
        $this->assertInstanceOf('League\Flysystem\Config', Util::ensureConfig(new Config()));
    }

    /**
     * @expectedException  LogicException
     */
    public function testInvalidValueEnsureConfig()
    {
        Util::ensureConfig(false);
    }

    public function invalidPathProvider()
    {
        return [
            ['something/../../../hehe'],
            ['/something/../../..'],
            ['..'],
        ];
    }

    /**
     * @expectedException  LogicException
     * @dataProvider       invalidPathProvider
     */
    public function testOutsideRootPath($path)
    {
        Util::normalizePath('something/../../../hehe');
    }

    public function pathProvider()
    {
        return [
            ['/dirname/', 'dirname'],
            ['dirname/..', ''],
            ['./dir/../././', ''],
            ['00004869/files/other/10-75..stl', '00004869/files/other/10-75..stl'],
            ['/dirname//subdir///subsubdir', 'dirname/subdir/subsubdir'],
            ['\dirname\\\\subdir\\\\\\subsubdir', 'dirname\subdir\subsubdir'],
            ['\\\\some\shared\\\\drive', 'some\shared\drive'],
            ['C:\dirname\\\\subdir\\\\\\subsubdir', 'C:\dirname\subdir\subsubdir'],
            ['C:\\\\dirname\subdir\\\\subsubdir', 'C:\dirname\subdir\subsubdir'],
        ];
    }

    /**
     * @dataProvider  pathProvider
     */
    public function testNormalizePath($input, $expected)
    {
        $result = Util::normalizePath($input);
        $this->assertEquals($expected, $result);
    }

    public function pathAndContentProvider()
    {
        return [
            ['/some/file.css', 'body { background: #000; } ', 'text/css'],
            ['/some/file.txt', 'body { background: #000; } ', 'text/plain'],
            ['/1x1', base64_decode('R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs='), 'image/gif'],
        ];
    }

    /**
     * @dataProvider  pathAndContentProvider
     */
    public function testGuessMimeType($path, $content, $expected)
    {
        $mimeType = Util::guessMimeType($path, $content);
        $this->assertEquals($expected, $mimeType);
    }
}

<?php

namespace Test\ImagesCrawler;

use ImagesCrawler\Url;
use PHPUnit\Framework\TestCase;

final class UrlTest extends TestCase
{
    public function testConstruct()
    {
        $url = new Url('https://example.com/path/?query=true#fragment');

        $this->assertEquals('https', $url->getScheme());
        $this->assertEquals('example.com', $url->getHost());
        $this->assertEquals('/path/', $url->getPath());
        $this->assertEquals('query=true', $url->getQuery());
        $this->assertEquals('fragment', $url->getFragment());
        $this->assertTrue($url->isAbsolute());
        $this->assertFalse($url->isRelative());

        $this->assertEquals('https://example.com/path/?query=true#fragment', $url->getValue());
        $this->assertEquals($url->getValue(), $url->__toString());
    }

    /**
     * @dataProvider hostAndPathNormalizationProvider
     * @param string $domain
     * @param string $relativePath
     * @param string $expectedUrl
     */
    public function testHostAndPathNormalization($domain, $relativePath, $expectedUrl)
    {
        $url = (new Url($relativePath))
            ->withHost($domain)
            ->withNormalizedPath();

        $this->assertEquals($url->getValue(), $expectedUrl);
    }

    public function hostAndPathNormalizationProvider()
    {
        return [
            ['http://www.example.com', '../', 'http://www.example.com/'], #0
            ['http://www.example.com/', './', 'http://www.example.com/'], #1
            ['http://www.example.com', '.', 'http://www.example.com/'], #2
            ['http://www.example.com/', 'a/b/c/./../../g', 'http://www.example.com/a/g'], #3
            ['http://www.example.com', 'mid/content=5/../6', 'http://www.example.com/mid/6'], #4
            ['//www.example.com', 'foo/bar/.', 'http://www.example.com/foo/bar/'], #5
            ['//www.example.com/', 'foo/bar/./', 'http://www.example.com/foo/bar/'], #6
            ['//www.example.com', 'foo/bar/..', 'http://www.example.com/foo/'], #7
            ['//www.example.com/', 'foo/bar/../', 'http://www.example.com/foo/'], #8
            ['//www.example.com', 'foo/bar/../baz', 'http://www.example.com/foo/baz'], #9
            ['www.example.com', '/foo/bar/../..', 'http://www.example.com/'], #10
            ['www.example.com/', '/foo/bar/../../baz', 'http://www.example.com/baz'], #11
            ['www.example.com', 'a/./b/../b/', 'http://www.example.com/a/b/'], #12
            ['www.example.com/', '../abc/#i-fragment', 'http://www.example.com/abc/#i-fragment'], #13
            ['www.example.com', '016shcool/index.htm', 'http://www.example.com/016shcool/index.htm'], #14
        ];
    }
}

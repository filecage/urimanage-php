<?php

namespace UriManage\Tests;

use PHPUnit\Framework\TestCase;
use UriManage\Exceptions\UriException;
use UriManage\Tests\Mocks\_String;
use UriManage\Uri;

class UriTest extends TestCase {

    /**
     * @param string $invalidScheme
     * @see https://www.ietf.org/rfc/rfc1738.txt [Section 2.1]
     *
     * @dataProvider provideInvalidSchemes
     * @return void
     */
    function testExpectsExceptionWhenPassingInvalidScheme (string|bool $invalidScheme) : void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URI scheme');

        $uri = new Uri(null);
        /** @noinspection PhpParamsInspection */
        $uri->withScheme($invalidScheme);
    }

    function provideInvalidSchemes () : array {
        return [
            [false],
            ['☹️'],
            ['i am invalid because of blank spaces'],
            ['0numberatstart'],
            ['no_underscore'],
        ];
    }

    function testExpectsExceptionWhenPassingNonStringHostname () {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URI host type, expected string and got `boolean`');

        $uri = new Uri(null);
        $uri->withHost(false);
    }

    function testExpectsHostToBeReset () {
        $uri = new Uri('http://www.example.com');
        $uri = $uri->withHost(null);

        $this->assertSame('', $uri->getHost());
        $this->assertSame('http:', (string) $uri,
            (string) $uri === 'http://' ? 'A leading `//` is part of the authority, not the scheme' : ''
        );
    }

    function testExpectsQueryToBeReset () {
        $uri = new Uri('http://www.example.com?foo=bar&baz=boo');
        $uri = $uri->withQuery(null);

        $this->assertSame('', $uri->getQuery());
        $this->assertSame('http://www.example.com', (string) $uri);
    }

    function testExpectsSchemeToBeReset () {
        $uri = new Uri('http://www.example.com');
        $uri = $uri->withScheme(null);

        $this->assertSame('', $uri->getScheme());
        $this->assertSame('//www.example.com', (string) $uri);
    }

    /**
     * @param string $uri
     *
     * @return void
     * @throws UriException
     * @dataProvider provideRelativeUris
     */
    function testExpectsUrisToBeRelative (string $uri) : void {
        $uri = new Uri($uri);
        $this->assertFalse($uri->isAbsolute());
    }

    function provideRelativeUris () : array {
        return [
            'Absolute path' => ['/foo.php'],
            'Relative path' => ['../foo'],
        ];
    }

    /**
     * @param string $uri
     *
     * @return void
     * @throws UriException
     * @dataProvider provideAbsoluteUris
     */
    function testExpectsUrisToBeAbsolute (string $uri) : void {
        $uri = new Uri($uri);
        $this->assertTrue($uri->isAbsolute());
    }

    function provideAbsoluteUris () : array {
        return [
            'Scheme, host'     => ['https://www.foobar.com'],
            'Authority only'   => ['//www.foobar.com'],
            'Authority, path'  => ['//www.foobar.com/foo'],
            'Host, port, path' => ['www.foobar.com:8080/foo'],
        ];
    }

    /**
     * This is just to complete missing test cases from the
     * PSR-7 integration tests. Most of the composition tests
     * are done using
     * @param string $uri
     * @param string $expectedCompositionUri
     *
     * @dataProvider provideCompositionUris
     * @return void
     * @throws UriException
     */
    function testExpectsUriComposition (Uri $uri, string $expectedCompositionUri) : void {
        $this->assertSame($uri->compose(), $expectedCompositionUri);
    }

    function provideCompositionUris () : \Generator {
        yield 'Absolute URL, relative path' => [(new Uri('https://www.example.com'))->withPath('foo'), 'https://www.example.com/foo'];
        yield 'Relative URL, absolute path' => [(new Uri('/foo')), '/foo'];
    }

}
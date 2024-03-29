<?php

namespace UriManage\Tests;

use PHPUnit\Framework\TestCase;
use UriManage\Exceptions\UriException;
use UriManage\Uri;

class UriTest extends TestCase {

    /**
     * @param string|bool $invalidScheme
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

    function testExpectsSchemeToBeReset () {
        $uri = new Uri('http://www.example.com');
        $uriWithoutScheme = $uri->withScheme('');

        $this->assertNotSame($uri, $uriWithoutScheme, 'Expected immutability to be kept');
        $this->assertSame('', $uriWithoutScheme->getScheme());
        $this->assertSame('//www.example.com', (string) $uriWithoutScheme);
    }

    /**
     * @dataProvider provideInvalidUserInfo
     */
    function testExpectsExceptionWhenPassingInvalidUserInfo (mixed $invalidUser, mixed $invalidPassword, string $expectedExceptionMessage) {
        $uri = new Uri(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);
        $uri->withUserInfo($invalidUser, $invalidPassword);
    }

    function provideInvalidUserInfo () : \Generator {
        yield 'invalid user type, no password' => [false, null, 'Invalid URI user type: expected `string` but got `boolean` instead'];
        yield 'invalid user type, valid password' => [false, 'foo', 'Invalid URI user type: expected `string` but got `boolean` instead'];
        yield 'valid user type, invalid password' => ['foobar', false, 'Invalid URI password type: expected `string` but got `boolean` instead'];
    }

    function testExpectsUserInfoToBeReset () {
        $uri = new Uri('http://foo:bar@www.example.com');
        $uriWithoutUserInfo = $uri->withUserInfo('');

        $this->assertNotSame($uri, $uriWithoutUserInfo, 'Expected immutability to be kept');
        $this->assertSame('', $uriWithoutUserInfo->getUserInfo());
        $this->assertSame('http://www.example.com', (string) $uriWithoutUserInfo);
    }

    function testExpectsHostToBeReset () {
        $uri = new Uri('http://www.example.com');
        $uriWithoutHost = $uri->withHost('');

        $this->assertNotSame($uri, $uriWithoutHost, 'Expected immutability to be kept');
        $this->assertSame('', $uriWithoutHost->getHost());
        $this->assertSame('http:', (string) $uriWithoutHost,
            (string) $uriWithoutHost === 'http://' ? 'A leading `//` is part of the authority, not the scheme' : ''
        );
    }

    function testExpectsQueryToBeReset () {
        $uri = new Uri('http://www.example.com?foo=bar&baz=boo');
        $uriWithoutQuery = $uri->withQuery('');

        $this->assertNotSame($uri, $uriWithoutQuery, 'Expected immutability to be kept');
        $this->assertSame('', $uriWithoutQuery->getQuery());
        $this->assertSame('http://www.example.com', (string) $uriWithoutQuery);
    }

    function testExpectsQueryToBeSetFromQueryObject () {
        $uri = new Uri('http://www.example.com?foo=bar&baz=boo');
        $query = $uri->getQueryData();

        $queryModified = $query->withParameterKeyAndValue('bar', 'boo');
        $uriWithModifiedQuery = $uri->withQuery($queryModified);

        $this->assertNotSame($uri->getQueryData(), $uriWithModifiedQuery->getQueryData());
        $this->assertNotSame($uri, $uriWithModifiedQuery, 'Expected immutability to be kept');
        $this->assertSame('foo=bar&baz=boo&bar=boo', $uriWithModifiedQuery->getQuery());
        $this->assertSame('http://www.example.com?foo=bar&baz=boo&bar=boo', (string) $uriWithModifiedQuery);
    }

    function testExpectsPortToBeReset () {
        $uri = new Uri('http://www.example.com:1234');
        $uriWithoutPort = $uri->withPort(null);

        $this->assertNotSame($uri, $uriWithoutPort, 'Expected immutability to be kept');
        $this->assertNull($uriWithoutPort->getPort());
        $this->assertSame('http://www.example.com', (string) $uriWithoutPort);
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
     * @param Uri $uri
     * @param string $expectedCompositionUri
     *
     * @dataProvider provideCompositionUris
     * @return void
     */
    function testExpectsUriComposition (Uri $uri, string $expectedCompositionUri) : void {
        $this->assertSame($uri->compose(), $expectedCompositionUri);
    }

    function provideCompositionUris () : \Generator {
        yield 'Absolute URL, relative path' => [(new Uri('https://www.example.com'))->withPath('foo'), 'https://www.example.com/foo'];
        yield 'Relative URL, absolute path' => [(new Uri('/foo')), '/foo'];
    }

    /**
     * @dataProvider provideSetterMethodsWithInvalidTypes
     */
    function testExpectsExceptionWhenInvalidTypesArePassedToUri (string $setterMethod) : void {
        $uri = new Uri(null);

        // Test with `true` and `false` to avoid false-negatives because of checks for truthy/falsy
        foreach ([true, false] as $invalidValue) {
            $this->expectException(\InvalidArgumentException::class);
            $this->expectExceptionMessage('type: expected `string` but got `boolean` instead');

            call_user_func([$uri, $setterMethod], $invalidValue);
        }
    }

    function provideSetterMethodsWithInvalidTypes () : \Generator {
        yield 'scheme' => ['withScheme'];
        yield 'host' => ['withHost'];
        yield 'path' => ['withPath'];
        yield 'query' => ['withQuery'];
        yield 'fragment' => ['withFragment'];
    }

    function testExpectsUriWithAddedPath () {
        $uri = new Uri('https://www.example.com/foo');
        $uri = $uri->withPathAdded('/bar');

        $this->assertSame('https://www.example.com/foo/bar', $uri->compose());
        $this->assertSame('/foo/bar', $uri->getPath());
    }

    function testExpectsUriWithAddedPathEvenIfPathWasEmptyBefore () {
        $uri = new Uri('https://www.example.com');
        $uri = $uri->withPathAdded('/foo/bar');

        $this->assertSame('https://www.example.com/foo/bar', $uri->compose());
        $this->assertSame('/foo/bar', $uri->getPath());
    }

}
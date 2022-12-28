<?php

    namespace UriManage\Tests;

    use PHPUnit\Framework\TestCase;
    use UriManage\Exceptions\UriException;
    use UriManage\Tests\Mocks\_String;
    use UriManage\Uri;

    class UriParserTest extends TestCase {

        function testEnsuresCorrectTreatmentOfPortAndUserInfoForHttpsUrl () {
            $httpsUri = new Uri('https://www.example.com');
            $this->assertSame('', $httpsUri->getUserInfo());
            $this->assertSame(null, $httpsUri->getPort());
            $this->assertSame('https', $httpsUri->getScheme());
            $this->assertSame('www.example.com', $httpsUri->getAuthority());
            $this->assertSame('https://www.example.com', (string) $httpsUri);
        }

        function testParsesProvidedUriWithAllParts() {
            $uri = new Uri('https://user:pass@example.com:8080/path/123?q=abc#test');
            $this->assertSame('https', $uri->getScheme());
            $this->assertSame('user:pass@example.com:8080', $uri->getAuthority());
            $this->assertSame('user:pass', $uri->getUserInfo());
            $this->assertSame('example.com', $uri->getHost());
            $this->assertSame(8080, $uri->getPort());
            $this->assertSame('/path/123', $uri->getPath());
            $this->assertSame('q=abc', $uri->getQuery());
            $this->assertSame('test', $uri->getFragment());
            $this->assertSame('https://user:pass@example.com:8080/path/123?q=abc#test', (string) $uri);
        }

        function testExpectsUriToBeNormalisedButOriginalUrlToBeKept() {
            $uri = new Uri('https://www.example.com:443/foo/');

            $this->assertSame('https://www.example.com/foo/', (string) $uri);
            $this->assertSame('https://www.example.com:443/foo/', $uri->getOriginalUri());
        }

        function testExpectsEmptyUri () {
            $emptyUri = new Uri(null);

            $this->assertEmpty($emptyUri->getScheme());
            $this->assertEmpty($emptyUri->getUserInfo());
            $this->assertEmpty($emptyUri->getHost());
            $this->assertEmpty($emptyUri->getPath());
            $this->assertEmpty($emptyUri->getQuery());
            $this->assertEmpty($emptyUri->getFragment());
            $this->assertNull($emptyUri->getOriginalUri());
        }

        function testExpectsUriFromStringable () {
            $stringable = new _String('http://stringable.com');

            $uri = new Uri($stringable);
            $this->assertSame('http://stringable.com', (string) $uri);
        }

        function testExpectsExceptionForInvalidUri () {
            $expectedException = null;
            try {
                new Uri('http://user@:80');
            } catch (UriException $exception) {
                $expectedException = $exception;
            }

            $this->assertInstanceOf(UriException::class, $expectedException);
            $this->assertStringContainsString('given URL is invalid', $exception->getMessage());
            $this->assertSame('http://user@:80', $exception->getUri());
        }


    }
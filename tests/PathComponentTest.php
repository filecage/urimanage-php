<?php

    namespace Tholabs\UriManage\Tests;

    use PHPUnit\Framework\TestCase;
    use Tholabs\UriManage\Components\Path;

    class PathComponentTest extends TestCase {

        function testExpectsEmptyPath () {
            $path = Path::createFromString('');

            $this->assertFalse($path->isAbsolute(), 'Empty path is expected to be relative');
            $this->assertFalse($path->hasTail(), 'Empty path is expected to have no tail');
            $this->assertEmpty($path->getParts(), 'Empty path is expected to have no parts');
            $this->assertSame('', (string) $path, 'Empty path is expected to be empty string');
        }

        function testExpectsRootPath () {
            $path = Path::createFromString('/');

            $this->assertTrue($path->isAbsolute(), 'Root path is expected to be absolute');
            $this->assertFalse($path->hasTail(), 'Root path is expected to have no tail');
            $this->assertEmpty($path->getParts(), 'Root path is expected to have no parts');
            $this->assertSame('/', (string) $path, 'Root path is expected to be single slash string');
        }

        /**
         * @param string $path
         * @param string $expectedFileExtension
         * @param string $expectedWithoutFileExtension
         *
         * @dataProvider providePathsWithFileExtension
         */
        function testShouldParseFileExtension (string $path, string $expectedFileExtension, string $expectedWithoutFileExtension) {
            $path = Path::createFromString($path);

            $this->assertSame($expectedFileExtension, $path->getFileExtension());

            $pathWithoutExtension = $path->withFileExtensionRemoved();
            $this->assertEmpty($pathWithoutExtension->getFileExtension());
            $this->assertSame($expectedWithoutFileExtension, (string) $pathWithoutExtension);
        }

        /**
         * @return \Generator
         */
        function providePathsWithFileExtension () : \Generator {
            yield 'foo.php' => ['foo.php', 'php', 'foo'];
            yield 'bar/foo.php' => ['bar/foo.php', 'php', 'bar/foo'];
            yield '/bar/foo.php' => ['/bar/foo.php', 'php', '/bar/foo'];
            yield '/bar/foo' => ['/bar/foo', '', '/bar/foo'];
            yield '/😎/😊.😉' => ['/😎/😊.😉', '😉', '/%f0%9f%98%8e/%f0%9f%98%8a'];
        }

        /**
         * @param string $path
         * @param array $parts
         * @dataProvider provideFalsyParameters
         */
        function testExpectsFalsyPathParametersToSurvive (string $path, string $pathString, array $parts) {
            $pathComponent = Path::createFromString($path);
            $this->assertSame($path, $pathComponent->composeUnsanitised(), 'Unsanitised string representation does not match path input');
            $this->assertSame($pathString, (string) $pathComponent, 'Path string representation failed to match expectation');
            $this->assertSame($parts, $pathComponent->getParts(), 'Path parts failed to match expectation');
        }

        /**
         * @return \Generator
         */
        function provideFalsyParameters () : \Generator {
            yield '0' => ['0', '0', ['0']];
            yield '0/1/0/' => ['0/1/0', '0/1/0', ['0', '1', '0']];
            yield '0//0' => ['0//0', '0//0', ['0', '', '0']];
            yield '//0' => ['//0', '/0', ['', '0']];
        }

        /**
         * @param string $path
         * @param bool $isAbsolute
         * @param bool $hasTail
         * @dataProvider providePathStrings
         */
        function testExpectsOutputToBeInput (string $path, bool $isAbsolute, bool $hasTail) {
            $pathComponent = Path::createFromString($path);

            if ($isAbsolute === true) {
                $this->assertTrue($pathComponent->isAbsolute(), 'Path was expected to be parsed as absolute');
            } else {
                $this->assertFalse($pathComponent->isAbsolute(), 'Path was expected to be parsed as relative');
            }

            if ($hasTail === true) {
                $this->assertTrue($pathComponent->hasTail(), 'Path was expected to be parsed with tail');
            } else {
                $this->assertFalse($pathComponent->hasTail(), 'Path was expected to be parsed without tail');
            }

            $this->assertSame($path, (string) $pathComponent);
        }

        /**
         * @return \Generator
         */
        function providePathStrings () : \Generator {
            yield '/foo/bar' => ['/foo/bar', true, false];
            yield 'foo/bar' => ['foo/bar', false, false];
            yield '/foo' => ['/foo', true, false];
            yield 'foo' => ['foo', false, false];
            yield '/foo/bar/baz/' => ['/foo/bar/baz/', true, true];
            yield 'foo/bar/baz/' => ['foo/bar/baz/', false, true];
        }

        /**
         * @param string $path
         * @param string $expectedOutput
         * @dataProvider provideEncodablePathStrings
         */
        function testExpectsPathStringsToBeUrlEncoded (string $path, string $expectedOutput) {
            $this->assertSame($expectedOutput, (string) Path::createFromString($path), 'Output encoding was not as expected');
        }

        /**
         * @return \Generator
         */
        function provideEncodablePathStrings () : \Generator {
            // Also ensure that encoded parts are lowercase but the original path is still treated case-sensitive
            yield '/foo bar/' => ['/foo bar/', '/foo%20bar/'];
            yield '/foo+bar/ba$/bür/' => ['/FOO+bar/BA$/bür/', '/FOO%2bbar/BA%24/b%c3%bcr/'];

            // Prevent double encoding -> expect the same output
            yield '/foo%20bar/' => ['/foo%20bar/', '/foo%20bar/'];
            yield '/foo%2fbar/baz/' => ['/foo%2fbar/baz/', '/foo%2fbar/baz/'];
            yield '/foo%2Fbar/baz/' => ['/foo%2Fbar/baz/', '/foo%2fbar/baz/'];
        }

    }
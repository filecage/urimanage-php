<?php

    namespace Tholabs\UriManage\Tests;

    use PHPUnit\Framework\TestCase;
    use Tholabs\UriManage\Components\Path;

    class PathComponentTest extends TestCase {

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
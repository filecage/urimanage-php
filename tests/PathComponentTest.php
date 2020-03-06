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
            $pathComponent = Path::fromString($path);

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

    }
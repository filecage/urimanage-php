<?php

    namespace Tholabs\UriManage\Tests;

    use Generator;
    use PHPUnit\Framework\TestCase;
    use Tholabs\UriManage\Components\QueryParameter;

    class QueryParameterComponentTest extends TestCase {

        /**
         * @param mixed $value
         * @param bool $expectedIsNull
         * @param bool $expectedBool
         * @param string $expectedString
         * @param string $expectedInt
         *
         * @dataProvider provideTypeCastableParameters
         */
        function testExpectsCorrectParameterCasting ($value, bool $expectedIsNull, bool $expectedBool, string $expectedString, int $expectedInt) {
            $queryParameter = new QueryParameter('test', $value);

            if ($expectedIsNull === true) {
                $this->assertTrue($queryParameter->isNull(), 'Expected QueryParameter to be null');
            } else {
                $this->assertFalse($queryParameter->isNull(), 'Expected QueryParameter to not be null');
            }

            if ($expectedBool === true) {
                $this->assertTrue($queryParameter->getValueAsBool(), 'Expected QueryParameter as boolean to be true');
            } else {
                $this->assertFalse($queryParameter->getValueAsBool(), 'Expected QueryParameter as boolean to be false');
            }

            $this->assertSame($expectedString, $queryParameter->getValueAsString());
            $this->assertSame($expectedInt, $queryParameter->getValueAsInt());
            $this->assertSame($value, $queryParameter->getValuePlain());
        }

        /**
         * @return Generator
         */
        function provideTypeCastableParameters () : Generator {
            yield '(string) foo' => ['foo', false, true, 'foo', 0];
            yield '(string) true' => ['true', false, true, 'true', 0];
            yield '(string) false' => ['false', false, true, 'false', 0];
            yield '(string) 1' => ['1', false, true, '1', 1];
            yield '(string) 0' => ['0', false, false, '0', 0];
            yield 'null' => [null, true, false, '', 0];
            yield '(bool) true' => [true, false, true, '1', 1];
            yield '(bool) false' => [false, false, false, '', 0];
        }

    }
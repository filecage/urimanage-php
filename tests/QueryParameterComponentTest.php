<?php

    namespace UriManage\Tests;

    use Generator;
    use PHPUnit\Framework\TestCase;
    use UriManage\Components\QueryParameters\QueryParameter;
    use UriManage\Components\QueryParameters\StringQueryParameter;

    class QueryParameterComponentTest extends TestCase {

        /**
         * @param mixed $value
         * @param bool $expectedBool
         * @param string $expectedString
         * @param int $expectedInt
         * @throws \InvalidArgumentException
         *
         * @dataProvider provideTypeCastableParameters
         */
        function testExpectsCorrectParameterCasting ($value, bool $expectedBool, string $expectedString, int $expectedInt) {
            $queryParameter = QueryParameter::create('test', $value);

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
            yield '(string) foo' => ['foo', true, 'foo', 0];
            yield '(string) true' => ['true', true, 'true', 0];
            yield '(string) false' => ['false', true, 'false', 0];
            yield '(string) 1' => ['1', true, '1', 1];
            yield '(string) 0' => ['0', false, '0', 0];
            yield 'null' => [null, false, '', 0];
            yield '(bool) true' => [true, true, '1', 1];
            yield '(bool) false' => [false, false, '', 0];
        }

    }
<?php

    namespace Tholabs\UriManage\Tests;

    use PHPUnit\Framework\TestCase;
    use Tholabs\UriManage\Components\Query;
    use Tholabs\UriManage\Components\QueryParameter;

    class QueryComponentTest extends TestCase {

        /**
         * @param string $queryString
         * @param string $expectedQueryString
         * @param array $expectedKeyValuePairs
         *
         * @dataProvider provideParseableQueryStrings
         */
        function testExpectsQueryToBeParsedCorrectly (string $queryString, string $expectedQueryString, array $expectedKeyValuePairs) {
            $query = Query::createFromString($queryString);

            // Assert that parsed output === input
            $this->assertSame($expectedQueryString, (string) $query);

            // Assert all given key/value pairs exist and have correct values
            foreach ($expectedKeyValuePairs as $key => $value) {
                $parameter = $query->getParameter($key);
                $this->assertInstanceOf(QueryParameter::class, $parameter);
                $this->assertSame($key, $parameter->getKey());
                $this->assertSame($value, $parameter->getValuePlain());
            }

            // Cross check
            $this->assertFalse($query->hasParameter('not_exists'));
            $this->assertNull($query->getParameter('not_exists'));
        }

        /**
         * @return \Generator
         */
        function provideParseableQueryStrings () : \Generator {
            yield 'simple parsing and sanitizing' => [
                'foo=bar&baz=true&bax=1&blah=&blubb',
                'foo=bar&baz=true&bax=1&blah=&blubb',
                [
                    'foo' => 'bar',
                    'baz' => 'true',
                    'bax' => '1',
                    'blah' => '',
                    'blubb' => null
                ]
            ];

            yield 'array list parsing' => [
                'foo[]=bar&foo[]=baz&foo[]',
                'foo%5B%5D=bar&foo%5B%5D=baz&foo%5B%5D',
                [
                    'foo' => ['bar', 'baz', null]
                ]
            ];

            yield 'key and parameter escaping' => [
                'schildkröte=turtle&fu/bar=fußball&i%20exist=👍',
                'schildkr%C3%B6te=turtle&fu%2Fbar=fu%C3%9Fball&i%20exist=%F0%9F%91%8D',
                [
                    'schildkröte' => 'turtle',
                    'fu/bar' => 'fußball',
                    'i exist' => '👍'
                ]
            ];
        }

    }
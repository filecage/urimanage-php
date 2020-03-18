<?php

    namespace Tholabs\UriManage\Components;

    use Tholabs\UriManage\Constants\Symbol;

    class Query {

        /**
         * @var QueryParameter[]
         */
        private $queryParameters;

        /**
         * @param string $query
         * @return Query
         */
        static function createFromString (string $query) : Query {
            return new static(...self::mapKeyValuePairsToQueryParameters($query));
        }

        /**
         * @param QueryParameter ...$queryParameters
         */
        function __construct (QueryParameter ...$queryParameters) {
            $this->queryParameters = $queryParameters;
        }

        /**
         * @param string $key
         * @return bool
         */
        function hasParameter (string $key) : bool {
            return $this->getParameter($key) !== null; // todo: we could build an index and read from that instead
        }

        /**
         * @param string $key
         *
         * @return QueryParameter|null
         */
        function getParameter (string $key) : ?QueryParameter {
            foreach ($this->queryParameters as $parameter) {
                if ($parameter->getKey() === $key) {
                    return $parameter;
                }
            }

            return null;
        }

        /**
         * @return string
         */
        function __toString () : string {
            return implode(Symbol::QUERY_PAIR_SEPARATOR, array_map(function($parameter){
                return (string) $parameter;
            }, $this->queryParameters));
        }

        /**
         * @param string $query
         * @return \Generator
         */
        private static function mapKeyValuePairsToQueryParameters (string $query) : \Generator {
            foreach (self::collectKeyValuePairs($query) as $key => $value) {
                yield new QueryParameter($key, $value);
            }
        }

        /**
         * @param string $query
         * @return array
         */
        private static function collectKeyValuePairs (string $query) : array {
            $parameters = [];
            foreach (explode(Symbol::QUERY_PAIR_SEPARATOR, $query) as $parameter) {
                $exploded = explode(Symbol::QUERY_KEYVALUE_SEPARATOR, $parameter);
                $key = rawurldecode($exploded[0]);
                $value = rawurldecode($exploded[1] ?? '');

                if (substr($key, -2) === Symbol::QUERY_ARRAY_SUFFIX) {
                    $key = substr($key, 0, -2);
                    $parameters[$key][] = $value;
                } else {
                    $parameters[$key] = $value;
                }
            }

            return $parameters;
        }

    }
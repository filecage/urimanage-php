<?php

    namespace UriManage\Components;

    use Generator;
    use InvalidArgumentException;
    use UriManage\Components\QueryParameters\QueryParameter;
    use UriManage\Constants\Symbol;

    /**
     * @internal
     */
    final class Query {

        /**
         * @var array<string, QueryParameter>
         */
        private array $queryParameters;

        /**
         * @throws InvalidArgumentException
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
            foreach ($queryParameters as $queryParameter) {
                $this->queryParameters[$queryParameter->getKey()] = $queryParameter;
            }
        }

        /**
         * @param string $key
         * @return bool
         */
        function hasParameter (string $key) : bool {
            return isset($this->queryParameters[$key]);
        }

        /**
         * @param string $key
         *
         * @return QueryParameter|null
         */
        function getParameter (string $key) : ?QueryParameter {
            return $this->queryParameters[$key] ?? null;
        }

        function withParameter (QueryParameter $queryParameter) : self {
            $instance = clone $this;
            $instance->queryParameters[$queryParameter->getKey()] = $queryParameter;

            return $instance;
        }

        function withParameterKeyRemoved (string $queryParameterKey) : self {
            $instance = clone $this;
            unset($instance->queryParameters[$queryParameterKey]);

            return $instance;
        }

        /**
         * @throws InvalidArgumentException
         * @param string $key
         * @param mixed $value
         *
         * @return self
         */
        function withParameterKeyAndValue (string $key, mixed $value) : self {
            return $this->withParameter(QueryParameter::create($key, $value));
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
         * @throws InvalidArgumentException
         * @param string $query
         *
         * @return Generator & iterable<QueryParameter>
         */
        private static function mapKeyValuePairsToQueryParameters (string $query) : Generator {
            foreach (self::collectKeyValuePairs($query) as $key => $value) {
                yield QueryParameter::create($key, $value);
            }
        }

        /**
         * @param string $query
         * @return array<string, array<int, string|null>|string|null>
         */
        private static function collectKeyValuePairs (string $query) : array {
            $parameters = [];
            foreach (explode(Symbol::QUERY_PAIR_SEPARATOR, $query) as $parameter) {
                $exploded = explode(Symbol::QUERY_KEYVALUE_SEPARATOR, $parameter);
                $key = rawurldecode($exploded[0]);
                $value = $exploded[1] ?? null;
                if ($value !== null) {
                    $value = rawurldecode($value);
                }

                if (substr($key, -2) === Symbol::QUERY_ARRAY_SUFFIX) {
                    $key = substr($key, 0, -2);
                    if (!isset($parameters[$key]) || !is_array($parameters[$key])) {
                        $parameters[$key] = [];
                    }

                    $parameters[$key][] = $value;
                } else {
                    $parameters[$key] = $value;
                }
            }

            return $parameters;
        }

    }
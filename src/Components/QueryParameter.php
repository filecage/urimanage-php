<?php

    namespace Tholabs\UriManage\Components;

    use Tholabs\UriManage\Constants\Symbol;

    class QueryParameter {

        /**
         * @var string
         */
        private $key;

        /**
         * @var mixed
         */
        private $value;

        /**
         * @param string $key
         * @param mixed $value
         */
        function __construct (string $key, $value) {
            $this->key = $key;
            $this->value = $value;
        }

        /**
         * @return string
         */
        function getKey () : string {
            return $this->key;
        }

        /**
         * @return mixed
         */
        function getValuePlain () {
            return $this->value;
        }

        /**
         * @return bool
         */
        function getValueAsBool () : bool {
            return (bool) $this->value;
        }

        /**
         * @return string
         */
        function getValueAsString () : string {
            return (string) $this->value;
        }

        /**
         * @return int
         */
        function getValueAsInt () : int {
            return (int) $this->value;
        }

        /**
         * @return string
         */
        function __toString () : string {
            $key = rawurlencode($this->key . (is_array($this->value) ? Symbol::QUERY_ARRAY_SUFFIX : ''));

            if (is_array($this->value)) {
                return implode(Symbol::QUERY_PAIR_SEPARATOR, array_map(function($value) use ($key){
                    return $key . Symbol::QUERY_KEYVALUE_SEPARATOR . rawurlencode($value);
                }, $this->value));
            }

            return $key . Symbol::QUERY_KEYVALUE_SEPARATOR . rawurlencode($this->value);
        }

    }
<?php

    namespace Tholabs\UriManage\Components;

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
         * @return bool
         */
        function isNull () : bool {
            return $this->value === null;
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
    }
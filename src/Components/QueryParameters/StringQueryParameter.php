<?php

    namespace UriManage\Components\QueryParameters;

    /**
     * @internal
     */
    class StringQueryParameter extends QueryParameter {
        private ?string $value;

        function __construct (string $key, ?string $value) {
            parent::__construct($key);
            $this->value = $value;
        }

        function getValuePlain () : ?string {
            return $this->value;
        }

        function getValueAsBool () : bool {
            return (bool) $this->value;
        }

        function getValueAsString () : string {
            return $this->value ?? '';
        }

        function getValueAsInt () : int {
            return (int) $this->value;
        }

    }
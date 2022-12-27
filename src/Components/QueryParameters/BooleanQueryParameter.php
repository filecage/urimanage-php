<?php

namespace UriManage\Components\QueryParameters;

use UriManage\Constants\Symbol;

/**
 * @internal
 */
class BooleanQueryParameter extends QueryParameter {
    private bool $value;

    function __construct (string $key, bool $value) {
        parent::__construct($key);
        $this->value = $value;
    }

    function getValuePlain () : bool {
        return $this->value;
    }

    function getValueAsBool () : bool {
        return $this->value;
    }

    function getValueAsString () : string {
        return (string) $this->value;
    }

    function getValueAsInt () : int {
        return (int) $this->value;
    }

}
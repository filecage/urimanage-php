<?php

namespace UriManage\Components\QueryParameters;


/**
 * @internal
 */
class ArrayQueryParameter extends QueryParameter {

    /** @var array<string|null> */
    private array $value;

    function __construct (string $key, string|null ...$values) {
        parent::__construct($key);
        $this->value = $values;
    }

    /**
     * @return array<string|null>
     */
    function getValuePlain () : array {
        return $this->value;
    }

    function getValueAsBool () : bool {
        return (bool) $this->value;
    }

    function getValueAsString () : string {
        return (string) $this->getValueAsInt();
    }

    function getValueAsInt () : int {
        return (int) $this->value;
    }

}
<?php

namespace UriManage\Components\QueryParameters;


use UriManage\Constants\Symbol;

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
        return empty($this->value) ? 0 : 1;
    }

    function __toString (): string {
        $key = rawurlencode($this->key . Symbol::QUERY_ARRAY_SUFFIX);

        return implode(Symbol::QUERY_PAIR_SEPARATOR, array_map(function($value) use ($key) {
            if ($value === null) {
                return $key;
            }

            return $key . Symbol::QUERY_KEYVALUE_SEPARATOR . rawurlencode($value);
        }, $this->value));
    }
}
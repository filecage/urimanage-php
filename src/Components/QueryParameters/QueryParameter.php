<?php

namespace UriManage\Components\QueryParameters;

use InvalidArgumentException;
use UriManage\Constants\Symbol;

/**
 * @internal
 */
abstract class QueryParameter implements \Stringable {

    /**
     * @throws InvalidArgumentException
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    static function create (string $key, mixed $value) : self {
        if (is_bool($value)) {
            return new BooleanQueryParameter($key, $value);
        } elseif (is_array($value)) {
            return new ArrayQueryParameter($key, ...$value);
        } elseif ($value instanceof \Stringable) {
            $value = (string) $value;
        }

        if ($value !== null && !is_string($value)) {
            throw new InvalidArgumentException("Unsupported query parameter of type `" . gettype($value) . "`");
        }

        return new StringQueryParameter($key, $value);
    }

    function __construct (protected string $key) {}

    function getKey (): string {
        return $this->key;
    }

    abstract function getValuePlain (): mixed;

    abstract function getValueAsBool (): bool;

    abstract function getValueAsString (): string;

    abstract function getValueAsInt (): int;

    function __toString () : string {
        $key = rawurlencode($this->key);
        if ($this->getValuePlain() === null) {
            return $key;
        }

        return $key . Symbol::QUERY_KEYVALUE_SEPARATOR . rawurlencode($this->getValueAsString());
    }
}
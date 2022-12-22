<?php

namespace UriManage\Tests\Mocks;

final class _String implements \Stringable {
    function __construct (private string $string) {}
    function __toString (): string { return $this->string; }
}
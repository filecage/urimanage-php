<?php

    namespace Tholabs\UriManage;

    use Tholabs\UriManage\Components\Path;
    use Tholabs\UriManage\Constants\Component;

    /**
     * @internal
     */
    class UriParser {

        /**
         * Parses the given component
         *
         * @param string $component
         * @param mixed $value
         *
         * @return mixed
         */
        static function parse (string $component, $value) {
            switch ($component) {
                case Component::HOST:
                    return strtolower($value);

                case Component::PATH:
                    return Path::createFromString($value);

                default:
                    return $value;
            }
        }

    }
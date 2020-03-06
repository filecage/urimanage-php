<?php

    namespace Tholabs\UriManage\Components;

    use Tholabs\UriManage\Uri;

    /**
     * @internal
     */
    class Path {

        /**
         * @var bool
         */
        private $absolute;

        /**
         * @var bool
         */
        private $hasTail;

        /**
         * @var string[]
         */
        private $parts;

        /**
         * @param string $path
         * @return Path
         */
        static function fromString (string $path) : Path {
            $absolute = ($path[0] ?? null) === Uri::URL_PARAMETER_PATH_SEPARATOR;
            $hasTail = ($path[-1] ?? null) === Uri::URL_PARAMETER_PATH_SEPARATOR;
            $parts = explode(Uri::URL_PARAMETER_PATH_SEPARATOR, trim($path, Uri::URL_PARAMETER_PATH_SEPARATOR));

            return new static($absolute, $hasTail, ...$parts);
        }

        /**
         * @param bool $absolute
         * @param bool $hasTail
         * @param string ...$parts
         */
        function __construct (bool $absolute, bool $hasTail, string ...$parts) {
            $this->absolute = $absolute;
            $this->hasTail = $hasTail;
            $this->parts = $parts;
        }

        /**
         * @return bool
         */
        function isAbsolute () : bool {
            return $this->absolute;
        }

        /**
         * @return bool
         */
        function hasTail () : bool {
            return $this->hasTail;
        }

        /**
         * @return array
         */
        function getParts () : array {
            return $this->parts;
        }

        /**
         * @return string
         */
        function __toString () : string {
            return ($this->isAbsolute() ? Uri::URL_PARAMETER_PATH_SEPARATOR : '')
                .  implode(Uri::URL_PARAMETER_PATH_SEPARATOR, $this->parts)
                .  ($this->hasTail() ? Uri::URL_PARAMETER_PATH_SEPARATOR : '')
            ;
        }
    }
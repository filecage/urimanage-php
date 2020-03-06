<?php

    namespace Tholabs\UriManage\Components;

    use Tholabs\UriManage\Constants\Symbol;

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
        static function createFromString (string $path) : Path {
            $absolute = ($path[0] ?? null) === Symbol::PATH_SEPARATOR;
            $hasTail = ($path[-1] ?? null) === Symbol::PATH_SEPARATOR;
            $parts = explode(Symbol::PATH_SEPARATOR, trim($path, Symbol::PATH_SEPARATOR));

            // We're decoding here to prevent double-encoding - a safer solution would be to never encode implicitly
            // but PSR-7 requires us to output the path encoded following RFC 3986
            $parts = array_map('rawurldecode', $parts);

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
            return ($this->isAbsolute() ? Symbol::PATH_SEPARATOR : '')
                .  implode(Symbol::PATH_SEPARATOR, array_map([$this, 'encodePathPart'], $this->parts))
                .  ($this->hasTail() ? Symbol::PATH_SEPARATOR : '')
            ;
        }

        /**
         * @param string $part
         * @return string
         */
        private function encodePathPart (string $part) : string {
            // To ensure compliance to php-http/psr7-integration-tests, we have to lowercase all replaced url entities
            // PSR-7 itself does not specify this and allows upper- as well as lowercase
            return preg_replace_callback('/%[0-9A-F]{2}/', function(array $urlEntity){
                return strtolower($urlEntity[0]);
            }, rawurlencode($part));
        }
    }
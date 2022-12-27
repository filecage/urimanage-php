<?php

    namespace UriManage\Components;

    use UriManage\Constants\Symbol;

    /**
     * @internal
     */
    final class Path {

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
         * @var string
         */
        private $fileExtension;

        /**
         * @param string $path
         * @return Path
         */
        static function createFromString (string $path) : Path {
            $absolute = ($path[0] ?? null) === Symbol::PATH_SEPARATOR;
            $hasTail = ($path[-1] ?? null) === Symbol::PATH_SEPARATOR;

            // Remove leading and tailing path separators, but only exactly one (meaning we can't use PHP's `trim()`)
            if ($absolute) $path = substr($path, 1);
            if ($hasTail) $path = substr($path, 0, -1);

            $parts = explode(Symbol::PATH_SEPARATOR, $path);

            if (strlen($path) <= 1) {
                $fileExtension = ''; // No file extension present
                $hasTail = false; // Path strings with 1 character or less can not have tails
                $parts = $parts === [''] ? [] : $parts; // If there is only a slash, there are no parts
            } else {
                $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
            }


            // We're decoding here to prevent double-encoding - a safer solution would be to never encode implicitly
            // but PSR-7 requires us to output the path encoded following RFC 3986
            $parts = array_map('rawurldecode', $parts);

            return new static($absolute, $hasTail, $fileExtension, ...$parts);
        }

        /**
         * @param bool $absolute
         * @param bool $hasTail
         * @param string $fileExtension
         * @param string ...$parts
         */
        function __construct (bool $absolute, bool $hasTail, string $fileExtension, string ...$parts) {
            $this->absolute = $absolute;
            $this->hasTail = $hasTail;
            $this->parts = $parts;
            $this->fileExtension = $fileExtension;
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
         * @return string[]
         */
        function getParts () : array {
            return $this->parts;
        }

        /**
         * @return Path
         */
        function withFileExtensionRemoved () : Path {
            $path = (string) $this;
            $pathInfo = pathinfo($path);
            $pathWithoutFileExtension = isset($pathInfo['dirname']) ? $pathInfo['dirname'] . Symbol::PATH_SEPARATOR : '';
            $pathWithoutFileExtension = $pathWithoutFileExtension . $pathInfo['filename'];
            $pathWithoutFileExtension = trim($pathWithoutFileExtension, Symbol::FILE_EXTENSION_SEPARATOR);

            // Remove leading slash if there was none before (pathinfo might add it)
            if (($path[0] ?? '') !== Symbol::PATH_SEPARATOR) {
                $pathWithoutFileExtension = ltrim($pathWithoutFileExtension, Symbol::PATH_SEPARATOR);
            }

            return self::createFromString($pathWithoutFileExtension);
        }

        /**
         * @return string
         */
        function getFileExtension () {
            return $this->fileExtension;
        }

        /**
         * Builds the path as-is, so without any normalisation applied
         * This might be dangerous in some cases, e.g. when allowing multiple slashes
         * in a URI that has no authority part
         *
         * (It would allow XSS or open redirects by omitting the scheme, but setting a new
         * authority like `//malicious-website.com/foo/bar`).
         *
         * @see https://framework.zend.com/security/advisory/ZF2015-05.html
         * @see https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2015-3257
         * @see https://github.com/php-http/psr7-integration-tests/pull/54
         *
         * @return string
         * @see Path::compose
         */
        function composeUnsanitised () : string {
            return ($this->isAbsolute() ? Symbol::PATH_SEPARATOR : '')
                .  implode(Symbol::PATH_SEPARATOR, array_map([$this, 'encodePathPart'], $this->parts))
                .  ($this->hasTail() ? Symbol::PATH_SEPARATOR : '')
                ;
        }

        /**
         * Builds the path and applies normalisation
         *
         * @return string
         * @see Path::composeUnsanitised()
         */
        function compose () : string {
            $path = $this->composeUnsanitised();

            return preg_replace('/^([\/]+)/', '/', $path) ?? '';
        }

        function __toString () : string {
            return $this->compose();
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
            }, rawurlencode($part)) ?? '';
        }
    }
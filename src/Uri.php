<?php

    namespace UriManage;

    use InvalidArgumentException;
    use Stringable;
    use Psr\Http\Message\UriInterface;
    use UriManage\Actions\Compose;
    use UriManage\Components\Path;
    use UriManage\Components\Query;
    use UriManage\Constants\Component;
    use UriManage\Exceptions\UriException;

    class Uri implements UriInterface {
        protected ?string $scheme;
        protected ?string $user;
        protected ?string $pass;
        protected ?string $host;
        protected ?int $port;
        protected ?Path $path = null;
        protected ?Query $query = null;
        protected ?string $fragment;
        protected ?string $originalUri;
        protected ?Uri $baseUri;
        protected bool $hasBaseUrlRemoved = false;

        /**
         * @param string|Stringable|null $uri
         *
         * @throws UriException
         */
        function __construct (string|Stringable|null $uri) {
            if ($uri === null) {
                return;
            } elseif ($uri instanceof Stringable) {
                $uri = (string) $uri;
            }

            $uriComponents = parse_url($uri);
            if ($uriComponents === false) {
                throw new UriException('Cannot create instance of ' . __CLASS__ . ', given URL is invalid', $uri);
            }

            $this->originalUri = $uri;
            foreach ($uriComponents as $component => $value) {
                $this->$component = UriParser::parse($component, $value);
            }
        }

        /**
         * @return string
         */
        function getFragment () : string {
            return $this->fragment ?? '';
        }

        /**
         * @return string
         */
        function getUserInfo () : string {
            if (!$this->hasUserInfo()) {
                return '';
            }

            if (($this->pass ?? '') === '') {
                return $this->user ?? '';
            }

            return sprintf('%s:%s', $this->user, $this->pass);
        }

        /**
         * @return string
         */
        function getAuthority () : string {
            if (!$this->hasUserInfo()) {
                if ($this->isDefaultPortForScheme() || $this->getPort() === null) {
                    return $this->getHost();
                }

                return sprintf('%s:%d', $this->getHost(), $this->getPort());
            }

            if ($this->isDefaultPortForScheme() || $this->getPort() === null) {
                return sprintf('%s@%s', $this->getUserInfo(), $this->getHost());
            }

            return sprintf('%s@%s:%d', $this->getUserInfo(), $this->getHost(), $this->getPort());
        }

        /**
         * @return string
         */
        function getHost () : string {
            return $this->host ?? '';
        }

        /**
         * @return string
         */
        function getPath () : string {
            return (string) ($this->path ?? '');
        }

        /**
         * @internal
         * @see Path::composeUnsanitised()
         * @return string
         */
        function getPathUnsanitised () : string {
            return $this->path?->composeUnsanitised() ?? '';
        }

        /**
         * @return int|null
         */
        function getPort () : ?int {
            if ($this->isDefaultPortForScheme()) {
                return null;
            }

            return $this->port ?? null;
        }

        /**
         * @return string
         */
        function getQuery () : string {
            return (string) ($this->query ?? '');
        }

        /**
         * @return string
         */
        function getScheme () : string {
            return $this->scheme ?? '';
        }

        /**
         * @param string|null $query
         * @return Uri
         */
        function withQuery ($query) : Uri {
            $instance = clone $this;

            if ($query === '' || $query === null) {
                unset($instance->query);
            } else {
                $instance->query = Query::createFromString($query);
            }

            return $instance;
        }


        /**
         * @param string|null $host
         *
         * @return Uri
         */
        function withHost ($host) : Uri {
            if ($host !== null && !is_string($host)) {
                throw new InvalidArgumentException("Invalid URI host type, expected string and got `" . gettype($host) . "`");
            }

            $instance = clone $this;

            if ($host === '' | $host === null) {
                unset($instance->host);
            } else {
                $instance->host = strtolower($host);
            }

            return $instance;
        }

        /**
         * @param string $path
         *
         * @return Uri
         */
        function withPath ($path) : Uri {
            if (!is_string($path) && !$path instanceof Path) {
                throw new InvalidArgumentException("Invalid URI path type, expected string and got `" . gettype($path) . "`");
            }

            $instance = clone $this;
            $instance->path = ($path instanceof Path) ? $path : UriParser::parse(Component::PATH, $path);

            return $instance;
        }

        /**
         * @throws InvalidArgumentException
         * @param string $scheme
         * @return Uri
         */
        function withScheme ($scheme) : Uri {
            if (!is_string($scheme) && $scheme !== null) {
                throw new InvalidArgumentException("Invalid URI scheme type, expected string and got `" . gettype($scheme) . "`");
            }

            // Unset scheme if it's set empty
            if ($scheme === '' || $scheme === null) {
                $instance = clone $this;
                unset($instance->scheme);

                return $instance;
            }

            $scheme = strtolower($scheme);

            if (!preg_match('/^[a-z][a-z0-9-\.\+]+$/', $scheme)) {
                throw new InvalidArgumentException("Invalid URI scheme: got `{$scheme}` but must start with a letter and may only contain letters, digits, and -/+/.");
            }

            $instance = clone $this;
            $instance->scheme = $scheme;

            return $instance;
        }

        /**
         * @param string $user
         * @param string|null $password
         * @return Uri
         */
        function withUserInfo ($user, $password = null) : Uri {
            $instance = clone $this;
            $instance->user = $user;
            $instance->pass = $password;

            return $instance;
        }

        /**
         * @param int|null $port
         * @return Uri
         */
        function withPort ($port) : Uri {
            $instance = clone $this;
            $instance->port = $port;

            return $instance;
        }

        /**
         * @param string $fragment
         * @return Uri
         */
        function withFragment ($fragment) : Uri {
            $instance = clone $this;
            $instance->fragment = $fragment;

            return $instance;
        }

        /**
         * @return ?string
         */
        function getOriginalUri () : ?string {
            return $this->originalUri ?? null;
        }

        /**
         * @return bool
         */
        function isAbsolute () : bool {
            return $this->getScheme() !== '' || $this->getAuthority() !== '';
        }

        function compose () : string {
            return Compose::compose($this);
        }

        /**
         * @return string
         */
        function __toString () : string {
            return $this->compose();
        }

        /**
         * Ensures a deep Uri cloning
         *
         * TODO: Test that ensures that immutability is kept
         */
        function __clone () {
            if ($this->path) $this->path = clone $this->path;
            if ($this->query) $this->query = clone $this->query;
        }

        /**
         * @return bool
         */
        private function isDefaultPortForScheme () : bool {
            if (!isset($this->port)) {
                return false; // If there is no port it also can't be the default one :)
            }

            return (DefaultSchemePorts::PORT_TO_SCHEME[$this->port] ?? null) === $this->getScheme();
        }

        /**
         * @return bool
         */
        private function hasUserInfo () : bool {
            return ($this->user ?? null) !== null;
        }

    }
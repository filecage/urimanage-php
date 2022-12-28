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
        protected ?string $scheme = null;
        protected ?string $user = null;
        protected ?string $pass = null;
        protected ?string $host = null;
        protected ?int $port = null;
        protected ?Path $path = null;
        protected ?Query $query = null;
        protected ?string $fragment = null;
        protected ?string $originalUri = null;

        /**
         * @throws InvalidArgumentException
         * @throws UriException
         * @param string|Stringable|null $uri
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
                /** @psalm-suppress InvalidScalarArgument TODO: Remove when psalm supports `ArrayShape` attribute */
                match ($component) {
                    Component::HOST => $this->host = UriParser::parseHost($value),
                    Component::PATH => $this->path = UriParser::parsePath($value),
                    Component::QUERY => $this->query = UriParser::parseQuery($value),
                    default => $this->$component = $value
                };
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
            if ($this->user === null) {
                return '';
            }

            if ($this->pass === null) {
                return $this->user;
            }

            return sprintf('%s:%s', $this->user, $this->pass);
        }

        /**
         * @return string
         */
        function getAuthority () : string {
            if ($this->user === null) {
                if ($this->isDefaultPortForScheme() || $this->port === null) {
                    return $this->getHost();
                }

                return sprintf('%s:%d', $this->getHost(), $this->port);
            }

            if ($this->isDefaultPortForScheme() || $this->port === null) {
                return sprintf('%s@%s', $this->getUserInfo(), $this->getHost());
            }

            return sprintf('%s@%s:%d', $this->getUserInfo(), $this->getHost(), $this->port);
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
         * Allows accessing the query data as object
         *
         * @return Query
         */
        function getQueryData () : Query {
            return $this->query ?? new Query();
        }

        /**
         * @return string
         */
        function getScheme () : string {
            return $this->scheme ?? '';
        }

        /**
         * @throws InvalidArgumentException
         * @param string|Query $query
         * @return static
         */
        function withQuery ($query) : static {
            if (!is_string($query) && !$query instanceof Query) {
                throw new InvalidArgumentException("Invalid URI query type: expected `string` but got `" . gettype($query) . "` instead");
            }

            $instance = clone $this;
            $instance->query = ($query === '') ? null : (($query instanceof Query) ? $query : UriParser::parseQuery($query));

            return $instance;
        }


        /**
         * @throws InvalidArgumentException
         * @param string $host
         *
         * @return static
         */
        function withHost ($host) : static {
            if (!is_string($host)) {
                throw new InvalidArgumentException("Invalid URI host type: expected `string` but got `" . gettype($host) . "` instead");
            }

            $instance = clone $this;

            if ($host === '') {
                $instance->host = null;
            } else {
                $instance->host = UriParser::parseHost($host);
            }

            return $instance;
        }

        /**
         * @throws InvalidArgumentException
         * @param Path|string $path
         *
         * @return static
         */
        function withPath ($path) : static {
            if (!is_string($path) && !$path instanceof Path) {
                throw new InvalidArgumentException("Invalid URI path type: expected `string` but got `" . gettype($path) . "` instead");
            }

            $instance = clone $this;
            $instance->path = ($path instanceof Path) ? $path : UriParser::parsePath($path);

            return $instance;
        }

        /**
         * @throws InvalidArgumentException
         * @param string $scheme
         *
         * @return static
         */
        function withScheme ($scheme) : static {
            // Unset scheme if it's set empty
            if ($scheme === '') {
                $instance = clone $this;
                unset($instance->scheme);

                return $instance;
            }

            if (!is_string($scheme)) {
                throw new InvalidArgumentException("Invalid URI scheme type: expected `string` but got `" . gettype($scheme) . "` instead");
            }

            $scheme = strtolower($scheme);

            if (!preg_match('/^[a-z][a-z0-9-.+]+$/', $scheme)) {
                throw new InvalidArgumentException("Invalid URI scheme: got `{$scheme}` but must start with a letter and may only contain letters, digits, and -/+/.");
            }

            $instance = clone $this;
            $instance->scheme = $scheme;

            return $instance;
        }

        /**
         * @throws InvalidArgumentException
         * @param string $user
         * @param string|null $password
         * @return static
         */
        function withUserInfo ($user, $password = null) : static {
            if (!is_string($user)) {
                throw new InvalidArgumentException("Invalid URI user type: expected `string` but got `" . gettype($user) . "` instead");
            }

            if ($password !== null && !is_string($password)) {
                throw new InvalidArgumentException("Invalid URI password type: expected `string` but got `" . gettype($password) . "` instead");
            }

            $instance = clone $this;
            $instance->user = ($user !== '') ? $user : null;
            $instance->pass = ($password !== '') ? $password : null;

            return $instance;
        }

        /**
         * @param null|int $port
         * @return static
         */
        function withPort ($port) : static {
            if ($port === null) {
                $instance = clone $this;
                $instance->port = null;

                return $instance;
            }

            if (!is_int($port)) {
                // PSR-7 is unclear about how to handle valid ports in a string format
                throw new InvalidArgumentException("Invalid URI port type: expected `int` but got `" . gettype($port) . "` instead");
            }

            if ($port > 65535 || $port < 0) {
                throw new InvalidArgumentException("Invalid URI port value: must be between 0-65535, got `{$port}` instead");
            }

            $instance = clone $this;
            $instance->port = $port;

            return $instance;
        }

        /**
         * @throws InvalidArgumentException
         * @param string $fragment
         * @return static
         */
        function withFragment ($fragment) : static {
            if (!is_string($fragment)) {
                throw new InvalidArgumentException("Invalid URI fragment type: expected `string` but got `" . gettype($fragment) . "` instead");
            }

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
         * @return bool
         */
        private function isDefaultPortForScheme () : bool {
            if (!isset($this->port)) {
                return false; // If there is no port it also can't be the default one :)
            }

            return (DefaultSchemePorts::PORT_TO_SCHEME[$this->port] ?? null) === $this->getScheme();
        }

    }
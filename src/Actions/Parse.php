<?php

    namespace UriManage\Actions;

    use InvalidArgumentException;
    use UriManage\Components\Path;
    use UriManage\Components\Query;

    /**
     * @internal
     */
    final class Parse {

        /**
         * Unreserved characters for use in a regex.
         *
         * @see https://tools.ietf.org/html/rfc3986#section-2.3
         */
        private const CHAR_UNRESERVED = 'a-zA-Z0-9_\-\.~';

        /**
         * Sub-delims for use in a regex.
         *
         * @see https://tools.ietf.org/html/rfc3986#section-2.2
         */
        private const CHAR_SUB_DELIMS = '!\$&\'\(\)\*\+,;=';

        static function parseHost (string $host) : string {
            return strtolower($host);
        }

        static function parseUserInfo (?string $userInfo) : ?string {
            if ($userInfo === null) {
                return null;
            }

            // Taken from https://github.com/guzzle/psr7/blob/815698d9f11c908bc59471d11f642264b533346a/src/Uri.php#L592-L603
            return preg_replace_callback(
                '/(?:[^%'.self::CHAR_UNRESERVED.self::CHAR_SUB_DELIMS.']+|%(?![A-Fa-f0-9]{2}))/',
                fn(array $match) => rawurlencode($match[0]),
                $userInfo
            );
        }

        static function parsePath (string $path) : Path {
            return Path::createFromString($path);
        }

        /**
         * @throws InvalidArgumentException
         * @param string $query
         *
         * @return Query
         */
        static function parseQuery (string $query) : Query {
            return Query::createFromString($query);
        }
    }
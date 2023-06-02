<?php

    namespace UriManage\Actions;

    use InvalidArgumentException;
    use UriManage\Components\Path;
    use UriManage\Components\Query;

    /**
     * @internal
     */
    final class Parse {
        static function parseHost (string $host) : string {
            return strtolower($host);
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
<?php

    namespace UriManage;

    use InvalidArgumentException;
    use UriManage\Components\Path;
    use UriManage\Components\Query;
    use UriManage\Constants\Component;

    /**
     * @internal
     */
    class UriParser {
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
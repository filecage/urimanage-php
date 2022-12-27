<?php

    namespace UriManage\Exceptions;

    class UriException extends \Exception {
        private string $uri;

        /**
         * @param string $message
         * @param string $uri
         *
         * @internal
         */
        function __construct(string $message, string $uri) {
            parent::__construct($message);
            $this->uri = $uri;
        }

        function getUri () : string {
            return $this->uri;
        }

    }
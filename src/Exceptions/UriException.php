<?php

    namespace UriManage\Exceptions;

    class UriException extends \Exception {
        private string $url;

        /**
         * @internal
         * @param string $message
         * @param string $url
         */
        function __construct(string $message, string $url) {
            parent::__construct($message);
            $this->url = $url;
        }

    }
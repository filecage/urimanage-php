<?php

    namespace UriManage\Exceptions;

    class UriException extends \Exception {

        /**
         * @var int
         */
        private $url;

        /**
         * @internal
         * @param string $message
         * @param int $url
         */
        function __construct($message, $url) {
            parent::__construct($message);
            $this->url = $url;
        }

    }
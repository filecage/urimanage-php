<?php

    namespace UriManage\Constants;

    /**
     * @internal
     */
    final class DefaultSchemePorts {

        const SCHEME_TO_PORT = [
            'http' => 80,
            'https' => 443,
            'ftp' => 21,
            'ssh' => 22
        ];

        const PORT_TO_SCHEME = [
            80 => 'http',
            443 => 'https',
            21 => 'ftp',
            22 => 'ssh'
        ];

    }
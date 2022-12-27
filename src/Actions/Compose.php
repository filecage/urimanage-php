<?php

namespace UriManage\Actions;

use UriManage\Constants\Symbol;
use UriManage\Uri;

/**
 * Used to concatenate the different strings into a URI string
 *
 * ⚠️ The interface of this class is most likely subject to change,
 * so you should not use it in your userland.
 *
 * @internal
 * @see Uri::compose an API-stable access
 */
final class Compose {

    static function compose (Uri $uri) : string {
        $uriString = '';
        if ($uri->isAbsolute()) {
            $uriString = self::addScheme($uriString, $uri);
            $uriString = self::addAuthority($uriString, $uri);
            $uriString = self::addPathForAbsoluteUrl($uriString, $uri);
        } else {
            $uriString = self::addPathForRelativeUrl($uriString, $uri);
        }

        $uriString = self::addQuery($uriString, $uri);
        $uriString = self::addFragment($uriString, $uri);

        return $uriString;
    }

    private static function addScheme (string $buffer, Uri $uri) : string {
        $scheme = $uri->getScheme();
        if ($scheme !== '') {
            $buffer .= $scheme . ':';
        }

        return $buffer;
    }

    private static function addAuthority (string $buffer, Uri $uri) : string {
        $authority = $uri->getAuthority();
        if ($authority !== '') {
            $buffer .= '//' . $authority;
        }

        return $buffer;
    }

    private static function addPathForRelativeUrl (string $buffer, Uri $uri) : string {
        return $buffer . $uri->getPath();
    }

    private static function addPathForAbsoluteUrl (string $buffer, Uri $uri) : string {
        $path = $uri->getPathUnsanitised();
        if ($path !== '') {
            // Add path separator symbol if the path doesn't have it yet
            if (substr($path, 0, 1) !== Symbol::PATH_SEPARATOR) {
                $buffer .= Symbol::PATH_SEPARATOR;
            }

            $buffer .= $path;
        }

        return $buffer;
    }

    private static function addQuery (string $buffer, Uri $uri) : string {
        $query = $uri->getQuery();
        if ($query !== '') {
            $buffer .= Symbol::QUERY_SEPARATOR . $query;
        }

        return $buffer;
    }

    private static function addFragment (string $buffer, Uri $uri) : string {
        $fragment = $uri->getFragment();
        if ($fragment !== '') {
            $buffer .= Symbol::FRAGMENT_SEPARATOR . $fragment;
        }

        return $buffer;
    }

}
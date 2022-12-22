<?php

    namespace UriManage\Tests;

    use Http\Psr7Test\UriIntegrationTest;
    use UriManage\Exceptions\UriException;
    use UriManage\Uri;

    class Psr7ComplianceTest extends UriIntegrationTest {

        const TEST_URI = 'http://login:pass@secure.example.com:443/test/query.php?kingkong=toto#doc3';

        /**
         * @var Uri
         */
        protected $uri;

        /**
         * @throws UriException
         */
        function setUp () : void {
            $this->uri = new Uri(self::TEST_URI);
        }

        /**
         * @param string $uri
         * @return Uri
         * @throws UriException
         */
        function createUri ($uri) {
            return new Uri($uri);
        }

        /**
         * @param string $scheme
         * @param string $expected
         * @group scheme
         * @dataProvider provideSchemes
         *
         * The value returned MUST be normalized to lowercase, per RFC 3986
         * Section 3.1.
         */
        function testGetScheme(string $scheme, string $expected) {
            $uri = $this->uri->withScheme($scheme);
            $this->assertInstanceOf(Uri::class, $uri);
            $this->assertSame($expected, $uri->getScheme(), 'Scheme must be normalized according to RFC3986');
        }

        /**
         * @return array
         */
        function provideSchemes() : array {
            return [
                'normalized scheme'   => ['HtTpS', 'https'],
                'simple scheme'       => ['http', 'http'],
                'no scheme'           => ['', ''],
                'special char scheme' => ['foo+bar-baz.boo123', 'foo+bar-baz.boo123']
            ];
        }

        /**
         * @group userinfo
         * @dataProvider userInfoProvider
         *
         * If a user is present in the URI, this will return that value;
         * additionally, if the password is also present, it will be appended to the
         * user value, with a colon (":") separating the values.
         *
         */
        function testGetUserInfo(string $user, ?string $pass, string $expected) {
            $uri = $this->uri->withUserInfo($user, $pass);
            $this->assertInstanceOf(Uri::class, $uri);
            $this->assertSame($expected, $uri->getUserInfo(), 'UserInfo must be normalized according to RFC3986');
        }

        /**
         * @return array
         */
        function userInfoProvider() : array {
            return [
                'with userinfo'  => ['iGoR', 'rAsMuZeN', 'iGoR:rAsMuZeN'],
                'no userinfo'    => ['', '', ''],
                'no pass'        => ['iGoR', '', 'iGoR'],
                'pass is null'   => ['iGoR', null, 'iGoR'],
                'case sensitive' => ['IgOr', 'RaSm0537', 'IgOr:RaSm0537'],
            ];
        }
    }
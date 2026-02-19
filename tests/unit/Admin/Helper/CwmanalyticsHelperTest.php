<?php

/**
 * Unit tests for CwmanalyticsHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmanalyticsHelper — focuses on pure static methods
 * that don't require a database connection.
 *
 * @since  10.1.0
 */
class CwmanalyticsHelperTest extends ProclaimTestCase
{
    // -------------------------------------------------------------------------
    // classifyReferrer() tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider referrerProvider
     */
    public function testClassifyReferrer(string $url, string $utmMedium, string $expectedType): void
    {
        $result = CwmanalyticsHelper::classifyReferrer($url, $utmMedium);
        self::assertSame($expectedType, $result['type']);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function referrerProvider(): array
    {
        return [
            'empty url is direct'               => ['', '', 'direct'],
            'empty url with email utm is email' => ['', 'email', 'email'],
            'google search is organic'          => ['https://www.google.com/search?q=sermon', '', 'organic'],
            'google.co.uk is organic'           => ['https://www.google.co.uk/search?q=test', '', 'organic'],
            'bing is organic'                   => ['https://www.bing.com/search?q=faith', '', 'organic'],
            'duckduckgo is organic'             => ['https://duckduckgo.com/?q=bible', '', 'organic'],
            'yahoo is organic'                  => ['https://search.yahoo.com/search?p=sermon', '', 'organic'],
            'facebook is social'                => ['https://www.facebook.com/share', '', 'social'],
            'instagram is social'               => ['https://www.instagram.com/p/xyz', '', 'social'],
            'youtube is social'                 => ['https://www.youtube.com/watch?v=abc', '', 'social'],
            'x.com is social'                   => ['https://x.com/user/status/123', '', 'social'],
            'twitter.com is social'             => ['https://twitter.com/user/status/123', '', 'social'],
            'linkedin is social'                => ['https://www.linkedin.com/posts/abc', '', 'social'],
            'reddit is social'                  => ['https://www.reddit.com/r/christian', '', 'social'],
            'threads is social'                 => ['https://www.threads.net/@user', '', 'social'],
            'unknown domain is other'           => ['https://www.someblog.com/post', '', 'other'],
            'url with email utm is email'       => ['https://mailchimp.com/track', 'email', 'email'],
            'tiktok is social'                  => ['https://www.tiktok.com/@user/video', '', 'social'],
            'pinterest is social'               => ['https://www.pinterest.com/pin/123', '', 'social'],
        ];
    }

    public function testClassifyReferrerReturnsDomain(): void
    {
        $result = CwmanalyticsHelper::classifyReferrer('https://www.google.com/search?q=test');
        self::assertSame('google.com', $result['domain']);
    }

    public function testClassifyReferrerEmptyDomainForDirect(): void
    {
        $result = CwmanalyticsHelper::classifyReferrer('');
        self::assertSame('', $result['domain']);
    }

    // -------------------------------------------------------------------------
    // classifyUserAgent() tests
    // -------------------------------------------------------------------------

    /**
     * @dataProvider userAgentProvider
     */
    public function testClassifyUserAgent(string $ua, string $device, string $browser, string $os): void
    {
        $result = CwmanalyticsHelper::classifyUserAgent($ua);
        self::assertSame($device, $result['device'], "Device mismatch for UA: $ua");
        self::assertSame($browser, $result['browser'], "Browser mismatch for UA: $ua");
        self::assertSame($os, $result['os'], "OS mismatch for UA: $ua");
    }

    /**
     * @return array<string, array{string, string, string, string}>
     */
    public static function userAgentProvider(): array
    {
        return [
            'empty ua' => [
                '',
                'unknown', 'other', 'other',
            ],
            'chrome on windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                'desktop', 'Chrome', 'Windows',
            ],
            'firefox on linux' => [
                'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/121.0',
                'desktop', 'Firefox', 'Linux',
            ],
            'safari on macOS' => [
                'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15',
                'desktop', 'Safari', 'macOS',
            ],
            'edge on windows' => [
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0 Safari/537.36 Edg/120.0',
                'desktop', 'Edge', 'Windows',
            ],
            'chrome on iphone' => [
                'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
                'mobile', 'Safari', 'iOS',
            ],
            'android mobile' => [
                'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 Mobile Safari/537.36 Chrome/120.0',
                'mobile', 'Chrome', 'Android',
            ],
            'ipad' => [
                'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Safari/604.1',
                'tablet', 'Safari', 'iOS',
            ],
            'opera' => [
                'Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/120.0 Safari/537.36 OPR/106.0',
                'desktop', 'Opera', 'Windows',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // isOptedOut() tests
    // -------------------------------------------------------------------------

    public function testIsOptedOutDoesNotThrow(): void
    {
        unset($_SERVER['HTTP_DNT'], $_COOKIE['proclaim_analytics_optout']);
        // In unit-test context Cwmparams/Factory may not be fully available,
        // but the method must not throw — it gracefully defaults.
        self::assertIsBool(CwmanalyticsHelper::isOptedOut());
    }

    public function testIsOptedOutDoesNotThrowWithDntHeader(): void
    {
        $_SERVER['HTTP_DNT'] = '1';
        self::assertIsBool(CwmanalyticsHelper::isOptedOut());
        unset($_SERVER['HTTP_DNT']);
    }
}

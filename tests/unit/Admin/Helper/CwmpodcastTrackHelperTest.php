<?php

/**
 * Unit tests for CwmpodcastTrackHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmpodcastTrackHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Pure-logic tests for the podcast download tracking helper (#1281):
 * bot detection and client fingerprinting used by the tracking redirect.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmpodcastTrackHelper::class)]
class CwmpodcastTrackHelperTest extends ProclaimTestCase
{
    public static function userAgentProvider(): array
    {
        return [
            // Excluded — crawlers, monitors, link previews, scripted fetches.
            'empty is bot'      => ['', true],
            'whitespace is bot' => ['   ', true],
            'googlebot'         => ['Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)', true],
            'bingbot'           => ['Mozilla/5.0 (compatible; bingbot/2.0)', true],
            'facebook preview'  => ['facebookexternalhit/1.1', true],
            'curl'              => ['curl/8.4.0', true],
            'wget'              => ['Wget/1.21.3', true],
            'python-requests'   => ['python-requests/2.31.0', true],
            'ahrefs'            => ['Mozilla/5.0 (compatible; AhrefsBot/7.0)', true],
            'pingdom monitor'   => ['Pingdom.com_bot_version_1.4', true],

            // Counted — real podcast clients / prefetchers must NOT be excluded.
            'apple podcasts'  => ['Apple Podcasts/1580.5', false],
            'applecoremedia'  => ['AppleCoreMedia/1.0.0.21G93 (iPhone; U; CPU OS 17_6)', false],
            'spotify'         => ['Spotify/9.0.10 iOS/17.5', false],
            'overcast'        => ['Overcast/2024.7 (+http://overcast.fm/; iPhone)', false],
            'itunes'          => ['iTunes/12.12 (Macintosh; OS X 14.5)', false],
            'castbox'         => ['CastBox/8.0 (Linux; Android 14)', false],
            'generic browser' => ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Safari/605.1.15', false],
        ];
    }

    #[DataProvider('userAgentProvider')]
    public function testIsBotClassifiesUserAgents(string $userAgent, bool $expected): void
    {
        $this->assertSame($expected, CwmpodcastTrackHelper::isBot($userAgent));
    }

    public function testClientHashIsDeterministicAndDistinct(): void
    {
        $a = CwmpodcastTrackHelper::clientHash('203.0.113.7', 'Apple Podcasts/1580.5');
        $b = CwmpodcastTrackHelper::clientHash('203.0.113.7', 'Apple Podcasts/1580.5');
        $c = CwmpodcastTrackHelper::clientHash('203.0.113.8', 'Apple Podcasts/1580.5');
        $d = CwmpodcastTrackHelper::clientHash('203.0.113.7', 'Spotify/9.0.10');

        // Same inputs -> same 40-char sha1.
        $this->assertSame($a, $b);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{40}$/', $a);

        // A different IP or UA -> a different fingerprint.
        $this->assertNotSame($a, $c);
        $this->assertNotSame($a, $d);
    }

    public function testClientHashIgnoresSurroundingWhitespace(): void
    {
        $this->assertSame(
            CwmpodcastTrackHelper::clientHash('203.0.113.7', 'Apple Podcasts/1580.5'),
            CwmpodcastTrackHelper::clientHash('  203.0.113.7  ', '  Apple Podcasts/1580.5  ')
        );
    }

    public function testResolveGuidReturnsStoredWithoutTouchingDatabase(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // A frozen guid is returned verbatim; the legacy value is ignored and no
        // write occurs (media id 0 would be an invalid write target anyway).
        $this->assertSame(
            'https://example.org/media/frozen.mp3',
            CwmpodcastTrackHelper::resolveGuid(
                $db,
                0,
                'https://example.org/media/frozen.mp3',
                'https://example.org/media/CHANGED.mp3'
            )
        );
    }

    public function testResolveGuidFallsBackToLegacyWhenNothingToStamp(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // No stored value and no valid media id -> emit legacy without persisting.
        $this->assertSame(
            'https://example.org/media/new.mp3',
            CwmpodcastTrackHelper::resolveGuid($db, 0, null, 'https://example.org/media/new.mp3')
        );
    }
}

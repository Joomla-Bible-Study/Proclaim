<?php

/**
 * Unit tests for CwmmediaStreamer::candidatePath()
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmmediaStreamer;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #2045.
 *
 * A media URL carries the site's own base path — `/joomla` for a site at
 * `https://example.com/joomla/` — and JPATH_ROOT already ends in that same
 * segment. Joining the two without removing it first wrote the segment twice,
 * so `realpath()` failed and every local file looked like it lived on someone
 * else's server.
 *
 * ⚠️ That failure is invisible from the outside. Ordinary media survived it,
 * because the streamer falls through to re-fetching its own URL over HTTP and
 * the web server answers. A file in `images/biblestudy/protected/` cannot: the
 * deny rules refuse that fetch, which is the entire point of the directory. So
 * protected storage was inoperable on subdirectory installs while System
 * Health reported the folder healthy.
 *
 * These assertions are on the join alone, which is why the join was separated
 * from the filesystem checks around it. No web server, no fixture, and no
 * dependence on where this test happens to be checked out.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmmediaStreamer::class)]
class CwmmediaStreamerPathTest extends ProclaimTestCase
{
    /**
     * @return  array<string, array{0: string, 1: string, 2: string, 3: string}>
     */
    public static function joins(): array
    {
        return [
            // target, base, root, expected
            'domain root' => [
                'https://example.com/images/biblestudy/protected/x.mp3',
                '',
                '/var/www/html',
                '/var/www/html/images/biblestudy/protected/x.mp3',
            ],
            'subdirectory install' => [
                'https://example.com/joomla/images/biblestudy/protected/x.mp3',
                '/joomla',
                '/var/www/html/joomla',
                '/var/www/html/joomla/images/biblestudy/protected/x.mp3',
            ],
            'nested subdirectory install' => [
                'https://example.com/sites/church/images/biblestudy/media/x.mp3',
                '/sites/church',
                '/var/www/html/sites/church',
                '/var/www/html/sites/church/images/biblestudy/media/x.mp3',
            ],
            // ⚠️ A base with a trailing slash must behave identically. Uri::root(true)
            // does not add one, but nothing stops a caller passing Uri::root().
            'base carrying a trailing slash' => [
                'https://example.com/joomla/images/x.mp3',
                '/joomla/',
                '/var/www/html/joomla',
                '/var/www/html/joomla/images/x.mp3',
            ],
            // The segment appears twice for real: the site is at /joomla and the
            // media genuinely lives in a folder of the same name. Only the
            // leading one is the base.
            'path repeating the base name' => [
                'https://example.com/joomla/joomla/x.mp3',
                '/joomla',
                '/var/www/html/joomla',
                '/var/www/html/joomla/joomla/x.mp3',
            ],
            'percent-encoded filename' => [
                'https://example.com/images/biblestudy/media/a%20file.mp3',
                '',
                '/var/www/html',
                '/var/www/html/images/biblestudy/media/a file.mp3',
            ],
            // Already rooted: pass through, and do not strip a base from it.
            'absolute filesystem path' => [
                '/var/www/html/joomla/images/biblestudy/protected/x.mp3',
                '/joomla',
                '/var/www/html/joomla',
                '/var/www/html/joomla/images/biblestudy/protected/x.mp3',
            ],
            'root given with a trailing slash' => [
                'https://example.com/images/x.mp3',
                '',
                '/var/www/html/',
                '/var/www/html/images/x.mp3',
            ],
        ];
    }

    /**
     * @param   string  $target    Target passed to the streamer.
     * @param   string  $base      Site base path.
     * @param   string  $root      Web root.
     * @param   string  $expected  Path the join must produce.
     *
     * @return  void
     */
    #[DataProvider('joins')]
    #[TestDox('A URL lands at the same place on disk however the site is installed')]
    public function testJoin(string $target, string $base, string $root, string $expected): void
    {
        $this->assertSame($expected, CwmmediaStreamer::candidatePath($target, $base, $root));
    }

    #[TestDox('A subdirectory install does not repeat its own base path')]
    public function testSubdirectoryBaseIsNotDoubled(): void
    {
        $got = CwmmediaStreamer::candidatePath(
            'https://example.com/joomla/images/biblestudy/protected/x.mp3',
            '/joomla',
            '/var/www/html/joomla'
        );

        // The bug, stated as the thing that must not happen, so a reader of a
        // failure sees what regressed rather than only which string differed.
        $this->assertStringNotContainsString(
            '/joomla/joomla/',
            $got,
            'The base path was written twice (#2045). realpath() then fails, the file is treated '
            . 'as remote, and a protected file becomes undeliverable.'
        );
    }

    #[TestDox('A target with no path yields nothing rather than the bare web root')]
    public function testPathlessTargetYieldsEmpty(): void
    {
        // ⚠️ Returning the root would make realpath() succeed on a directory,
        // and the containment check would pass. The caller has to be able to
        // tell "no path" from "a path that resolves".
        $this->assertSame('', CwmmediaStreamer::candidatePath('https://example.com', '', '/var/www/html'));
        $this->assertSame('', CwmmediaStreamer::candidatePath('', '', '/var/www/html'));
    }

    #[TestDox('Traversal survives the join intact, for realpath to collapse and the caller to reject')]
    public function testTraversalIsLeftForRealpathToResolve(): void
    {
        // Not sanitised here on purpose. This function answers "where would it
        // be"; resolveLocalPath() collapses the result and refuses anything
        // outside the web root. Silently rewriting a hostile path would hide
        // it from the check that exists to catch it.
        $this->assertSame(
            '/var/www/html/images/../../etc/passwd',
            CwmmediaStreamer::candidatePath('https://example.com/images/../../etc/passwd', '', '/var/www/html')
        );
    }
}

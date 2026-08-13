<?php

/**
 * Unit tests for the media access chain in Cwmdownload
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmdownload;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * A media file is only as reachable as the message it belongs to, and the
 * series that message is in.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmdownloadAccessTest extends ProclaimTestCase
{
    /** An anonymous visitor: Public and Guest. */
    private const ANON = [1, 5];

    /** A logged-in member: Public and Registered. */
    private const MEMBER = [1, 2];

    /**
     * Build a media row as the download query returns it.
     *
     * @param   int|null  $file    The media file's own access level.
     * @param   int|null  $study   The message's level, or null when there is no message.
     * @param   int|null  $series  The series' level, or null when the message is in no series.
     *
     * @return object
     */
    private function media(?int $file, ?int $study = null, ?int $series = null): object
    {
        return (object) ['access' => $file, 'study_access' => $study, 'series_access' => $series];
    }

    // -----------------------------------------------------------------
    // the bug this closes
    // -----------------------------------------------------------------

    /**
     * The whole point. Before this, only the file's own level was checked, so a
     * Public file hanging off a Registered message was downloadable by anyone —
     * j6-dev carried seven such rows.
     */
    public function testAPublicFileUnderARestrictedMessageIsRefused(): void
    {
        $media = $this->media(file: 1, study: 2);

        $this->assertFalse(
            Cwmdownload::isAccessible($media, self::ANON),
            'A Public file on a Registered message must not be downloadable anonymously.'
        );
        $this->assertTrue(
            Cwmdownload::isAccessible($media, self::MEMBER),
            'A member who holds Registered may still download it.'
        );
    }

    /** The series restricts too, even when the message itself is Public. */
    public function testAPublicFileUnderARestrictedSeriesIsRefused(): void
    {
        $media = $this->media(file: 1, study: 1, series: 2);

        $this->assertFalse(Cwmdownload::isAccessible($media, self::ANON));
        $this->assertTrue(Cwmdownload::isAccessible($media, self::MEMBER));
    }

    /** Nothing restricted anywhere in the chain: still downloadable. */
    public function testAFullyPublicChainIsAllowed(): void
    {
        $this->assertTrue(
            Cwmdownload::isAccessible($this->media(file: 1, study: 1, series: 1), self::ANON)
        );
    }

    // -----------------------------------------------------------------
    // the file's own level still counts
    // -----------------------------------------------------------------

    /**
     * The per-file level is not ignored — it can tighten beyond the message.
     * Today every media file is Public, so this never fires in practice, but
     * the field keeps its meaning rather than becoming decorative.
     */
    public function testTheFilesOwnLevelCanRestrictBeyondItsMessage(): void
    {
        $this->assertFalse(
            Cwmdownload::isAccessible($this->media(file: 2, study: 1), self::ANON)
        );
    }

    // -----------------------------------------------------------------
    // why every level is required, rather than "the most restrictive"
    // -----------------------------------------------------------------

    /**
     * Joomla view levels are arbitrary sets of user groups with no ordering, so
     * there is no "strictest" of Special and Guest to reduce the chain to. A
     * user holding one but not the other must be refused, which only works if
     * every level is required independently.
     */
    public function testEveryLevelIsRequiredBecauseLevelsHaveNoOrdering(): void
    {
        // Holds Special (3) but not Guest (5).
        $holdsSpecial = [1, 3];

        $this->assertFalse(
            Cwmdownload::isAccessible($this->media(file: 5, study: 3), $holdsSpecial),
            'Holding the message level is not enough when the file requires another.'
        );
        $this->assertTrue(
            Cwmdownload::isAccessible($this->media(file: 5, study: 3), [1, 3, 5])
        );
    }

    // -----------------------------------------------------------------
    // absent links in the chain
    // -----------------------------------------------------------------

    /**
     * The joins are LEFT joins: a media file with no message, or a message in no
     * series, yields null. A missing link constrains nothing — it must not be
     * treated as level 0 and deny everyone.
     */
    public function testAbsentChainLinksDoNotRestrict(): void
    {
        $this->assertTrue(
            Cwmdownload::isAccessible($this->media(file: 1, study: null, series: null), self::ANON),
            'Media with no message must not be denied by the missing link.'
        );
        $this->assertTrue(
            Cwmdownload::isAccessible($this->media(file: 1, study: 1, series: null), self::ANON),
            'A message in no series must not be denied by the missing series.'
        );
        $this->assertFalse(
            Cwmdownload::isAccessible($this->media(file: 1, study: 2, series: null), self::ANON),
            'A missing series must not rescue a restricted message either.'
        );
    }

    /** A user with no levels at all gets nothing, not everything. */
    public function testAUserWithNoLevelsIsRefused(): void
    {
        $this->assertFalse(
            Cwmdownload::isAccessible($this->media(file: 1, study: 1), [])
        );
    }
}

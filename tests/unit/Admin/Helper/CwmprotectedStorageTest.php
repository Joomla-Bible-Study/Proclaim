<?php

/**
 * Unit tests for CwmprotectedStorage
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmprotectedStorage;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * The directory restricted media lives in, and when its verdict goes stale.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmprotectedStorageTest extends ProclaimTestCase
{
    // -----------------------------------------------------------------
    // where it lives
    // -----------------------------------------------------------------

    /**
     * Under images/biblestudy/ with the rest of the user content, so a backup
     * that collects media collects this too and a host move does not leave a
     * second location to discover.
     */
    public function testItLivesUnderTheUsualMediaTree(): void
    {
        self::assertStringStartsWith(
            'images/biblestudy/',
            CwmprotectedStorage::RELATIVE_PATH,
            'Protected media belongs with the other user content, not in its own tree.'
        );
    }

    public function testPathAndUrlDescribeTheSameLocation(): void
    {
        self::assertStringEndsWith(CwmprotectedStorage::RELATIVE_PATH, CwmprotectedStorage::path());
        self::assertStringEndsWith(CwmprotectedStorage::RELATIVE_PATH, CwmprotectedStorage::url());
    }

    /** No doubled or missing separator, whatever JPATH_ROOT ends with. */
    public function testThePathIsWellFormed(): void
    {
        self::assertStringNotContainsString('//', substr(CwmprotectedStorage::path(), 1));
    }

    // -----------------------------------------------------------------
    // when a verdict is taken again
    // -----------------------------------------------------------------

    /** Never checked is always due — absence of a verdict is not a good one. */
    public function testAVerdictThatWasNeverTakenIsDue(): void
    {
        self::assertTrue(CwmprotectedStorage::isRecheckDue(null, 1_000_000));
        self::assertTrue(CwmprotectedStorage::isRecheckDue(0, 1_000_000));
    }

    public function testAFreshVerdictIsTrusted(): void
    {
        $now = 1_000_000_000;

        self::assertFalse(
            CwmprotectedStorage::isRecheckDue($now - 60, $now),
            'A verdict taken a minute ago should not trigger another HTTP probe.'
        );
    }

    /**
     * The thing being watched for is a host migration or a switch to nginx,
     * neither of which announces itself — so a verdict has a shelf life.
     */
    public function testAStaleVerdictIsRetaken(): void
    {
        $now = 1_000_000_000;

        self::assertTrue(
            CwmprotectedStorage::isRecheckDue($now - CwmprotectedStorage::RECHECK_SECONDS - 1, $now)
        );
        self::assertTrue(
            CwmprotectedStorage::isRecheckDue($now - CwmprotectedStorage::RECHECK_SECONDS, $now),
            'Exactly at the boundary counts as due.'
        );
    }

    /**
     * A timestamp ahead of now means the clock moved or the value was edited.
     * Trusting it would park a stale verdict indefinitely.
     */
    public function testAFutureTimestampIsNotTrusted(): void
    {
        $now = 1_000_000_000;

        self::assertTrue(CwmprotectedStorage::isRecheckDue($now + 86_400, $now));
    }

    // -----------------------------------------------------------------
    // what can be moved in
    // -----------------------------------------------------------------

    /** Only a file we serve can be relocated here. */
    public function testOnlyOurOwnFilesCanBeMovedIn(): void
    {
        self::assertTrue(
            CwmprotectedStorage::canHold(\Joomla\CMS\Uri\Uri::root() . 'images/biblestudy/a.mp3')
        );
        self::assertFalse(
            CwmprotectedStorage::canHold('https://s3.example.org/bucket/a.mp3'),
            'A remote object is not ours to relocate — which is not the same as it being unprotectable.'
        );
    }
}

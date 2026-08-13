<?php

/**
 * Unit tests for CwmmediaProtectionHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmmediaProtectionHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Restricted in Proclaim, but still handed out by the web server.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmediaProtectionHelperTest extends ProclaimTestCase
{
    /** What a logged-out visitor holds. */
    private const GUEST = [1, 5];

    /**
     * @param   int|null  $file    Media file's own level.
     * @param   int|null  $study   Message's level.
     * @param   int|null  $series  Series' level.
     *
     * @return object
     */
    private function media(?int $file, ?int $study = null, ?int $series = null): object
    {
        return (object) ['access' => $file, 'study_access' => $study, 'series_access' => $series];
    }

    // -----------------------------------------------------------------
    // restricted-from-guests
    // -----------------------------------------------------------------

    public function testAFullyPublicChainIsNotRestricted(): void
    {
        self::assertFalse(
            CwmmediaProtectionHelper::isRestrictedFromGuests($this->media(1, 1, 1), self::GUEST)
        );
    }

    /** Restriction anywhere in the chain counts, which is the #1777 rule. */
    public function testRestrictionAnywhereInTheChainCounts(): void
    {
        self::assertTrue(
            CwmmediaProtectionHelper::isRestrictedFromGuests($this->media(2, 1, 1), self::GUEST),
            'the file itself'
        );
        self::assertTrue(
            CwmmediaProtectionHelper::isRestrictedFromGuests($this->media(1, 2, 1), self::GUEST),
            'its message'
        );
        self::assertTrue(
            CwmmediaProtectionHelper::isRestrictedFromGuests($this->media(1, 1, 2), self::GUEST),
            'its series'
        );
    }

    /** LEFT joins yield null for an absent link; that must not read as restricted. */
    public function testAbsentChainLinksAreNotRestrictions(): void
    {
        self::assertFalse(
            CwmmediaProtectionHelper::isRestrictedFromGuests($this->media(1, null, null), self::GUEST)
        );
    }

    /**
     * Asked as "would a guest be refused" rather than "is the level 1", so a
     * site that grants its logged-out visitors more than Public is answered
     * correctly rather than warned at spuriously.
     */
    public function testItAsksWhatGuestsHoldRatherThanAssumingLevelOne(): void
    {
        $media = $this->media(1, 3, 1);

        self::assertTrue(CwmmediaProtectionHelper::isRestrictedFromGuests($media, self::GUEST));
        self::assertFalse(
            CwmmediaProtectionHelper::isRestrictedFromGuests($media, [1, 3, 5]),
            'A site whose guests hold level 3 is not restricting anything here.'
        );
    }

    // -----------------------------------------------------------------
    // served-by-web-server
    // -----------------------------------------------------------------

    public function testAUrlUnderTheSiteRootIsServedByTheWebServer(): void
    {
        self::assertTrue(
            CwmmediaProtectionHelper::isServedByWebServer(\Joomla\CMS\Uri\Uri::root() . 'images/biblestudy/a.mp3')
        );
    }

    /** Another host's file is that host's problem, not a leak of ours. */
    public function testAUrlOnAnotherHostIsNotOurs(): void
    {
        self::assertFalse(
            CwmmediaProtectionHelper::isServedByWebServer('https://cdn.example.org/media/a.mp3')
        );
    }

    public function testAnEmptyUrlIsNotALeak(): void
    {
        self::assertFalse(CwmmediaProtectionHelper::isServedByWebServer(''));
    }

    // -----------------------------------------------------------------
    // the combination — only both halves together are a problem
    // -----------------------------------------------------------------

    public function testOnlyRestrictedAndReachableTogetherWarrantAWarning(): void
    {
        $local  = \Joomla\CMS\Uri\Uri::root() . 'images/biblestudy/a.mp3';
        $remote = 'https://cdn.example.org/media/a.mp3';

        self::assertTrue(
            CwmmediaProtectionHelper::isRestrictedButReachable($this->media(1, 2), $local, self::GUEST),
            'restricted and on our web server — the case worth warning about'
        );
        self::assertFalse(
            CwmmediaProtectionHelper::isRestrictedButReachable($this->media(1, 2), $remote, self::GUEST),
            'restricted but stored elsewhere — not ours to leak'
        );
        self::assertFalse(
            CwmmediaProtectionHelper::isRestrictedButReachable($this->media(1, 1), $local, self::GUEST),
            'public and on our web server — working as intended'
        );
    }
}

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

    // -----------------------------------------------------------------
    // the canary: does the web server actually refuse this directory?
    // -----------------------------------------------------------------

    /** A 200 handing back the canary is the whole point: the file is public. */
    public function testTheCanaryComingBackMeansExposed(): void
    {
        self::assertSame(
            CwmmediaProtectionHelper::EXPOSED,
            CwmmediaProtectionHelper::interpretProbe(200, 'tok-123', 'tok-123')
        );
    }

    /** A client error is the web server declining. 404 counts as much as 403. */
    public function testAClientErrorMeansProtected(): void
    {
        foreach ([401, 403, 404, 410] as $status) {
            self::assertSame(
                CwmmediaProtectionHelper::PROTECTED,
                CwmmediaProtectionHelper::interpretProbe($status, '', 'tok-123'),
                "HTTP {$status} is a refusal."
            );
        }
    }

    /**
     * The rule that matters most. A deny rule that silently does nothing and a
     * network failure look identical from here, so anything we cannot read as
     * a refusal must not be reported as protection.
     */
    public function testAnythingInconclusiveIsNeverReportedAsProtected(): void
    {
        $cases = [
            'no response at all'            => [null, ''],
            'a redirect'                    => [302, ''],
            'a server error'                => [500, ''],
            'a bad gateway'                 => [502, ''],
            '200 with someone else\'s body' => [200, '<html>Please log in</html>'],
        ];

        foreach ($cases as $what => [$status, $body]) {
            self::assertSame(
                CwmmediaProtectionHelper::UNVERIFIED,
                CwmmediaProtectionHelper::interpretProbe($status, $body, 'tok-123'),
                ucfirst($what) . ' must be UNVERIFIED, never PROTECTED.'
            );
        }
    }

    /**
     * A 500 is not a refusal. Treating every non-2xx as protection would call
     * a broken server a secure one, which is the failure this check exists to
     * prevent.
     */
    public function testAServerErrorIsNotMistakenForARefusal(): void
    {
        self::assertNotSame(
            CwmmediaProtectionHelper::PROTECTED,
            CwmmediaProtectionHelper::interpretProbe(500, '', 'tok-123')
        );
    }

    // -----------------------------------------------------------------
    // the deny files
    // -----------------------------------------------------------------

    /** Apache 2.2 and 2.4 both, plus IIS — the pair Proclaim already ships. */
    public function testItWritesGuardsForApacheAndIis(): void
    {
        $dir = sys_get_temp_dir() . '/cwm-deny-' . bin2hex(random_bytes(6));
        mkdir($dir, 0o777, true);

        try {
            CwmmediaProtectionHelper::writeDenyFiles($dir);

            $htaccess = (string) file_get_contents($dir . '/.htaccess');
            self::assertStringContainsString('Deny from all', $htaccess, 'Apache 2.2');
            self::assertStringContainsString('Require all denied', $htaccess, 'Apache 2.4');
            self::assertStringContainsString(
                'allowUnlisted="false"',
                (string) file_get_contents($dir . '/web.config'),
                'IIS'
            );
        } finally {
            @unlink($dir . '/.htaccess');
            @unlink($dir . '/web.config');
            @rmdir($dir);
        }
    }

    /** Never clobber a guard an administrator has customised. */
    public function testItLeavesAnExistingGuardAlone(): void
    {
        $dir = sys_get_temp_dir() . '/cwm-deny-' . bin2hex(random_bytes(6));
        mkdir($dir, 0o777, true);
        file_put_contents($dir . '/.htaccess', '# hand-written');

        try {
            CwmmediaProtectionHelper::writeDenyFiles($dir);

            self::assertSame('# hand-written', file_get_contents($dir . '/.htaccess'));
        } finally {
            @unlink($dir . '/.htaccess');
            @unlink($dir . '/web.config');
            @rmdir($dir);
        }
    }

    /** An unwritable or missing directory cannot be verified, so it is not. */
    public function testAnUnusableDirectoryIsUnverifiedRatherThanProtected(): void
    {
        self::assertSame(
            CwmmediaProtectionHelper::UNVERIFIED,
            CwmmediaProtectionHelper::probeDirectory('/no/such/directory/here', 'https://example.org/x')
        );
    }
}

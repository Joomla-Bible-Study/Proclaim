<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmmediaProtectionHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * `canResolveSiteRoot()` has to refuse a root that was invented rather than derived.
 *
 * ⚠️ This exists because the first version of that guard was wrong and the whole
 * suite passed anyway. It tested `Uri::root()` for a scheme and a host, on the
 * belief that a request-less context produces a hostless value. It does not:
 * off a request with `live_site` empty, `Uri::base()` returns
 * `http://localhost/` — well-formed, and matching nothing on a site served from
 * its real address.
 *
 * That is worse than a broken value. `isServedByWebServer()` compares media URLs
 * against the root, so every URL on a real site compares as "not ours", and
 * `RestrictedMediaCheck` reads that as nothing being exposed. A security check
 * reporting all-clear from cron is the failure this guard exists to prevent, and
 * the first version walked straight into it.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmmediaProtectionRootTest extends ProclaimTestCase
{
    /**
     * Whether this site has a Site URL configured.
     *
     * @return  bool
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function liveSiteIsSet(): bool
    {
        return trim((string) Factory::getContainer()->get('config')->get('live_site', '')) !== '';
    }

    #[TestDox('a root invented off a request is refused, however well-formed it looks')]
    public function testAnInventedRootIsRefused(): void
    {
        $this->assertStringContainsString(
            'cli',
            \PHP_SAPI,
            'These assertions describe the request-less case; the suite is expected to run under CLI.'
        );

        if (self::liveSiteIsSet()) {
            // Not a skip: with a Site URL configured the root is real, and the
            // guard must say so. That is the other half of the contract.
            $this->assertTrue(
                CwmmediaProtectionHelper::canResolveSiteRoot(),
                'live_site is configured, so the root is the site\'s own address and usable off a request.'
            );

            return;
        }

        // ⚠️ The canary. Without this the assertion below could pass because
        // Uri::root() returned something obviously broken, which would mean the
        // guard is not being tested for the case that actually bites.
        $this->assertMatchesRegularExpression(
            '#^https?://[^/]+#i',
            Uri::root(),
            'Uri::root() is expected to look well-formed here — that is the point. If it no longer '
            . 'does, this test is no longer exercising the trap and needs rewriting.'
        );

        $this->assertFalse(
            CwmmediaProtectionHelper::canResolveSiteRoot(),
            "Uri::root() is well-formed but invented: there was no request to derive it from and no\n"
            . "live_site to fall back on. Accepting it makes every media URL compare as 'not ours',\n"
            . 'and RestrictedMediaCheck then reports a clean site from a scheduled task.'
        );
    }

    #[TestDox('the guard does not decide on the shape of the root alone')]
    public function testTheGuardIsNotMerelyAWellFormednessTest(): void
    {
        $source = (string) file_get_contents(
            \dirname(__DIR__, 4) . '/admin/src/Helper/CwmmediaProtectionHelper.php'
        );

        $start = strpos($source, 'public static function canResolveSiteRoot()');

        $this->assertNotFalse($start, 'canResolveSiteRoot() could not be found.');

        $body = substr($source, $start, (int) strpos($source, "\n    }\n", $start) - $start);

        $this->assertStringContainsString(
            'live_site',
            $body,
            "canResolveSiteRoot() no longer consults live_site, so it is deciding on the shape of\n"
            . 'Uri::root() alone — which returns a well-formed but invented value off a request.'
        );
    }
}

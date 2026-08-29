<?php

/**
 * The count behind the "servers awaiting migration" notice.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmserverMigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmserverMigrationPendingTest extends ProclaimTestCase
{
    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The pending count reports both servers and the media rows on them')]
    public function testCountShape(): void
    {
        $result = CwmserverMigrationHelper::countPendingMigration();

        $this->assertArrayHasKey('servers', $result);
        $this->assertArrayHasKey('media', $result);
        $this->assertIsInt($result['servers']);
        $this->assertIsInt($result['media']);
        $this->assertGreaterThanOrEqual(0, $result['servers']);
        $this->assertGreaterThanOrEqual(0, $result['media']);
    }

    /**
     * The media count is only meaningful next to the server count. Reporting
     * rows without servers would put a notice on screen with no action behind
     * it, since there would be nothing to migrate.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('No legacy servers means no media rows are reported either')]
    public function testMediaIsZeroWhenNoLegacyServers(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $db->setQuery(
            'SELECT COUNT(*) FROM ' . $db->quoteName('#__bsms_servers')
            . ' WHERE ' . $db->quoteName('type') . ' = ' . $db->quote('legacy')
        );

        $legacy = (int) $db->loadResult();
        $result = CwmserverMigrationHelper::countPendingMigration();

        if ($legacy === 0) {
            $this->assertSame(0, $result['servers']);
            $this->assertSame(
                0,
                $result['media'],
                'With nothing to migrate the media count must be 0, not the whole media table.'
            );

            return;
        }

        // ⚠️ Not a silent skip. If the fixture has legacy servers, assert the
        // branch that actually ran rather than reporting a pass for a case
        // that was never exercised.
        $this->assertSame(
            $legacy,
            $result['servers'],
            'The reported server count must match what the database holds.'
        );
    }

    /**
     * ⚠️ This check runs from a page render, from a restore finishing, and
     * (from 10.6.0) from a scheduled task. Anything reaching for the identity,
     * the session or the request works in the browser and dies under cron --
     * the failure mode recorded for api.php. Reading the source is cruder than
     * running it headless, but it runs in CI on every change, which the
     * headless case does not.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The count asks nothing about the current user, session or request')]
    public function testCountIsContextFree(): void
    {
        $method = new \ReflectionMethod(CwmserverMigrationHelper::class, 'countPendingMigration');
        $file   = (string) file_get_contents((string) $method->getFileName());
        $lines  = explode("\n", $file);
        $body   = implode(
            "\n",
            \array_slice($lines, $method->getStartLine() - 1, $method->getEndLine() - $method->getStartLine() + 1)
        );

        $this->assertNotSame('', trim($body), 'Failed to read the method body — this test would pass on nothing.');

        foreach (['getIdentity', 'getSession', 'getInput', 'enqueueMessage', 'Route::', 'Uri::'] as $forbidden) {
            $this->assertStringNotContainsString(
                $forbidden,
                $body,
                $forbidden . ' ties this count to a web request. It also has to answer from a scheduled task.'
            );
        }
    }
}

<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Repo;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The order the uninstall destroys things in, which decides how bad a failure is.
 *
 * `dropTablesIfRequested()` does two destructive things: it drops Proclaim's
 * tables, and it deletes Proclaim's `#__assets` rows. Both can fail. Only one
 * order fails recoverably:
 *
 *   tables first  -> dropped tables, stale asset rows. Drift `Cwmassets`
 *                    already sweeps, and nothing of the site's is lost.
 *   assets first  -> deleted permissions, tables still full of content. The
 *                    records survive with nothing governing access to them.
 *
 * It ran assets-first until #1980. A `DatabaseDriver` import removed a month
 * before the call that needed it made the second half raise an `Error` — which
 * the method's own `catch (\Exception)` does not catch — so an uninstall with
 * drop_tables enabled destroyed the permissions and dropped no tables at all.
 * That went unnoticed from 2026-03-23 to 2026-08-27.
 *
 * ⚠️ Asserted at source level rather than by running it. Exercising this needs
 * a database it is allowed to destroy, and the integration suite runs against a
 * live dev database — see #1983 for the disposable-site harness. The property
 * worth defending meanwhile is that the order is written down deliberately,
 * which is the same bar `HealthContractTest` sets for its ACL guard.
 *
 * @since  __DEPLOY_VERSION__
 */
class UninstallOrderTest extends ProclaimTestCase
{
    /**
     * The body of `dropTablesIfRequested()`.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function method(): string
    {
        $source = (string) file_get_contents(\dirname(__DIR__, 3) . '/proclaim.script.php');
        $start  = strpos($source, 'private function dropTablesIfRequested(): void');

        self::assertNotFalse($start, 'dropTablesIfRequested() could not be found.');

        $end = strpos($source, "\n    }\n", $start);

        self::assertNotFalse($end, 'Could not find the end of dropTablesIfRequested().');

        return substr($source, $start, $end - $start);
    }

    #[TestDox('the uninstall drops the tables before it deletes the permission rows')]
    public function testTablesAreDroppedBeforeAssetsAreDeleted(): void
    {
        $body = self::method();

        $dropSql     = strpos($body, 'splitSql');
        $deleteAsset = strpos($body, "quote('com_proclaim%')");

        $this->assertNotFalse($dropSql, 'The uninstall SQL is no longer run here.');
        $this->assertNotFalse($deleteAsset, 'The #__assets cleanup is no longer done here.');

        $this->assertLessThan(
            $deleteAsset,
            $dropSql,
            "dropTablesIfRequested() deletes Proclaim's #__assets rows before it drops the tables.\n"
            . "A failure between the two then leaves a site whose content is intact and whose\n"
            . "permissions are gone, which is not recoverable from here. The other order leaves\n"
            . 'stale asset rows, which Cwmassets already sweeps. Drop the tables first.'
        );
    }

    /**
     * ⚠️ The early returns this replaced skipped the asset cleanup entirely
     * when the SQL file was absent. Now the tables run first, an early return
     * between the two halves would silently reintroduce that.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('nothing returns between dropping the tables and clearing the permission rows')]
    public function testNoEarlyReturnBetweenTheTwoHalves(): void
    {
        $body    = self::method();
        $between = substr(
            $body,
            (int) strpos($body, 'splitSql'),
            (int) strpos($body, "quote('com_proclaim%')") - (int) strpos($body, 'splitSql')
        );

        $this->assertStringNotContainsString(
            'return;',
            $between,
            "A return between dropping the tables and deleting the #__assets rows leaves the rows\n"
            . 'behind on a path that meant to remove them. Let the block fall through instead.'
        );
    }

    #[TestDox('the uninstall still refuses unless the administrator asked for it')]
    public function testDroppingIsGatedOnTheStoredSetting(): void
    {
        $body = self::method();

        $this->assertStringContainsString(
            "quoteName('drop_tables')",
            $body,
            'dropTablesIfRequested() no longer reads the drop_tables setting.'
        );

        $gate = strpos($body, '$dropTables < 1');

        $this->assertNotFalse($gate, 'The drop_tables gate is gone; an uninstall would always drop.');

        $this->assertLessThan(
            (int) strpos($body, 'splitSql'),
            $gate,
            'The drop_tables gate has to be read before anything is destroyed.'
        );
    }
}

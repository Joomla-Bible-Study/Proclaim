<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Install;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * 11.0 must refuse to update a site whose scripture references have not moved.
 *
 * The 9.x upgrade path went, and CwmscriptureMigration::retireLegacyColumns()
 * with it -- the routine that moved the flat columns on `#__bsms_studies` into
 * `#__bsms_study_scriptures` before dropping them. Nothing here does that any
 * more, so a site arriving with those columns populated would have the
 * references silently left behind: the data loss the retirement prevents,
 * reintroduced by deletion rather than by a bug.
 *
 * ⚠️ The guard is what makes the removal safe. Removing the removal's guard is
 * an easy tidy-up to make while reading preflight() and noticing an early
 * return -- hence these.
 *
 * Source-inspection, like CwmrestoreTest: preflight() needs a database and an
 * application. What is checkable without either is that the gate is called
 * before anything else happens on the update route, and that it asks the right
 * question.
 *
 * @since  __DEPLOY_VERSION__
 */
class MinimumUpgradeSourceTest extends ProclaimTestCase
{
    private static function script(): string
    {
        return (string) file_get_contents(\dirname(__DIR__, 4) . '/proclaim.script.php');
    }

    public function testTheMinimumIsDeclaredOnceAndIsTheReleaseThatRetiredTheColumns(): void
    {
        $this->assertMatchesRegularExpression(
            "/MINIMUM_UPGRADE_SOURCE\s*=\s*'10\.5\.8'/",
            self::script(),
            'retireLegacyColumns() shipped in 10.5.8. A lower minimum lets through a site '
            . 'that never ran it.'
        );
    }

    public function testPreflightConsultsTheGateOnTheUpdateRoute(): void
    {
        $script = self::script();

        $this->assertMatchesRegularExpression(
            '/if \(!\$this->upgradeSourceIsSupported\(\)\) \{\s*return false;/',
            $script,
            'The gate must abort preflight, not merely warn.'
        );

        // Ordering matters: the version has to be read before the gate can
        // report it, and both have to happen before any install work.
        // The call site, not the declaration -- the method is defined above
        // preflight(), so searching for the bare name compares the wrong things.
        $readAt = strpos($script, '$this->fromVersion = $this->readInstalledVersion();');
        $gateAt = strpos($script, 'if (!$this->upgradeSourceIsSupported())');

        $this->assertNotFalse($readAt);
        $this->assertNotFalse($gateAt);
        $this->assertLessThan(
            $gateAt,
            $readAt,
            'The installed version is read before the gate runs.'
        );
    }

    /**
     * The authoritative check is the column, not the version.
     *
     * A version is a proxy: manifest_cache can be unreadable, and a -dev build
     * of the minimum compares lower than the release while carrying the fix.
     * The column is the fact.
     */
    public function testTheGateAsksTheDatabaseNotJustTheVersion(): void
    {
        $script = self::script();

        $this->assertMatchesRegularExpression(
            "/columnExists\(\s*'#__bsms_studies',\s*'booknumber'\s*\)/",
            $script,
            'The precondition is that the flat columns are gone. Ask that.'
        );

        $gate = substr(
            $script,
            (int) strpos($script, 'private function upgradeSourceIsSupported')
        );
        $gate = substr($gate, 0, (int) strpos($gate, "\n    }"));

        $columnAt  = strpos($gate, 'columnExists');
        $messageAt = strpos($gate, 'abortUnsupportedSource');

        $this->assertNotFalse($columnAt);
        $this->assertNotFalse($messageAt);
        $this->assertLessThan(
            $messageAt,
            $columnAt,
            'A site whose columns are already gone must pass, whatever its version says.'
        );
    }

    public function testARefusalIsRecordedForHeadlessUpdatesToo(): void
    {
        $script = self::script();

        $this->assertMatchesRegularExpression(
            '/private function abortUnsupportedSource/',
            $script
        );
        $this->assertMatchesRegularExpression(
            '/logInstall\(\s*\'preflight: REFUSED/',
            $script,
            'A CLI or cron update has no screen for enqueueMessage(), so the log line '
            . 'is the only record of why it stopped.'
        );
    }
}

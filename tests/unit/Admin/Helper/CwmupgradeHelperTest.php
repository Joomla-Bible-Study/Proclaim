<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmupgradeHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Regression tests for #1542, found during a Helper-folder-wide bug-hunting
 * review (sequel to #1521-#1528).
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmupgradeHelperTest extends ProclaimTestCase
{
    /**
     * #__bsms_timeset is part of the current 10.x schema (seeded by install
     * SQL, PK-repaired on every extension update by proclaim.script.php) --
     * not a 9.x-only artifact. Dropping it here is real, permanent data loss
     * on every completed 9.x->10.x upgrade. Checked against the source
     * rather than run live: DROP TABLE is DDL and causes an implicit commit
     * in MySQL, so this must never be exercised against a table any other
     * test or fixture might rely on existing.
     */
    public function testCleanup9xArtifactsLegacyTableListExcludesBsmsTimeset(): void
    {
        $reflection = new \ReflectionMethod(CwmupgradeHelper::class, 'cleanup9xArtifacts');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertStringNotContainsString(
            "'#__bsms_timeset'",
            $body,
            '#__bsms_timeset is a live 10.x table, not a 9.x artifact -- must not be in the drop list, see #1542'
        );
    }

    /**
     * Confirms the fix didn't also remove the genuinely-legacy tables --
     * cleanup9xArtifacts() must still drop real 9.x-only artifacts when they
     * exist. Uses throwaway tables under the real prefix so getTableList()
     * sees them, and cleans up in finally regardless of outcome since DROP
     * TABLE is DDL and cannot be rolled back.
     */
    public function testCleanup9xArtifactsStillDropsGenuineLegacyTables(): void
    {
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $prefix = $db->getPrefix();

        $versionTable       = $prefix . 'bsms_version';
        $schemaversionTable = $prefix . 'bsms_schemaversion';

        try {
            $db->setQuery('CREATE TABLE ' . $db->quoteName($versionTable) . ' (id INT)');
            $db->execute();
            $db->setQuery('CREATE TABLE ' . $db->quoteName($schemaversionTable) . ' (id INT)');
            $db->execute();

            $dropped = CwmupgradeHelper::cleanup9xArtifacts();

            $this->assertSame(2, $dropped, 'both genuinely-legacy fixture tables should have been dropped');

            $tableList = $db->getTableList();
            $this->assertNotContains($versionTable, $tableList);
            $this->assertNotContains($schemaversionTable, $tableList);
        } finally {
            $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName($versionTable));
            $db->execute();
            $db->setQuery('DROP TABLE IF EXISTS ' . $db->quoteName($schemaversionTable));
            $db->execute();
        }
    }

    /**
     * upgradeVerifyXHR() must not call cleanup9xArtifacts() unconditionally
     * -- on a partially-failed upgrade (verify()['success'] === false) that
     * would destroy the only tables detect9xSchema() checks for, permanently
     * hiding the wizard's re-entry point. Checked structurally against the
     * controller source since the method emits raw JSON + header()/close()
     * calls that aren't practical to exercise in a unit test.
     */
    public function testUpgradeVerifyXhrGatesCleanupOnVerifySuccess(): void
    {
        $reflection = new \ReflectionMethod(
            \CWM\Component\Proclaim\Administrator\Controller\CwmadminController::class,
            'upgradeVerifyXHR'
        );
        $lines = file($reflection->getFileName());
        $body  = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertDoesNotMatchRegularExpression(
            '/\$dropped\s*=\s*CwmupgradeHelper::cleanup9xArtifacts\(\);/',
            $body,
            'cleanup9xArtifacts() must not run unconditionally -- see #1542'
        );
        $this->assertMatchesRegularExpression(
            "/\\\$verifyResult\\['success'\\]\\s*\\?\\s*CwmupgradeHelper::cleanup9xArtifacts\\(\\)/",
            $body,
            'cleanup9xArtifacts() must be gated on verify()[\'success\'] -- see #1542'
        );
    }

    /**
     * runDataFixes()'s guided-tours step was the only one that reported
     * success:true for any caught exception, silently hiding a genuine bug
     * in CwmguidedtourHelper::registerGuidedTours() behind a "skipped"
     * label. Checked structurally: triggering a real exception from that
     * call deterministically isn't practical in a unit test.
     */
    public function testRunDataFixesReportsGuidedTourFailureInsteadOfHidingIt(): void
    {
        $reflection = new \ReflectionMethod(CwmupgradeHelper::class, 'runDataFixes');
        $lines      = file($reflection->getFileName());
        $body       = implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );

        $this->assertMatchesRegularExpression(
            '/registerGuidedTours.*?\$errors\[\]\s*=\s*\'registerGuidedTours:/s',
            $body,
            'a thrown exception from registerGuidedTours() must be recorded in $errors, not silently relabeled success -- see #1542'
        );
        $this->assertDoesNotMatchRegularExpression(
            "/'name' => 'registerGuidedTours', 'success' => true, 'detail' => 'skipped'/",
            $body,
            'must not report success:true for a caught exception -- see #1542'
        );
    }
}

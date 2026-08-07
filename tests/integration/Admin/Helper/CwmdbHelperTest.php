<?php

/**
 * Integration tests for CwmdbHelper's execute() contract and table allow-list.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1566.
 *
 * 1. performDB() and resetdb() both gated error handling on
 *    `if (!$db->execute())`. DatabaseDriver::execute() returns true or throws
 *    -- it never returns false -- so those branches were unreachable and every
 *    real SQL failure escaped as an uncaught exception instead of the false
 *    both methods' contracts promise. Callers rely on that false:
 *    CwmadminController::copyTables() to abort a table copy cleanly, and
 *    CwminstallModel::realRun() to run resetStack() during migration-rollback
 *    recovery (neither wraps the call in try/catch).
 *
 * 2. getObjects() used an unanchored str_contains() prefix test. On shared
 *    -database hosting getTableList() returns other installs' tables too, and
 *    a sibling table merely containing the prefix produced a mangled '#__'
 *    name that was handed to every consumer -- including the export allow-list
 *    Cwmbackup uses to keep the AJAX endpoint away from tables like #__users.
 *
 * Exercised against the real driver because the whole point is what the real
 * execute() does on a real failure; a stubbed driver would return whatever the
 * stub was told to.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmdbHelper::class)]
class CwmdbHelperTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- performDB() honours its documented bool contract
    // -------------------------------------------------------------------------

    #[TestDox('performDB() returns false instead of throwing when the query fails')]
    public function testPerformDbReturnsFalseOnAFailingQuery(): void
    {
        // Pre-fix this raised ExecutionFailureException straight out of the
        // helper; copyTables() has no try/catch, so a failing CREATE TABLE
        // aborted the loop mid-run instead of reporting a clean failure.
        $result = CwmdbHelper::performDB(
            'SELECT * FROM ' . $this->db->quoteName('#__cwm_table_that_does_not_exist_1566'),
            'test: '
        );

        $this->assertFalse($result, 'A failing query must return false, not throw — see #1566');
    }

    #[TestDox('performDB() still returns true for a query that succeeds')]
    public function testPerformDbReturnsTrueOnSuccess(): void
    {
        $result = CwmdbHelper::performDB(
            'SELECT COUNT(*) FROM ' . $this->db->quoteName('#__bsms_studies')
        );

        $this->assertTrue($result, 'A valid query must still report success');
    }

    #[TestDox('performDB() rejects an empty query without touching the database')]
    public function testPerformDbRejectsEmptyQuery(): void
    {
        $this->assertFalse(CwmdbHelper::performDB(''));
    }

    #[TestDox('A failing query is reported, not silently swallowed')]
    public function testPerformDbSurfacesTheFailureToTheOperator(): void
    {
        $app = Factory::getApplication();

        // Drain anything already queued so the assertion sees only our message.
        $app->getMessageQueue(true);

        CwmdbHelper::performDB(
            'SELECT * FROM ' . $this->db->quoteName('#__cwm_table_that_does_not_exist_1566'),
            'test: '
        );

        $messages = $app->getMessageQueue(true);

        $this->assertNotEmpty(
            $messages,
            'Returning false must not mean failing silently — the operator needs to know'
        );
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- getObjects() anchors its prefix match
    // -------------------------------------------------------------------------

    #[TestDox('getObjects() returns this component\'s tables, all well-formed')]
    public function testGetObjectsReturnsWellFormedProclaimTables(): void
    {
        $names = array_column(CwmdbHelper::getObjects(), 'name');

        $this->assertNotEmpty($names, 'The component\'s own tables must still be discovered');

        foreach ($names as $name) {
            $this->assertStringStartsWith(
                '#__bsms_',
                $name,
                'A mangled name here reaches the backup/restore loops and the export allow-list — see #1566'
            );
        }
    }

    #[TestDox('A sibling install\'s table that merely contains the prefix is not matched')]
    public function testUnanchoredPrefixMatchIsRejected(): void
    {
        $prefix = $this->db->getPrefix();

        // The shape that broke it: a table from another install sharing the
        // schema, whose name contains this site's prefix but does not start
        // with it. Asserted against the same predicate getObjects() now uses,
        // since creating a real sibling table is not worth the fixture.
        $sibling = 'my' . $prefix . 'bsms_studies';

        $this->assertTrue(
            str_contains($sibling, $prefix),
            'Precondition: the old unanchored test would have matched this'
        );
        $this->assertFalse(
            str_starts_with($sibling, $prefix . 'bsms_'),
            'The anchored test must reject a table that only contains the prefix'
        );

        // And confirm no such mangled entry is present in the live result.
        $names = array_column(CwmdbHelper::getObjects(), 'name');

        foreach ($names as $name) {
            $this->assertDoesNotMatchRegularExpression(
                '/^#__[a-z]*_bsms_/',
                $name,
                'A double-prefixed name is the signature of the unanchored match — see #1566'
            );
        }
    }

    #[TestDox('The legacy #__bsms_update table stays excluded')]
    public function testLegacyUpdateTableRemainsExcluded(): void
    {
        $names = array_column(CwmdbHelper::getObjects(), 'name');

        $this->assertNotContains(
            '#__bsms_update',
            $names,
            'The legacy version table is superseded by #__schemas and must stay filtered out'
        );
    }
}

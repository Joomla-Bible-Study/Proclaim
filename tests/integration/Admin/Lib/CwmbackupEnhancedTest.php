<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;

/**
 * Test class for enhanced Cwmbackup functionality.
 *
 * Seeds the rows each export needs inside a rolled-back transaction so the
 * REAL export branch is exercised in every environment. The prior version
 * asserted disjunctively ("UPDATE or no-config comment", "DELETE or no-tasks
 * comment or table-not-found") — on CI, which has no com_proclaim extension
 * row and (before the ci.yml fixture) no scheduler table at all, only the
 * fallback branches ever ran, so a regression breaking the actual export SQL
 * on configured sites passed everywhere.
 *
 * @package  Proclaim.Tests
 * @since    10.1.0
 */
class CwmbackupEnhancedTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        // The bootstrap in tests/unit/bootstrap.php defines PROCLAIM_TEST_DB_AVAILABLE
        // after a successful DatabaseDriver::getInstance() + connect(). When it's
        // false the component's getComponentConfigExport() / getScheduledTasksExport()
        // / getExportTableData() methods cannot run because they query real tables.
        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available — configure build.properties with a Joomla path.');
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
                // Connection may have been lost — nothing to roll back.
            }
        }

        parent::tearDown();
    }

    /**
     * Ensure a com_proclaim #__extensions row with non-empty params exists.
     *
     * Find-or-insert (same pattern as CwmlocationHelperFailOpenTest): the dev
     * DB has a real row from a full install, CI's base.sql-only fixture has
     * the table but no row. Runs inside the test transaction, so an inserted
     * or updated row rolls back.
     *
     * @return  void
     */
    private function ensureComponentConfigRow(): void
    {
        $row = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName(['extension_id', 'params']))
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_proclaim'))
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
        )->loadObject();

        if ($row === null) {
            $extRow = (object) [
                'name'           => 'com_proclaim',
                'type'           => 'component',
                'element'        => 'com_proclaim',
                'folder'         => '',
                'client_id'      => 1,
                'enabled'        => 1,
                'manifest_cache' => '',
                'params'         => '{"backup_fixture":"1"}',
                'custom_data'    => '',
            ];
            $this->db->insertObject('#__extensions', $extRow);

            return;
        }

        if (empty($row->params)) {
            $update               = new \stdClass();
            $update->extension_id = (int) $row->extension_id;
            $update->params       = '{"backup_fixture":"1"}';
            $this->db->updateObject('#__extensions', $update, 'extension_id');
        }
    }

    /**
     * Insert a proclaim.% scheduler task row for the export to find.
     *
     * @return  void
     */
    private function insertProclaimTask(): void
    {
        $row = (object) [
            'title'   => 'Backup Fixture Task',
            'type'    => 'proclaim.backupfixture',
            'state'   => 0,
            'params'  => '{}',
            'created' => '2026-01-15 10:00:00',
        ];

        $this->db->insertObject('#__scheduler_tasks', $row);
    }

    /**
     * Test component config export returns the real UPDATE statement.
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function testGetComponentConfigExportReturnsValidSQL(): void
    {
        $this->ensureComponentConfigRow();

        $backup = new Cwmbackup();
        $result = $backup->getComponentConfigExport();

        $this->assertIsString($result);
        $this->assertStringContainsString('Component Configuration', $result);

        // With a config row guaranteed present, the export must take the
        // real branch — an UPDATE that restores the params on import.
        $this->assertStringContainsString('UPDATE', $result, 'A present config row must produce an UPDATE statement');
        $this->assertStringContainsString(
            "'com_proclaim'",
            $result,
            'The UPDATE must target the com_proclaim extension row'
        );
        $this->assertStringNotContainsString(
            'No component configuration found',
            $result,
            'The no-config fallback must not fire when a config row exists'
        );
    }

    /**
     * Test scheduled tasks export returns the real DELETE + INSERT statements.
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function testGetScheduledTasksExportReturnsValidSQL(): void
    {
        // CI creates #__scheduler_tasks explicitly (ci.yml fixture step); a
        // missing table here is an environment bug, not an acceptable branch.
        $this->assertContains(
            $this->db->getPrefix() . 'scheduler_tasks',
            $this->db->getTableList(),
            '#__scheduler_tasks must exist in the test DB — check the CI schema fixture step'
        );

        $this->insertProclaimTask();

        $backup = new Cwmbackup();
        $result = $backup->getScheduledTasksExport();

        $this->assertIsString($result);
        $this->assertStringContainsString('Scheduled Tasks', $result);

        // With a proclaim.% task guaranteed present, the export must take the
        // real branch: clean-slate DELETE plus an INSERT per task.
        $this->assertStringContainsString('DELETE FROM', $result, 'A present task must produce the clean-slate DELETE');
        $this->assertStringContainsString('INSERT INTO', $result, 'A present task must produce an INSERT');
        $this->assertStringContainsString(
            'proclaim.backupfixture',
            $result,
            "The seeded task's type must appear in the INSERT"
        );
        $this->assertStringNotContainsString(
            'No scheduled tasks found',
            $result,
            'The no-tasks fallback must not fire when a proclaim task exists'
        );
    }

    /**
     * Test getExportTableData handles virtual tables
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function testGetExportTableDataHandlesVirtualTables(): void
    {
        $backup = new Cwmbackup();

        $configResult = $backup->getExportTableData('_component_config');
        $this->assertIsString($configResult);
        $this->assertStringContainsString('Component Configuration', $configResult);

        $tasksResult = $backup->getExportTableData('_scheduled_tasks');
        $this->assertIsString($tasksResult);
        $this->assertStringContainsString('Scheduled Tasks', $tasksResult);
    }
}

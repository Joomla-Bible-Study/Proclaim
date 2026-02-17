<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Unit\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for enhanced Cwmbackup functionality
 *
 * @package  Proclaim.Tests
 * @since    10.1.0
 */
class CwmbackupEnhancedTest extends ProclaimTestCase
{
    /**
     * Test component config export returns valid SQL
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function testGetComponentConfigExportReturnsValidSQL(): void
    {
        // Skip if database container not available (unit test environment)
        try {
            $backup = new Cwmbackup();
            $result = $backup->getComponentConfigExport();

            $this->assertIsString($result);
            $this->assertStringContainsString('Component Configuration', $result);

            // Should contain UPDATE statement or "No component configuration found"
            $this->assertTrue(
                str_contains($result, 'UPDATE') || str_contains($result, 'No component configuration found'),
                'Result should contain UPDATE statement or no-config comment'
            );
        } catch (\Error $e) {
            if (str_contains($e->getMessage(), 'Call to a member function get() on null')) {
                $this->markTestSkipped('Database container not available in test environment');
            }

            throw $e;
        }
    }

    /**
     * Test scheduled tasks export returns valid SQL
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function testGetScheduledTasksExportReturnsValidSQL(): void
    {
        // Skip if database container not available (unit test environment)
        try {
            $backup = new Cwmbackup();
            $result = $backup->getScheduledTasksExport();

            $this->assertIsString($result);
            $this->assertStringContainsString('Scheduled Tasks', $result);

            // Should contain DELETE statement or "No scheduled tasks found" or "table not found"
            $this->assertTrue(
                str_contains($result, 'DELETE') ||
                str_contains($result, 'No scheduled tasks found') ||
                str_contains($result, 'table not found'),
                'Result should contain DELETE statement or appropriate comment'
            );
        } catch (\Error $e) {
            if (str_contains($e->getMessage(), 'Call to a member function get() on null')) {
                $this->markTestSkipped('Database container not available in test environment');
            }

            throw $e;
        }
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
        // Skip if database container not available (unit test environment)
        try {
            $backup = new Cwmbackup();

            // Test component config virtual table
            $configResult = $backup->getExportTableData('_component_config');
            $this->assertIsString($configResult);
            $this->assertStringContainsString('Component Configuration', $configResult);

            // Test scheduled tasks virtual table
            $tasksResult = $backup->getExportTableData('_scheduled_tasks');
            $this->assertIsString($tasksResult);
            $this->assertStringContainsString('Scheduled Tasks', $tasksResult);
        } catch (\Error $e) {
            if (str_contains($e->getMessage(), 'Call to a member function get() on null')) {
                $this->markTestSkipped('Database container not available in test environment');
            }

            throw $e;
        }
    }
}

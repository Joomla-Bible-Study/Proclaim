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

/**
 * Test class for enhanced Cwmbackup functionality
 *
 * @package  Proclaim.Tests
 * @since    10.1.0
 */
class CwmbackupEnhancedTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Factory::getContainer() === null) {
            $this->markTestSkipped('Database not available — configure build.properties with a Joomla path.');
        }
    }

    /**
     * Test component config export returns valid SQL
     *
     * @return void
     *
     * @since 10.1.0
     */
    public function testGetComponentConfigExportReturnsValidSQL(): void
    {
        $backup = new Cwmbackup();
        $result = $backup->getComponentConfigExport();

        $this->assertIsString($result);
        $this->assertStringContainsString('Component Configuration', $result);

        $this->assertTrue(
            str_contains($result, 'UPDATE') || str_contains($result, 'No component configuration found'),
            'Result should contain UPDATE statement or no-config comment'
        );
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
        $backup = new Cwmbackup();
        $result = $backup->getScheduledTasksExport();

        $this->assertIsString($result);
        $this->assertStringContainsString('Scheduled Tasks', $result);

        $this->assertTrue(
            str_contains($result, 'DELETE') ||
            str_contains($result, 'No scheduled tasks found') ||
            str_contains($result, 'table not found'),
            'Result should contain DELETE statement or appropriate comment'
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

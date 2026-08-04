<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression tests for #1521.
 *
 * getExportTableData()/getTableRowCount()/getExportTableStructure()/getExportTableRows()
 * took `$table` straight from AJAX request input (CwmbackupController::exportTableXHR())
 * and interpolated it into SHOW CREATE TABLE / SELECT * queries with no allow-list check.
 * A request for e.g. `#__users` would export Joomla's user table -- including password
 * hashes -- through an endpoint meant only for this component's own `#__bsms_*` tables.
 *
 * These tests exercise the real (read-only) methods against the real dev database rather
 * than a mock, since the fix depends on CwmdbHelper::getObjects()'s actual table list.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmbackupTest extends ProclaimTestCase
{
    public function testGetTableRowCountRejectsANonProclaimTable(): void
    {
        $this->assertSame(0, (new Cwmbackup())->getTableRowCount('#__users'));
    }

    public function testGetExportTableStructureRejectsANonProclaimTable(): void
    {
        $this->assertSame('', (new Cwmbackup())->getExportTableStructure('#__users'));
    }

    public function testGetExportTableRowsRejectsANonProclaimTable(): void
    {
        $this->assertSame('', (new Cwmbackup())->getExportTableRows('#__users', 0, 10));
    }

    public function testGetExportTableDataRejectsANonProclaimTable(): void
    {
        $this->assertSame('', (new Cwmbackup())->getExportTableData('#__users'));
    }

    public function testGetTableRowCountAcceptsARealProclaimTable(): void
    {
        $tables = array_column(CwmdbHelper::getObjects(), 'name');
        $this->assertNotEmpty($tables, 'test precondition: at least one Proclaim table must exist');

        // A real table must not be silently rejected -- assert the call actually
        // reaches the DB (a rejected table always returns exactly 0 via the guard;
        // this only proves non-rejection when the count comes back > 0, so use a
        // table this dev DB is known to have rows in).
        $rowCount = (new Cwmbackup())->getTableRowCount('#__bsms_studies');

        $this->assertGreaterThan(0, $rowCount, 'expected #__bsms_studies to have rows on j5-dev');
    }

    public function testGetExportTableDataStillHandlesVirtualTables(): void
    {
        // Virtual "tables" (_component_config, etc.) are handled before the
        // allow-list check and must keep working -- they are not real table names.
        $export = (new Cwmbackup())->getExportTableData('_component_config');

        $this->assertStringContainsString('Component Configuration', $export);
    }
}

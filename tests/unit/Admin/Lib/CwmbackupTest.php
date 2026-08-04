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

    /**
     * Regression tests for #1524.
     *
     * exportdb()'s per-table loop called getExportTable(), which ran an
     * unbounded `SELECT *` (loadObjectList() with no LIMIT) and buffered the
     * generated SQL for the entire database into $this->data_cache before a
     * single write -- doubling peak memory (row objects + generated SQL
     * text) across the whole dump at once, and risking memory/timeout
     * exhaustion on large tables. exportdb() now streams each table through
     * the already-existing chunked pair (getExportTableStructure() +
     * repeated getExportTableRows() calls) and flushes to disk per chunk.
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(Cwmbackup::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    public function testGetExportTableAndDataCacheAreRemoved(): void
    {
        $this->assertFalse(
            method_exists(Cwmbackup::class, 'getExportTable'),
            'the unbounded getExportTable() must be removed once exportdb() no longer calls it -- see #1524'
        );

        $reflection = new \ReflectionClass(Cwmbackup::class);
        $this->assertFalse(
            $reflection->hasProperty('data_cache'),
            'the whole-database SQL buffer must be removed now that exportdb() streams per chunk -- see #1524'
        );
    }

    public function testExportdbUsesTheChunkedReadPathInsteadOfAnUnboundedLoad(): void
    {
        $body = self::methodBody('exportdb');

        $this->assertMatchesRegularExpression('/getExportTableStructure\(/', $body);
        $this->assertMatchesRegularExpression('/getExportTableRows\(/', $body);
        $this->assertDoesNotMatchRegularExpression(
            '/getExportTable\(/',
            $body,
            'exportdb() must not call the removed unbounded getExportTable() -- see #1524'
        );
        $this->assertMatchesRegularExpression(
            '/for\s*\(.*EXPORT_CHUNK_SIZE/s',
            $body,
            'exportdb() must page through each table in bounded chunks -- see #1524'
        );
    }

    public function testGetExportTableDataDelegatesToTheChunkedPairInsteadOfDuplicatingDdlLogic(): void
    {
        $body = self::methodBody('getExportTableData');

        $this->assertDoesNotMatchRegularExpression(
            '/SHOW CREATE TABLE/',
            $body,
            'getExportTableData() must not duplicate the DDL-fetch logic -- delegate to getExportTableStructure() -- see #1524'
        );
        $this->assertMatchesRegularExpression('/getExportTableStructure\(/', $body);
        $this->assertMatchesRegularExpression('/getExportTableRows\(/', $body);
    }

    /**
     * Live, read-only proof that a bounded getExportTableRows() call really
     * is bounded -- exercised against a real table with more rows than the
     * requested limit.
     */
    public function testGetExportTableRowsReturnsExactlyTheRequestedLimit(): void
    {
        $backup   = new Cwmbackup();
        $rowCount = $backup->getTableRowCount('#__bsms_studies');

        $this->assertGreaterThan(2, $rowCount, 'test precondition: #__bsms_studies must have more than 2 rows on j5-dev');

        $chunk = $backup->getExportTableRows('#__bsms_studies', 0, 2);

        $this->assertSame(2, preg_match_all('/^INSERT INTO/m', $chunk));
    }

    /**
     * Live, read-only proof that paging through a table in chunks visits
     * every row exactly once -- no rows lost or duplicated by chunking.
     */
    public function testChunkedRowsCoverTheSameTotalAsAnUnboundedRead(): void
    {
        $backup    = new Cwmbackup();
        $table     = '#__bsms_message_type';
        $rowCount  = $backup->getTableRowCount($table);

        $this->assertGreaterThan(0, $rowCount, 'test precondition: #__bsms_message_type must have rows on j5-dev');

        $chunkedInserts = 0;

        for ($offset = 0; $offset < $rowCount; $offset += 2) {
            $chunkedInserts += preg_match_all('/^INSERT INTO/m', $backup->getExportTableRows($table, $offset, 2));
        }

        $unboundedInserts = preg_match_all('/^INSERT INTO/m', $backup->getExportTableRows($table, 0, 0));

        $this->assertSame($rowCount, $chunkedInserts);
        $this->assertSame($rowCount, $unboundedInserts);
    }

    public function testFileSizeHeaderReadsRangeViaJoomlaInputNotSuperglobal(): void
    {
        $body = self::methodBody('fileSizeHeader');

        $this->assertDoesNotMatchRegularExpression(
            '/\$_SERVER\[.HTTP_RANGE.\]/',
            $body,
            'fileSizeHeader() must read HTTP_RANGE via getInput()->server, not the raw superglobal -- see #1524'
        );
        $this->assertDoesNotMatchRegularExpression(
            "/exec\('ls -al/",
            $body,
            'the 32-bit filesize() overflow workaround is obsolete under PHP 8.3+ (64-bit) -- see #1524'
        );
    }
}

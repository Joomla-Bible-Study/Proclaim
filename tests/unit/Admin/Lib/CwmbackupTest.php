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

        if ($rowCount <= 2) {
            $this->markTestSkipped('#__bsms_studies needs more than 2 rows to exercise a bounded chunk; only ' . $rowCount . ' present on this DB.');
        }

        $chunk = $backup->getExportTableRows('#__bsms_studies', 0, 2);

        $this->assertSame(2, preg_match_all('/^INSERT INTO/m', $chunk));
    }

    /**
     * Live, read-only proof that paging through a table in chunks visits
     * every row exactly once -- no rows lost or duplicated by chunking.
     *
     * A bounded LIMIT/OFFSET with no ORDER BY has no guaranteed row order
     * across separate queries; counting INSERT statements alone can't catch
     * a chunk boundary silently dropping one row and duplicating another.
     * This compares the actual set of primary-key ids extracted from each
     * chunk against the set from an unbounded read, on a table large enough
     * to span multiple chunks at the real chunk size used by exportdb() --
     * skipped on a DB too small to prove anything (e.g. a fresh CI fixture
     * seeded from install SQL only, with no update-migration sample data).
     */
    public function testChunkedRowsCoverTheExactSameRowsAsAnUnboundedRead(): void
    {
        $backup    = new Cwmbackup();
        $table     = '#__bsms_studies';
        $chunkSize = 500;
        $rowCount  = $backup->getTableRowCount($table);

        if ($rowCount <= $chunkSize) {
            $this->markTestSkipped('#__bsms_studies needs more than ' . $chunkSize . ' rows to span multiple chunks; only ' . $rowCount . ' present on this DB.');
        }

        $chunkedIds = [];

        for ($offset = 0; $offset < $rowCount; $offset += $chunkSize) {
            preg_match_all('/`id`=\'(\d+)\'/', $backup->getExportTableRows($table, $offset, $chunkSize), $matches);
            $chunkedIds = array_merge($chunkedIds, $matches[1]);
        }

        preg_match_all('/`id`=\'(\d+)\'/', $backup->getExportTableRows($table, 0, 0), $matches);
        $unboundedIds = $matches[1];

        $this->assertCount($rowCount, $chunkedIds, 'chunked read must return exactly one row per id -- see #1524');
        $this->assertCount($rowCount, array_unique($chunkedIds), 'chunked read must not duplicate any id across chunk boundaries -- see #1524');
        sort($chunkedIds);
        sort($unboundedIds);
        $this->assertSame($unboundedIds, $chunkedIds, 'chunked read must cover exactly the same ids as an unbounded read -- see #1524');
    }

    /**
     * Regression test for the primary-key-order fix: not every Proclaim
     * table has an `id` column (#__bsms_storage's PK is `key`,
     * #__bsms_timeset's is `timeset`, #__bsms_podcast_download_log has a
     * composite PK). getExportTableRows() must build its ORDER BY from the
     * table's actual primary key rather than assuming `id`, or a bounded
     * export of these tables would throw "Unknown column 'id'".
     *
     * #__bsms_storage is created by a later update-SQL migration rather
     * than the base install, so it may not exist on a DB seeded from
     * install SQL alone (e.g. a fresh CI fixture) -- each table is checked
     * independently rather than asserted as a fixed set.
     */
    public function testGetExportTableRowsHandlesTablesWithoutAnIdColumn(): void
    {
        $backup       = new Cwmbackup();
        $knownTables  = array_column(CwmdbHelper::getObjects(), 'name');
        $exercisedAny = false;

        foreach (['#__bsms_storage', '#__bsms_timeset', '#__bsms_podcast_download_log'] as $table) {
            if (!\in_array($table, $knownTables, true)) {
                continue;
            }

            $rowCount = $backup->getTableRowCount($table);

            if ($rowCount === 0) {
                continue;
            }

            $exercisedAny = true;
            $export       = $backup->getExportTableRows($table, 0, 10);

            $this->assertSame($rowCount, preg_match_all('/^INSERT INTO/m', $export), "expected exactly $rowCount row(s) from $table");
        }

        if (!$exercisedAny) {
            $this->markTestSkipped('none of the no-id-column tables exist with rows on this DB.');
        }
    }

    /**
     * Direct test of the ORDER BY clause builder itself.
     *
     * getExportTableRows()'s row order happening to be stable on this dev
     * DB (single writer, small tables) doesn't prove the ORDER BY clause is
     * actually being emitted -- MySQL can return InnoDB clustered-index
     * order without one under exactly these conditions, which is the whole
     * reason the fix is needed for cases where that assumption doesn't
     * hold. This exercises the clause builder directly against real
     * `SHOW KEYS` output, including a composite primary key.
     */
    public function testGetPrimaryKeyOrderClauseBuildsFromTheRealPrimaryKey(): void
    {
        $db         = \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
        $reflection = new \ReflectionMethod(Cwmbackup::class, 'getPrimaryKeyOrderClause');

        $this->assertSame('`id` ASC', $reflection->invoke(null, $db, '#__bsms_studies'));
        $this->assertSame(
            '`media_id` ASC, `client_hash` ASC',
            $reflection->invoke(null, $db, '#__bsms_podcast_download_log'),
            'composite primary key columns must be ordered by their actual index sequence'
        );

        // #__bsms_storage is created by a later update-SQL migration, not
        // the base install -- may not exist on a DB seeded from install SQL
        // alone (e.g. a fresh CI fixture).
        $knownTables = array_column(CwmdbHelper::getObjects(), 'name');

        if (\in_array('#__bsms_storage', $knownTables, true)) {
            $this->assertSame('`key` ASC', $reflection->invoke(null, $db, '#__bsms_storage'));
        }
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

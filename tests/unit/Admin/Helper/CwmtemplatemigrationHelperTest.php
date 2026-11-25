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

use CWM\Component\Proclaim\Administrator\Helper\CwmtemplatemigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

// ============================================================================
// Test infrastructure — local to this file
// ============================================================================

/**
 * Fluent query builder stub.
 *
 * Accepts any method call and returns $this so fluent builder chains inside
 * CwmtemplatemigrationHelper work without a real database.
 *
 * @since  10.1.0
 */
class TemplateMigFakeQuery
{
    /**
     * Accept any builder call and return the stub, so a chain keeps working.
     *
     * @param   string  $name  The method called
     * @param   array   $args  Its arguments
     *
     * @return  static  This stub
     *
     * @since   10.1.0
     */
    public function __call(string $name, array $args): static
    {
        return $this;
    }

    /**
     * A placeholder SQL string.
     *
     * @return  string  FAKE SQL
     *
     * @since   10.1.0
     */
    public function __toString(): string
    {
        return 'FAKE SQL';
    }
}

/**
 * Mutable template store shared between the fake DB and the testable helper.
 *
 * Passing this object (not an array) to both ensures that writes from
 * updateTemplateParams() are immediately visible to the next loadObjectList()
 * call — which is how the real DB behaves across migration stages.
 *
 * @since  10.1.0
 */
class TemplateMigStore
{
    /**
     * The template rows.
     *
     * @var    object[]
     * @since  10.1.0
     */
    public array $rows = [];

    /**
     * Hold the given template rows.
     *
     * @param   array  $rows  The template rows
     *
     * @since   10.1.0
     */
    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }
}

/**
 * In-memory database double for CwmtemplatemigrationHelper.
 *
 * Delegates loadObjectList() to the shared TemplateMigStore so that after
 * updateTemplateParams() mutates the store, subsequent reads get fresh data.
 *
 * @since  10.1.0
 */
class TemplateMigFakeDb extends \Joomla\Database\DatabaseDriver
{
    /**
     * The store this double reads from.
     *
     * @var    TemplateMigStore
     * @since  10.1.0
     */
    private TemplateMigStore $store;

    /**
     * Keep the store, without connecting to a database.
     *
     * @param   TemplateMigStore  $store  The shared template store
     *
     * @since   10.1.0
     */
    public function __construct(TemplateMigStore $store)
    {
        // Skip parent constructor — no real DB connection needed
        $this->store = $store;
    }

    /**
     * A fresh query stub.
     *
     * @param   bool  $new  Ignored
     *
     * @return  TemplateMigFakeQuery  The stub
     *
     * @since   10.1.0
     */
    public function getQuery($new = false): TemplateMigFakeQuery
    {
        return new TemplateMigFakeQuery();
    }

    // The component builds queries with createQuery() (#1394). DatabaseDriver
    // declares a QueryInterface return type that the __call-based fake cannot
    // satisfy, and the inherited implementation needs a factory this double
    // skips constructing — so hand back a real query. The fake DB ignores what
    // is passed to setQuery() anyway, and building it for real costs nothing.
    /**
     * A real MySQLi query on this double, which the stub cannot stand in for.
     *
     * @return  \Joomla\Database\QueryInterface  The query
     *
     * @since   10.5.0
     */
    public function createQuery(): \Joomla\Database\QueryInterface
    {
        return new \Joomla\Database\Mysqli\MysqliQuery($this);
    }

    /**
     * Accept a query and ignore it.
     *
     * @param   mixed  $query   The query
     * @param   int    $offset  Ignored
     * @param   int    $limit   Ignored
     *
     * @return  static  This double
     *
     * @since   10.1.0
     */
    public function setQuery($query, $offset = 0, $limit = 0): static
    {
        return $this;
    }

    /**
     * The store's rows.
     *
     * @param   string  $key    Ignored
     * @param   string  $class  Ignored
     *
     * @return  array  The rows
     *
     * @since   10.1.0
     */
    public function loadObjectList($key = '', $class = \stdClass::class): array
    {
        return $this->store->rows;
    }

    /**
     * The params of the store's rows.
     *
     * @param   int  $offset  Ignored
     *
     * @return  array  The params values
     *
     * @since   10.1.0
     */
    public function loadColumn($offset = 0): array
    {
        return array_column($this->store->rows, 'params');
    }

    /**
     * Pretend to run the query.
     *
     * @return  bool  Always true
     *
     * @since   10.1.0
     */
    public function execute(): bool
    {
        return true;
    }

    /**
     * Quote a value with single quotes and addslashes().
     *
     * @param   mixed  $text    The value
     * @param   bool   $escape  Ignored
     *
     * @return  string  The quoted value
     *
     * @since   10.1.0
     */
    public function quote($text, $escape = true): string
    {
        return "'" . addslashes((string) $text) . "'";
    }

    /**
     * Wrap a name, or each of several names, in backticks.
     *
     * @param   string|array  $name  The name or names
     * @param   mixed         $as    Ignored
     *
     * @return  string|array  The quoted name or names
     *
     * @since   10.1.0
     */
    public function quoteName($name, $as = null): string|array
    {
        if (\is_array($name)) {
            return array_map(static fn ($n) => '`' . $n . '`', $name);
        }

        return '`' . (string) $name . '`';
    }

    // Abstract method implementations required by DatabaseDriver
    /**
     * No CREATE statements.
     *
     * @param   mixed  $tables  Ignored
     *
     * @return  array  An empty array
     *
     * @since   10.3.0
     */
    public function getTableCreate($tables)
    {
        return [];
    }

    /**
     * Not supported by this double.
     *
     * @param   string  $query  The query
     *
     * @return  \Joomla\Database\StatementInterface  Never; it always throws
     *
     * @since   10.3.0
     * @throws  \RuntimeException  Always
     */
    protected function prepareStatement(string $query): \Joomla\Database\StatementInterface
    {
        throw new \RuntimeException('prepareStatement not implemented in test fake');
    }

    /**
     * Report that the character set wasn't set.
     *
     * @return  bool  Always false
     *
     * @since   10.3.0
     */
    public function setUtf()
    {
        return false;
    }
    /**
     * Do nothing; there is no connection to make.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function connect()
    {
    }
    /**
     * Report a connection.
     *
     * @return  bool  Always true
     *
     * @since   10.3.0
     */
    public function connected()
    {
        return true;
    }
    /**
     * Escape a value with addslashes().
     *
     * @param   mixed  $text   The value
     * @param   bool   $extra  Ignored
     *
     * @return  string  The escaped value
     *
     * @since   10.3.0
     */
    public function escape($text, $extra = false)
    {
        return addslashes((string) $text);
    }
    /**
     * A fixed collation.
     *
     * @return  string  utf8mb4_general_ci
     *
     * @since   10.3.0
     */
    public function getCollation()
    {
        return 'utf8mb4_general_ci';
    }
    /**
     * A fixed connection collation.
     *
     * @return  string  utf8mb4_general_ci
     *
     * @since   10.3.0
     */
    public function getConnectionCollation()
    {
        return 'utf8mb4_general_ci';
    }
    /**
     * No connection encryption.
     *
     * @return  string  An empty string
     *
     * @since   10.3.0
     */
    public function getConnectionEncryption(): string
    {
        return '';
    }
    /**
     * Report no support for connection encryption.
     *
     * @return  bool  Always false
     *
     * @since   10.3.0
     */
    public function isConnectionEncryptionSupported(): bool
    {
        return false;
    }
    /**
     * No columns.
     *
     * @param   string  $table     Ignored
     * @param   bool    $typeOnly  Ignored
     *
     * @return  array  An empty array
     *
     * @since   10.3.0
     */
    public function getTableColumns($table, $typeOnly = true)
    {
        return [];
    }
    /**
     * No keys.
     *
     * @param   mixed  $tables  Ignored
     *
     * @return  array  An empty array
     *
     * @since   10.3.0
     */
    public function getTableKeys($tables)
    {
        return [];
    }
    /**
     * No tables.
     *
     * @return  array  An empty array
     *
     * @since   10.3.0
     */
    public function getTableList()
    {
        return [];
    }
    /**
     * A fixed server version.
     *
     * @return  string  8.0
     *
     * @since   10.3.0
     */
    public function getVersion()
    {
        return '8.0';
    }
    /**
     * No inserted id.
     *
     * @return  int  Always 0
     *
     * @since   10.3.0
     */
    public function insertid()
    {
        return 0;
    }
    /**
     * Report the driver as supported.
     *
     * @return  bool  Always true
     *
     * @since   10.3.0
     */
    public static function isSupported()
    {
        return true;
    }
    /**
     * Do nothing.
     *
     * @param   string  $tableName  Ignored
     *
     * @return  static  This double
     *
     * @since   10.3.0
     */
    public function lockTable($tableName)
    {
        return $this;
    }
    /**
     * Do nothing.
     *
     * @param   string  $oldTable  Ignored
     * @param   string  $newTable  Ignored
     * @param   mixed   $backup    Ignored
     * @param   mixed   $prefix    Ignored
     *
     * @return  static  This double
     *
     * @since   10.3.0
     */
    public function renameTable($oldTable, $newTable, $backup = null, $prefix = null)
    {
        return $this;
    }
    /**
     * Pretend to select a database.
     *
     * @param   string  $database  Ignored
     *
     * @return  bool  Always true
     *
     * @since   10.3.0
     */
    public function select($database)
    {
        return true;
    }
    /**
     * Do nothing.
     *
     * @param   bool  $toSavepoint  Ignored
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function transactionCommit($toSavepoint = false)
    {
    }
    /**
     * Do nothing.
     *
     * @param   bool  $toSavepoint  Ignored
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function transactionRollback($toSavepoint = false)
    {
    }
    /**
     * Do nothing.
     *
     * @param   bool  $asSavepoint  Ignored
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function transactionStart($asSavepoint = false)
    {
    }
    /**
     * Do nothing.
     *
     * @return  static  This double
     *
     * @since   10.3.0
     */
    public function unlockTables()
    {
        return $this;
    }
}

/**
 * Testable subclass of CwmtemplatemigrationHelper.
 *
 * Key design decisions:
 *  - Owns a TemplateMigStore shared with the injected FakeDb, so each migration
 *    stage sees updates written by the previous stage (matches real DB semantics).
 *  - Overrides updateTemplateParams() to mutate the shared store and capture the
 *    final params for assertion.
 *  - Exposes protected version-filter methods as public for white-box tests.
 *
 * @since  10.1.0
 */
class TestableCwmtemplatemigrationHelper extends CwmtemplatemigrationHelper
{
    /**
     * The store shared with the fake database.
     *
     * @var    TemplateMigStore
     * @since  10.1.0
     */
    private TemplateMigStore $store;

    /**
     * Final params JSON by template id; the last write for an id wins.
     *
     * @var    array<int, string>
     * @since  10.1.0
     */
    private array $finalParams = [];

    /**
     * Build the helper on a fake database that shares a store holding these rows.
     *
     * @param   array  $templateRows  The template rows
     *
     * @since   10.1.0
     */
    public function __construct(array $templateRows)
    {
        $this->store = new TemplateMigStore($templateRows);
        parent::__construct(new TemplateMigFakeDb($this->store));
    }

    // ---- Override to keep store and assertions in sync ----------------------

    /**
     * Write the params into the shared store, and record them for the assertions.
     *
     * @param   int     $templateId  The template
     * @param   string  $params      Its new params JSON
     *
     * @return  bool  Always true
     *
     * @since   10.1.0
     */
    protected function updateTemplateParams(int $templateId, string $params): bool
    {
        // Update the shared store so subsequent migration stages read fresh data.
        foreach ($this->store->rows as &$row) {
            if ((int) $row->id === $templateId) {
                $row->params = $params;
                break;
            }
        }

        // Capture for test assertions.
        $this->finalParams[$templateId] = $params;

        return true;
    }

    /**
     * Return the final params JSON for each template written during migration.
     *
     * @return  array<int, string>  Params JSON by template id
     *
     * @since   10.1.0
     */
    public function getSavedParams(): array
    {
        return $this->finalParams;
    }

    /**
     * Return the current (post-migration) params for template #$id as an array.
     *
     * @param   int  $id  The template's id
     *
     * @return  array  The params, or an empty array when the template has none
     *
     * @since   10.1.0
     */
    public function getParamsArray(int $id = 1): array
    {
        return json_decode($this->finalParams[$id] ?? '{}', true) ?? [];
    }

    // ---- Expose protected version-filter methods for white-box tests --------

    /**
     * Expose getMigrationsAfterVersion().
     *
     * @param   string  $fromVersion  The version migrated from
     *
     * @return  array  The migrations after it
     *
     * @since   10.1.0
     */
    public function callGetMigrationsAfterVersion(string $fromVersion): array
    {
        return $this->getMigrationsAfterVersion($fromVersion);
    }

    /**
     * Expose getRenamesAfterVersion().
     *
     * @param   string  $fromVersion  The version migrated from
     *
     * @return  array  The renames after it
     *
     * @since   10.1.0
     */
    public function callGetRenamesAfterVersion(string $fromVersion): array
    {
        return $this->getRenamesAfterVersion($fromVersion);
    }

    /**
     * Expose getColorConversionsAfterVersion().
     *
     * @param   string  $fromVersion  The version migrated from
     *
     * @return  array  The color conversions after it
     *
     * @since   10.1.0
     */
    public function callGetColorConversionsAfterVersion(string $fromVersion): array
    {
        return $this->getColorConversionsAfterVersion($fromVersion);
    }

    /**
     * Expose getPathConversionsAfterVersion().
     *
     * @param   string  $fromVersion  The version migrated from
     *
     * @return  array  The path conversions after it
     *
     * @since   10.1.0
     */
    public function callGetPathConversionsAfterVersion(string $fromVersion): array
    {
        return $this->getPathConversionsAfterVersion($fromVersion);
    }

    // ---- Expose individual migration stage methods for isolated testing -----

    /**
     * Expose convertPathsInTemplates().
     *
     * @param   array  $replacements  Search => replace path pairs
     *
     * @return  int  Number of templates updated
     *
     * @since   10.1.0
     */
    public function callConvertPathsInTemplates(array $replacements): int
    {
        return $this->convertPathsInTemplates($replacements);
    }
}

// ============================================================================
// Factory helper
// ============================================================================

/**
 * Build a testable helper pre-loaded with the given template params.
 *
 * @param   array<array<string,mixed>|string>  $templates  Each template's params, as an array or JSON
 *
 * @return  TestableCwmtemplatemigrationHelper  The helper
 *
 * @since   10.1.0
 */
function makeMigHelper(array $templates): TestableCwmtemplatemigrationHelper
{
    $rows = [];

    foreach ($templates as $i => $params) {
        $row         = new \stdClass();
        $row->id     = $i + 1;
        $row->title  = 'Template ' . ($i + 1);
        $row->params = \is_array($params) ? json_encode($params, JSON_UNESCAPED_SLASHES) : (string) $params;
        $rows[]      = $row;
    }

    return new TestableCwmtemplatemigrationHelper($rows);
}

// ============================================================================
// Test class
// ============================================================================

/**
 * Unit tests for CwmtemplatemigrationHelper.
 *
 * Covers the backward-compatibility migration logic that runs automatically
 * when upgrading from Proclaim 10.0.x to 10.1.0.
 *
 * Test categories
 * ---------------
 *  1. Version filtering       — correct migrations selected for fromVersion
 *  2. B/C data completeness   — all documented params/renames/fields registered
 *  3. Apply-if-missing logic  — new defaults added only when absent
 *  4. Rename logic            — old name removed, value transferred to new name
 *  5. Color conversion        — 0x format → # hex format
 *  6. Path conversion         — com_biblestudy → com_proclaim
 *  7. Rowspan image migration — rowspanitem values mapped to Layout Editor elements
 *  8. Public API              — getMigrations(), addMigration(), migrateAll()
 *
 * @package  Proclaim.Tests
 * @since    10.1.0
 */
class CwmtemplatemigrationHelperTest extends ProclaimTestCase
{
    // =========================================================================
    // Section 1 – Version filtering (pure logic, no DB)
    // =========================================================================

    /**
     * From 0.0.0, the 10.1.0 migrations are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testMigrationsAfterVersion000Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetMigrationsAfterVersion('0.0.0'));
    }

    /**
     * From 10.1.0, no migrations are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testMigrationsAfterVersion101ReturnsEmpty(): void
    {
        $this->assertEmpty(
            makeMigHelper([])->callGetMigrationsAfterVersion('10.1.0'),
            'No migrations should run when fromVersion = 10.1.0'
        );
    }

    /**
     * From 10.0.0, the 10.1.0 migrations are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testMigrationsAfterVersion100Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetMigrationsAfterVersion('10.0.0'));
    }

    /**
     * From 10.9.9, the 10.1.0 migrations are not selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testMigrationsAfterVersion1099DoesNotReturn101(): void
    {
        $this->assertArrayNotHasKey('10.1.0', makeMigHelper([])->callGetMigrationsAfterVersion('10.9.9'));
    }

    /**
     * From 0.0.0, the 10.1.0 renames are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRenamesAfterVersion000Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetRenamesAfterVersion('0.0.0'));
    }

    /**
     * From 10.1.0, no renames are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRenamesAfterVersion101ReturnsEmpty(): void
    {
        $this->assertEmpty(makeMigHelper([])->callGetRenamesAfterVersion('10.1.0'));
    }

    /**
     * From 0.0.0, the 10.1.0 color conversions are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testColorConversionsAfterVersion000Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetColorConversionsAfterVersion('0.0.0'));
    }

    /**
     * From 10.1.0, no color conversions are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testColorConversionsAfterVersion101ReturnsEmpty(): void
    {
        $this->assertEmpty(makeMigHelper([])->callGetColorConversionsAfterVersion('10.1.0'));
    }

    /**
     * From 0.0.0, the 10.1.0 path conversions are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testPathConversionsAfterVersion000Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetPathConversionsAfterVersion('0.0.0'));
    }

    /**
     * From 10.1.0, no path conversions are selected.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testPathConversionsAfterVersion101ReturnsEmpty(): void
    {
        $this->assertEmpty(makeMigHelper([])->callGetPathConversionsAfterVersion('10.1.0'));
    }

    // =========================================================================
    // Section 2a – B/C completeness: new 10.1.0 default parameters
    // =========================================================================

    /**
     * Every parameter documented in Backward-Compatibility-Breaks-10.1.md
     * must appear in the $migrations array with the correct default value.
     *
     * @param   string  $param            The parameter's name
     * @param   string  $expectedDefault  The default it should be added with
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('provideExpectedNewParams')]
    public function testMigrationHasAllDocumentedNewParams(string $param, string $expectedDefault): void
    {
        $migrations = makeMigHelper([])->getMigrations();

        $this->assertArrayHasKey('10.1.0', $migrations);
        $this->assertArrayHasKey($param, $migrations['10.1.0'], "Missing migration param: {$param}");
        $this->assertSame($expectedDefault, $migrations['10.1.0'][$param], "Wrong default for {$param}");
    }

    /**
     * Each new 10.1.0 parameter, with the default it should be added with.
     *
     * @return  array<string, array{string, string}>  Each case
     *
     * @since   10.1.0
     */
    public static function provideExpectedNewParams(): array
    {
        return [
            'default_show_archived'            => ['default_show_archived', '2'],
            'default_show_archive_badge'       => ['default_show_archive_badge', '1'],
            'show_passage_view'                => ['show_passage_view', '3'],
            'showpassage_icon'                 => ['showpassage_icon', '1'],
            'allow_version_switch'             => ['allow_version_switch', '0'],
            'listheadertype'                   => ['listheadertype', 'table-light'],
            'scripture_separator'              => ['scripture_separator', 'middot'],
            'pagination_style'                 => ['pagination_style', 'pagination'],
            'infinite_scroll_threshold'        => ['infinite_scroll_threshold', '3'],
            'series_pagination_style'          => ['series_pagination_style', 'pagination'],
            'series_infinite_scroll_threshold' => ['series_infinite_scroll_threshold', '3'],
        ];
    }

    /**
     * The 10.1.0 migration defines exactly 11 new default params.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testMigrationHasExactly11ParamsFor101(): void
    {
        $migrations = makeMigHelper([])->getMigrations();

        $this->assertCount(11, $migrations['10.1.0'], '10.1.0 must define exactly 11 new default params');
    }

    // =========================================================================
    // Section 2b – B/C completeness: param renames
    // =========================================================================

    /**
     * Every rename documented in Backward-Compatibility-Breaks-10.1.md must be
     * registered in the $renames array.
     *
     * @param   string  $oldName  The parameter's old name
     * @param   string  $newName  The name it is renamed to
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('provideExpectedRenames')]
    public function testRenameMapHasAllDocumentedEntries(string $oldName, string $newName): void
    {
        $h    = makeMigHelper([]);
        $ref  = new \ReflectionClass(CwmtemplatemigrationHelper::class);
        $prop = $ref->getProperty('renames');
        $map  = $prop->getValue($h);

        $this->assertArrayHasKey('10.1.0', $map, '10.1.0 renames must exist');
        $this->assertArrayHasKey($oldName, $map['10.1.0'], "Missing rename for: {$oldName}");
        $this->assertSame($newName, $map['10.1.0'][$oldName], "Wrong mapping for: {$oldName}");
    }

    /**
     * Each 10.1.0 rename, old name then new.
     *
     * @return  array<string, array{string, string}>  Each case
     *
     * @since   10.1.0
     */
    public static function provideExpectedRenames(): array
    {
        return [
            'show_type_search'      => ['show_type_search',      'show_messagetype_search'],
            'show_locations_search' => ['show_locations_search', 'show_location_search'],
            'teacher_id'            => ['teacher_id',            'lteacher_id'],
            'series_id'             => ['series_id',             'lseries_id'],
            'booknumber'            => ['booknumber',            'lbooknumber'],
            'topic_id'              => ['topic_id',              'ltopic_id'],
            'messagetype'           => ['messagetype',           'lmessagetype'],
            'locations'             => ['locations',             'llocations'],
            'teacherimagerrow'      => ['teacherimagerrow',      'teacherimagerow'],
            'dteacherimagerrow'     => ['dteacherimagerrow',     'dteacherimagerow'],
            'tsteacherimagerrow'    => ['tsteacherimagerrow',    'tsteacherimagerow'],
            'tdteacherimagerrow'    => ['tdteacherimagerrow',    'tdteacherimagerow'],
            'steacherimagerrow'     => ['steacherimagerrow',     'steacherimagerow'],
            'sdteacherimagerrow'    => ['sdteacherimagerrow',    'sdteacherimagerow'],
        ];
    }

    /**
     * The 10.1.0 rename map has exactly 14 entries.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRenameMapHasExactly14EntriesFor101(): void
    {
        $h    = makeMigHelper([]);
        $ref  = new \ReflectionClass(CwmtemplatemigrationHelper::class);
        $prop = $ref->getProperty('renames');
        $map  = $prop->getValue($h);

        $this->assertCount(14, $map['10.1.0'], 'Exactly 14 renames documented for 10.1.0');
    }

    // =========================================================================
    // Section 2c – B/C completeness: color fields
    // =========================================================================

    /**
     * Each documented color field is registered for the 10.1.0 color conversion.
     *
     * @param   string  $field  The field's name
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('provideExpectedColorFields')]
    public function testColorConversionListHasAllDocumentedFields(string $field): void
    {
        $h      = makeMigHelper([]);
        $ref    = new \ReflectionClass(CwmtemplatemigrationHelper::class);
        $prop   = $ref->getProperty('colorConversions');
        $colors = $prop->getValue($h);

        $this->assertArrayHasKey('10.1.0', $colors);
        $this->assertContains($field, $colors['10.1.0'], "Color field not registered: {$field}");
    }

    /**
     * Each color field the 10.1.0 conversion must cover.
     *
     * @return  array<string, array{string}>  Each case
     *
     * @since   10.1.0
     */
    public static function provideExpectedColorFields(): array
    {
        return [
            'backcolor'            => ['backcolor'],
            'frontcolor'           => ['frontcolor'],
            'lightcolor'           => ['lightcolor'],
            'screencolor'          => ['screencolor'],
            'popupbackground'      => ['popupbackground'],
            'teacherdisplay_color' => ['teacherdisplay_color'],
            'seriesdisplay_color'  => ['seriesdisplay_color'],
        ];
    }

    // =========================================================================
    // Section 2d – B/C completeness: path conversions
    // =========================================================================

    /**
     * The 10.1.0 path conversions map media/com_biblestudy/ to media/com_proclaim/.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testPathConversionHasBibleStudyToProclaimMapping(): void
    {
        $h     = makeMigHelper([]);
        $ref   = new \ReflectionClass(CwmtemplatemigrationHelper::class);
        $prop  = $ref->getProperty('pathConversions');
        $paths = $prop->getValue($h);

        $this->assertArrayHasKey('10.1.0', $paths);
        $this->assertArrayHasKey('media/com_biblestudy/', $paths['10.1.0']);
        $this->assertSame('media/com_proclaim/', $paths['10.1.0']['media/com_biblestudy/']);
    }

    // =========================================================================
    // Section 3 – apply-if-missing logic
    // =========================================================================

    /**
     * Migrating an empty template adds the pagination_style default.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testApplyParamsAddsDefaultToEmptyTemplate(): void
    {
        $h = makeMigHelper(['{}']);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('pagination_style', $params);
        $this->assertSame('pagination', $params['pagination_style']);
    }

    /**
     * Migrating keeps a template's own pagination_style value.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testApplyParamsDoesNotOverwriteExistingValue(): void
    {
        $h = makeMigHelper([['pagination_style' => 'loadmore']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertSame('loadmore', $params['pagination_style'] ?? 'pagination', 'Custom value must not be overwritten');
    }

    /**
     * Migrating from 10.1.0 runs no migrations and writes nothing.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testMigrateFromSameVersionRunsNothing(): void
    {
        $h     = makeMigHelper(['{}']);
        $count = $h->migrateFromVersion('10.1.0');

        $this->assertSame(0, $count, 'Upgrading from 10.1.0 should run no migrations');
        $this->assertEmpty($h->getSavedParams(), 'No writes should happen at same version');
    }

    // =========================================================================
    // Section 4 – rename logic
    // =========================================================================

    /**
     * A rename moves the old param's value to the new name and removes the old name.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRenameTransfersValueToNewName(): void
    {
        $h = makeMigHelper([['teacher_id' => '42']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('lteacher_id', $params, 'New param name must exist after rename');
        $this->assertSame('42', (string) $params['lteacher_id'], 'Value must be preserved during rename');
        $this->assertArrayNotHasKey('teacher_id', $params, 'Old param name must be removed');
    }

    /**
     * All six list filter params are renamed to their l-prefixed names, and the old names
     * are removed.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRenameAllLPrefixParams(): void
    {
        $old = [
            'teacher_id'  => '1',
            'series_id'   => '2',
            'booknumber'  => '101',
            'topic_id'    => '5',
            'messagetype' => '3',
            'locations'   => '7',
        ];
        $h = makeMigHelper([$old]);
        $h->migrateFromVersion('0.0.0');

        $params   = $h->getParamsArray(1);
        $expected = ['lteacher_id', 'lseries_id', 'lbooknumber', 'ltopic_id', 'lmessagetype', 'llocations'];

        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $params, "Renamed param '{$key}' must exist");
        }

        foreach (array_keys($old) as $key) {
            $this->assertArrayNotHasKey($key, $params, "Old param '{$key}' must be removed");
        }
    }

    /**
     * The two search filter params are renamed, and the old names are removed.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRenameSearchFilterParams(): void
    {
        $h = makeMigHelper([[
            'show_type_search'      => '1',
            'show_locations_search' => '0',
        ]]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('show_messagetype_search', $params);
        $this->assertArrayHasKey('show_location_search', $params);
        $this->assertArrayNotHasKey('show_type_search', $params);
        $this->assertArrayNotHasKey('show_locations_search', $params);
    }

    /**
     * teacherimagerrow is corrected to teacherimagerow in all six contexts, and the
     * misspelled names are removed.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRenameTypoFixAllSixContextPrefixes(): void
    {
        $typos = [
            'teacherimagerrow'   => '1',
            'dteacherimagerrow'  => '1',
            'tsteacherimagerrow' => '1',
            'tdteacherimagerrow' => '1',
            'steacherimagerrow'  => '1',
            'sdteacherimagerrow' => '1',
        ];
        $h = makeMigHelper([$typos]);
        $h->migrateFromVersion('0.0.0');

        $params  = $h->getParamsArray(1);
        $correct = [
            'teacherimagerow', 'dteacherimagerow', 'tsteacherimagerow',
            'tdteacherimagerow', 'steacherimagerow', 'sdteacherimagerow',
        ];

        foreach ($correct as $name) {
            $this->assertArrayHasKey($name, $params, "Fixed name '{$name}' must exist");
        }

        foreach (array_keys($typos) as $typo) {
            $this->assertArrayNotHasKey($typo, $params, "Typo '{$typo}' must be removed");
        }
    }

    /**
     * A param already under its new name keeps its value, and the old name doesn't
     * appear.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRenameDoesNotTouchAlreadyNewStyleParam(): void
    {
        $h = makeMigHelper([['lteacher_id' => '7']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertSame('7', (string) ($params['lteacher_id'] ?? ''), 'Already-new-style value must be preserved');
        $this->assertArrayNotHasKey('teacher_id', $params, 'Old name must not appear after migration');
    }

    /**
     * Table::bind() merges the submitted form array on top of currently
     * stored params, so an admin editing a template whose migration hasn't
     * run yet can end up with BOTH the stale old-name key and a real,
     * just-saved new-name value present at once. The rename copy must not
     * blindly overwrite that just-saved value with the stale one -- see
     * #1541.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testRenameDoesNotOverwriteAlreadyPopulatedNewName(): void
    {
        $h = makeMigHelper([['teacher_id' => '5', 'lteacher_id' => '7']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertSame(
            '7',
            (string) ($params['lteacher_id'] ?? ''),
            'must not clobber the already-populated new-name value with the stale old-name value'
        );
        $this->assertArrayNotHasKey('teacher_id', $params, 'stale old name must still be removed');
    }

    // =========================================================================
    // Section 5 – color conversion
    // =========================================================================

    /**
     * An upper-case 0x color becomes the same color with a hash.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testColorConversionConverts0xUppercaseToHash(): void
    {
        $h = makeMigHelper([['backcolor' => '0xFF0000']]);
        $h->migrateFromVersion('0.0.0');

        $this->assertSame('#FF0000', $h->getParamsArray(1)['backcolor'] ?? null);
    }

    /**
     * A lower-case 0x color becomes an upper-case hash color.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testColorConversionConverts0xLowercaseToHashUppercase(): void
    {
        $h = makeMigHelper([['frontcolor' => '0xaabbcc']]);
        $h->migrateFromVersion('0.0.0');

        $this->assertSame('#AABBCC', $h->getParamsArray(1)['frontcolor'] ?? null, '0xaabbcc must become #AABBCC');
    }

    /**
     * All seven color fields are converted from 0x to hash format.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testColorConversionConvertsAllSevenFields(): void
    {
        $input = [
            'backcolor'            => '0x112233',
            'frontcolor'           => '0x445566',
            'lightcolor'           => '0x778899',
            'screencolor'          => '0xAABBCC',
            'popupbackground'      => '0xDDEEFF',
            'teacherdisplay_color' => '0x010203',
            'seriesdisplay_color'  => '0xFEDCBA',
        ];
        $h = makeMigHelper([$input]);
        $h->migrateFromVersion('0.0.0');

        $params   = $h->getParamsArray(1);
        $expected = [
            'backcolor'            => '#112233',
            'frontcolor'           => '#445566',
            'lightcolor'           => '#778899',
            'screencolor'          => '#AABBCC',
            'popupbackground'      => '#DDEEFF',
            'teacherdisplay_color' => '#010203',
            'seriesdisplay_color'  => '#FEDCBA',
        ];

        foreach ($expected as $field => $value) {
            $this->assertSame($value, $params[$field] ?? null, "Wrong conversion for: {$field}");
        }
    }

    /**
     * A color already in hash format comes through the migration unchanged.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testColorConversionDoesNotTouchValidHashFormat(): void
    {
        $h = makeMigHelper([['backcolor' => '#FF0000']]);
        $h->migrateFromVersion('0.0.0');

        // migrateFromVersion('0.0.0') always ends up writing an update for
        // this template -- applyParamsToTemplates() adds missing
        // version-gated default params regardless of color conversion -- so
        // this can't assert "no update happened". What it can assert
        // unconditionally is that backcolor itself, already in valid #
        // format, comes through that write unchanged.
        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('backcolor', $params);
        $this->assertSame('#FF0000', $params['backcolor'], '# format must not be altered');
    }

    /**
     * An empty color value comes through the migration unchanged.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testColorConversionDoesNotTouchEmptyValue(): void
    {
        $h = makeMigHelper([['backcolor' => '']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('backcolor', $params);
        $this->assertSame('', $params['backcolor'], 'Empty color value must not be changed');
    }

    // =========================================================================
    // Section 6 – path conversion
    // =========================================================================

    /**
     * A com_biblestudy path is rewritten to com_proclaim in the saved params.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testPathConversionReplacesBibleStudyPath(): void
    {
        $h = makeMigHelper([['custom_icon' => 'media/com_biblestudy/images/icon.png']]);
        $h->migrateFromVersion('0.0.0');

        $saved = $h->getSavedParams();
        $this->assertNotEmpty($saved, 'Template with legacy path must produce an update');

        // Path replacement is done on the raw JSON string, check JSON directly
        $json = reset($saved);
        $this->assertStringContainsString('com_proclaim', $json, 'Updated JSON must contain new path');
        $this->assertStringNotContainsString('com_biblestudy', $json, 'Old path must be gone from JSON');
    }

    /**
     * A com_proclaim path comes through the migration unchanged.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testPathConversionDoesNotAlterCurrentPath(): void
    {
        $h = makeMigHelper([['custom_icon' => 'media/com_proclaim/images/icon.png']]);
        $h->migrateFromVersion('0.0.0');

        // Same reasoning as the color-conversion tests above: an update
        // still gets written for unrelated reasons (default param
        // additions), so assert the already-current path survives it
        // unchanged rather than asserting no write happened at all.
        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('custom_icon', $params);
        $this->assertSame('media/com_proclaim/images/icon.png', $params['custom_icon']);
    }

    // =========================================================================
    // Section 7 – rowspan image migration
    // =========================================================================

    /**
     * Each rowspanitem value places its element in row 1, and resets rowspanitem to 0.
     *
     * @param   int     $rowspanitem  The legacy rowspanitem value
     * @param   string  $elementKey   The element it maps to
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[DataProvider('provideRowspanItemMap')]
    public function testRowspanItemMapsToElement(int $rowspanitem, string $elementKey): void
    {
        $h = makeMigHelper([['rowspanitem' => (string) $rowspanitem, 'rowspanitemspan' => '3']]);
        $h->migrateRowspanImages();

        $saved = $h->getSavedParams();
        $this->assertNotEmpty($saved, "rowspanitem={$rowspanitem} should produce an update");

        $params  = json_decode(reset($saved), true);
        $rowKey  = $elementKey . 'row';
        $this->assertSame('1', (string) ($params[$rowKey] ?? ''), "rowspanitem={$rowspanitem} must set {$rowKey}=1");
        $this->assertSame('0', (string) ($params['rowspanitem'] ?? ''), 'rowspanitem must be reset to 0');
    }

    /**
     * Each rowspanitem value, with the element it maps to.
     *
     * @return  array<string, array{int, string}>  Each case
     *
     * @since   10.1.0
     */
    public static function provideRowspanItemMap(): array
    {
        return [
            'rowspanitem=1 → teacherimage'      => [1, 'teacherimage'],
            'rowspanitem=2 → thumbnail'         => [2, 'thumbnail'],
            'rowspanitem=3 → seriesthumbnail'   => [3, 'seriesthumbnail'],
            'rowspanitem=4 → teacherlargeimage' => [4, 'teacherlargeimage'],
        ];
    }

    /**
     * A rowspanitem of 0 migrates nothing and writes nothing.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRowspanItemZeroIsSkipped(): void
    {
        $h = makeMigHelper([['rowspanitem' => '0']]);

        $this->assertSame(0, $h->migrateRowspanImages(), 'rowspanitem=0 must not trigger migration');
        $this->assertEmpty($h->getSavedParams());
    }

    /**
     * Each Bootstrap 2 image class becomes its Bootstrap 5 equivalent in the element's
     * custom class.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRowspanItemMigratesBootstrap2ClassToBootstrap5(): void
    {
        $cases = [
            'img-rounded'  => 'rounded',
            'img-polaroid' => 'img-thumbnail',
            'img-circle'   => 'rounded-circle',
        ];

        foreach ($cases as $legacy => $modern) {
            $h = makeMigHelper([[
                'rowspanitem'      => '1',   // teacherimage
                'rowspanitemspan'  => '4',
                'rowspanitemimage' => $legacy,
            ]]);
            $h->migrateRowspanImages();

            $saved  = $h->getSavedParams();
            $params = json_decode(reset($saved), true);
            $this->assertSame($modern, $params['teacherimagecustom'] ?? '', "{$legacy} must become {$modern}");
        }
    }

    /**
     * The messages and series rowspan settings both migrate, each to its own element.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRowspanItemMigratesTwoContextPrefixes(): void
    {
        $h = makeMigHelper([[
            'rowspanitem'      => '2',   // messages context → thumbnail
            'rowspanitemspan'  => '4',
            'srowspanitem'     => '3',   // series context → seriesthumbnail
            'srowspanitemspan' => '4',
        ]]);
        $h->migrateRowspanImages();

        $saved  = $h->getSavedParams();
        $this->assertNotEmpty($saved);

        $params = json_decode(reset($saved), true);
        $this->assertSame('1', (string) ($params['thumbnailrow'] ?? ''), 'thumbnailrow=1 for messages context');
        $this->assertSame('1', (string) ($params['sseriesthumbnailrow'] ?? ''), 'sseriesthumbnailrow=1 for series context');
    }

    /**
     * An element already placed keeps its row, and the legacy rowspanitem is still reset
     * to 0.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testRowspanItemSkipsElementAlreadyPlaced(): void
    {
        // thumbnail is already placed in row 2 — migration must not move it to row 1
        $h = makeMigHelper([[
            'rowspanitem'     => '2',   // thumbnail
            'rowspanitemspan' => '4',
            'thumbnailrow'    => '2',   // already placed!
        ]]);
        $h->migrateRowspanImages();

        // A save always happens here: the legacy rowspanitem flag is reset
        // to '0' even when the element is already placed, so the update path
        // runs regardless. Assert the row value unconditionally -- the prior
        // isset()-guarded version was vacuously green if the migration
        // dropped/renamed thumbnailrow entirely (same bug class as the three
        // fixed in PR #1595).
        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('thumbnailrow', $params);
        $this->assertSame('2', (string) $params['thumbnailrow'], 'Already-placed element must not be moved');
        $this->assertSame('0', (string) ($params['rowspanitem'] ?? ''), 'Legacy flag must still be cleared');
    }

    /**
     * migrateRowspanImages() has no version of its own to gate on -- it's
     * meant to run on every migrateFromVersion() call regardless of
     * $fromVersion. All four version-gated migration/rename/color/path
     * arrays are keyed only under '10.1.0' with no later entries, so any
     * $fromVersion >= '10.1.0' (i.e. every currently-supported upgrade
     * path) used to hit an early return before ever reaching the rowspan
     * cleanup. Uses a $fromVersion well past '10.1.0' to reproduce that
     * -- see #1541.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testMigrateFromVersionPastAllGatedMigrationsStillRunsRowspanCleanup(): void
    {
        $h       = makeMigHelper([['rowspanitem' => '1', 'rowspanitemspan' => '4']]);
        $updated = $h->migrateFromVersion('99.0.0');

        $this->assertSame(1, $updated, 'rowspan cleanup must still run and count as an update');

        $params = $h->getParamsArray(1);
        $this->assertSame('1', (string) ($params['teacherimagerow'] ?? ''), 'rowspanitem=1 must still migrate to teacherimagerow=1');
        $this->assertSame('0', (string) ($params['rowspanitem'] ?? ''), 'rowspanitem must still be reset to 0');
    }

    // =========================================================================
    // Section 8 – Public API
    // =========================================================================

    /**
     * getMigrations() returns an array.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testGetMigrationsReturnsArray(): void
    {
        $this->assertIsArray(makeMigHelper([])->getMigrations());
    }

    /**
     * addMigration() adds a version's params to the migrations.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testAddMigrationInsertsCustomVersionEntry(): void
    {
        $h = makeMigHelper([]);
        $h->addMigration('11.0.0', ['new_feature' => '0']);

        $migrations = $h->getMigrations();
        $this->assertArrayHasKey('11.0.0', $migrations);
        $this->assertSame('0', $migrations['11.0.0']['new_feature']);
    }

    /**
     * An added 11.0.0 migration is selected from 10.0.0 but not from 11.0.0.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testAddMigrationVersionFilteringWorks(): void
    {
        $h = makeMigHelper([]);
        $h->addMigration('11.0.0', ['future_feature' => '1']);

        // From 10.0.0: both 10.1.0 and 11.0.0 migrations must run
        $this->assertArrayHasKey('11.0.0', $h->callGetMigrationsAfterVersion('10.0.0'));

        // From 11.0.0: 11.0.0 must NOT run (already at that version)
        $this->assertArrayNotHasKey('11.0.0', $h->callGetMigrationsAfterVersion('11.0.0'));
    }

    /**
     * migrateAll() updates at least one template and writes its params.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testMigrateAllRunsAllMigrations(): void
    {
        $h     = makeMigHelper(['{}']);
        $count = $h->migrateAll();

        $this->assertGreaterThan(0, $count, 'migrateAll() must update at least one template');
        $this->assertNotEmpty($h->getSavedParams(), 'migrateAll() must produce at least one write');
    }

    /**
     * migrateAll() updates as many templates as migrateFromVersion('0.0.0').
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testMigrateAllAndMigrateFromVersionZeroProduceSameResult(): void
    {
        $h1 = makeMigHelper(['{}']);
        $h2 = makeMigHelper(['{}']);

        $c1 = $h1->migrateFromVersion('0.0.0');
        $c2 = $h2->migrateAll();

        $this->assertSame($c1, $c2, 'migrateAll() and migrateFromVersion(0.0.0) must produce same update count');
    }
}

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

// ============================================================================
// Test infrastructure — local to this file
// ============================================================================

/**
 * Fluent query builder stub.
 *
 * Accepts any method call and returns $this so fluent builder chains inside
 * CwmtemplatemigrationHelper work without a real database.
 */
class TemplateMigFakeQuery
{
    public function __call(string $name, array $args): static
    {
        return $this;
    }

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
 */
class TemplateMigStore
{
    /** @var object[] */
    public array $rows = [];

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
 */
class TemplateMigFakeDb extends \Joomla\Database\DatabaseDriver implements \Joomla\Database\DatabaseInterface
{
    private TemplateMigStore $store;

    public function __construct(TemplateMigStore $store)
    {
        $this->store = $store;
    }

    public function getQuery($new = false): TemplateMigFakeQuery
    {
        return new TemplateMigFakeQuery();
    }

    public function setQuery($query, $offset = 0, $limit = 0): static
    {
        return $this;
    }

    public function loadObjectList($key = '', $class = \stdClass::class): array
    {
        return $this->store->rows;
    }

    public function loadColumn($offset = 0): array
    {
        return array_column($this->store->rows, 'params');
    }

    public function execute(): bool
    {
        return true;
    }

    public function quote($text, $escape = true): string
    {
        return "'" . addslashes((string) $text) . "'";
    }

    public function quoteName($name, $as = null): string|array
    {
        if (\is_array($name)) {
            return array_map(static fn ($n) => '`' . $n . '`', $name);
        }

        return '`' . (string) $name . '`';
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
 */
class TestableCwmtemplatemigrationHelper extends CwmtemplatemigrationHelper
{
    private TemplateMigStore $store;

    /** @var array<int, string>  id → final params JSON (last write wins per ID) */
    private array $finalParams = [];

    public function __construct(array $templateRows)
    {
        $this->store = new TemplateMigStore($templateRows);
        parent::__construct(new TemplateMigFakeDb($this->store));
    }

    // ---- Override to keep store and assertions in sync ----------------------

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

    /** Return the final params JSON for each template written during migration. */
    public function getSavedParams(): array
    {
        return $this->finalParams;
    }

    /** Return the current (post-migration) params for template #$id as an array. */
    public function getParamsArray(int $id = 1): array
    {
        return json_decode($this->finalParams[$id] ?? '{}', true) ?? [];
    }

    // ---- Expose protected version-filter methods for white-box tests --------

    public function callGetMigrationsAfterVersion(string $fromVersion): array
    {
        return $this->getMigrationsAfterVersion($fromVersion);
    }

    public function callGetRenamesAfterVersion(string $fromVersion): array
    {
        return $this->getRenamesAfterVersion($fromVersion);
    }

    public function callGetColorConversionsAfterVersion(string $fromVersion): array
    {
        return $this->getColorConversionsAfterVersion($fromVersion);
    }

    public function callGetPathConversionsAfterVersion(string $fromVersion): array
    {
        return $this->getPathConversionsAfterVersion($fromVersion);
    }

    // ---- Expose individual migration stage methods for isolated testing -----

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
 * @param  array<array<string,mixed>|string>  $templates
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

    public function testMigrationsAfterVersion000Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetMigrationsAfterVersion('0.0.0'));
    }

    public function testMigrationsAfterVersion101ReturnsEmpty(): void
    {
        $this->assertEmpty(
            makeMigHelper([])->callGetMigrationsAfterVersion('10.1.0'),
            'No migrations should run when fromVersion = 10.1.0'
        );
    }

    public function testMigrationsAfterVersion100Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetMigrationsAfterVersion('10.0.0'));
    }

    public function testMigrationsAfterVersion1099DoesNotReturn101(): void
    {
        $this->assertArrayNotHasKey('10.1.0', makeMigHelper([])->callGetMigrationsAfterVersion('10.9.9'));
    }

    public function testRenamesAfterVersion000Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetRenamesAfterVersion('0.0.0'));
    }

    public function testRenamesAfterVersion101ReturnsEmpty(): void
    {
        $this->assertEmpty(makeMigHelper([])->callGetRenamesAfterVersion('10.1.0'));
    }

    public function testColorConversionsAfterVersion000Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetColorConversionsAfterVersion('0.0.0'));
    }

    public function testColorConversionsAfterVersion101ReturnsEmpty(): void
    {
        $this->assertEmpty(makeMigHelper([])->callGetColorConversionsAfterVersion('10.1.0'));
    }

    public function testPathConversionsAfterVersion000Returns101(): void
    {
        $this->assertArrayHasKey('10.1.0', makeMigHelper([])->callGetPathConversionsAfterVersion('0.0.0'));
    }

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
     * @dataProvider provideExpectedNewParams
     */
    public function testMigrationHasAllDocumentedNewParams(string $param, string $expectedDefault): void
    {
        $migrations = makeMigHelper([])->getMigrations();

        $this->assertArrayHasKey('10.1.0', $migrations);
        $this->assertArrayHasKey($param, $migrations['10.1.0'], "Missing migration param: {$param}");
        $this->assertSame($expectedDefault, $migrations['10.1.0'][$param], "Wrong default for {$param}");
    }

    /** @return array<string, array{string, string}> */
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
     * @dataProvider provideExpectedRenames
     */
    public function testRenameMapHasAllDocumentedEntries(string $oldName, string $newName): void
    {
        $h    = makeMigHelper([]);
        $ref  = new \ReflectionClass(CwmtemplatemigrationHelper::class);
        $prop = $ref->getProperty('renames');
        $prop->setAccessible(true);
        $map  = $prop->getValue($h);

        $this->assertArrayHasKey('10.1.0', $map, '10.1.0 renames must exist');
        $this->assertArrayHasKey($oldName, $map['10.1.0'], "Missing rename for: {$oldName}");
        $this->assertSame($newName, $map['10.1.0'][$oldName], "Wrong mapping for: {$oldName}");
    }

    /** @return array<string, array{string, string}> */
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

    public function testRenameMapHasExactly14EntriesFor101(): void
    {
        $h    = makeMigHelper([]);
        $ref  = new \ReflectionClass(CwmtemplatemigrationHelper::class);
        $prop = $ref->getProperty('renames');
        $prop->setAccessible(true);
        $map  = $prop->getValue($h);

        $this->assertCount(14, $map['10.1.0'], 'Exactly 14 renames documented for 10.1.0');
    }

    // =========================================================================
    // Section 2c – B/C completeness: color fields
    // =========================================================================

    /**
     * @dataProvider provideExpectedColorFields
     */
    public function testColorConversionListHasAllDocumentedFields(string $field): void
    {
        $h    = makeMigHelper([]);
        $ref  = new \ReflectionClass(CwmtemplatemigrationHelper::class);
        $prop = $ref->getProperty('colorConversions');
        $prop->setAccessible(true);
        $colors = $prop->getValue($h);

        $this->assertArrayHasKey('10.1.0', $colors);
        $this->assertContains($field, $colors['10.1.0'], "Color field not registered: {$field}");
    }

    /** @return array<string, array{string}> */
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

    public function testPathConversionHasBibleStudyToProclaimMapping(): void
    {
        $h    = makeMigHelper([]);
        $ref  = new \ReflectionClass(CwmtemplatemigrationHelper::class);
        $prop = $ref->getProperty('pathConversions');
        $prop->setAccessible(true);
        $paths = $prop->getValue($h);

        $this->assertArrayHasKey('10.1.0', $paths);
        $this->assertArrayHasKey('media/com_biblestudy/', $paths['10.1.0']);
        $this->assertSame('media/com_proclaim/', $paths['10.1.0']['media/com_biblestudy/']);
    }

    // =========================================================================
    // Section 3 – apply-if-missing logic
    // =========================================================================

    public function testApplyParamsAddsDefaultToEmptyTemplate(): void
    {
        $h = makeMigHelper(['{}']);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('pagination_style', $params);
        $this->assertSame('pagination', $params['pagination_style']);
    }

    public function testApplyParamsDoesNotOverwriteExistingValue(): void
    {
        $h = makeMigHelper([['pagination_style' => 'loadmore']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertSame('loadmore', $params['pagination_style'] ?? 'pagination', 'Custom value must not be overwritten');
    }

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

    public function testRenameTransfersValueToNewName(): void
    {
        $h = makeMigHelper([['teacher_id' => '42']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertArrayHasKey('lteacher_id', $params, 'New param name must exist after rename');
        $this->assertSame('42', (string) $params['lteacher_id'], 'Value must be preserved during rename');
        $this->assertArrayNotHasKey('teacher_id', $params, 'Old param name must be removed');
    }

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

    public function testRenameDoesNotTouchAlreadyNewStyleParam(): void
    {
        $h = makeMigHelper([['lteacher_id' => '7']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);
        $this->assertSame('7', (string) ($params['lteacher_id'] ?? ''), 'Already-new-style value must be preserved');
        $this->assertArrayNotHasKey('teacher_id', $params, 'Old name must not appear after migration');
    }

    // =========================================================================
    // Section 5 – color conversion
    // =========================================================================

    public function testColorConversionConverts0xUppercaseToHash(): void
    {
        $h = makeMigHelper([['backcolor' => '0xFF0000']]);
        $h->migrateFromVersion('0.0.0');

        $this->assertSame('#FF0000', $h->getParamsArray(1)['backcolor'] ?? null);
    }

    public function testColorConversionConverts0xLowercaseToHashUppercase(): void
    {
        $h = makeMigHelper([['frontcolor' => '0xaabbcc']]);
        $h->migrateFromVersion('0.0.0');

        $this->assertSame('#AABBCC', $h->getParamsArray(1)['frontcolor'] ?? null, '0xaabbcc must become #AABBCC');
    }

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

    public function testColorConversionDoesNotTouchValidHashFormat(): void
    {
        $h = makeMigHelper([['backcolor' => '#FF0000']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);

        if (isset($params['backcolor'])) {
            $this->assertSame('#FF0000', $params['backcolor'], '# format must not be altered');
        }

        $this->assertTrue(true); // Test passes even if no color update written
    }

    public function testColorConversionDoesNotTouchEmptyValue(): void
    {
        $h = makeMigHelper([['backcolor' => '']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);

        if (\array_key_exists('backcolor', $params)) {
            $this->assertSame('', $params['backcolor'], 'Empty color value must not be changed');
        }

        $this->assertTrue(true);
    }

    // =========================================================================
    // Section 6 – path conversion
    // =========================================================================

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

    public function testPathConversionDoesNotAlterCurrentPath(): void
    {
        $h = makeMigHelper([['custom_icon' => 'media/com_proclaim/images/icon.png']]);
        $h->migrateFromVersion('0.0.0');

        $params = $h->getParamsArray(1);

        if (isset($params['custom_icon'])) {
            $this->assertSame('media/com_proclaim/images/icon.png', $params['custom_icon']);
        }

        $this->assertTrue(true);
    }

    // =========================================================================
    // Section 7 – rowspan image migration
    // =========================================================================

    /**
     * @dataProvider provideRowspanItemMap
     */
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

    /** @return array<string, array{int, string}> */
    public static function provideRowspanItemMap(): array
    {
        return [
            'rowspanitem=1 → teacherimage'      => [1, 'teacherimage'],
            'rowspanitem=2 → thumbnail'         => [2, 'thumbnail'],
            'rowspanitem=3 → seriesthumbnail'   => [3, 'seriesthumbnail'],
            'rowspanitem=4 → teacherlargeimage' => [4, 'teacherlargeimage'],
        ];
    }

    public function testRowspanItemZeroIsSkipped(): void
    {
        $h = makeMigHelper([['rowspanitem' => '0']]);

        $this->assertSame(0, $h->migrateRowspanImages(), 'rowspanitem=0 must not trigger migration');
        $this->assertEmpty($h->getSavedParams());
    }

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

    public function testRowspanItemSkipsElementAlreadyPlaced(): void
    {
        // thumbnail is already placed in row 2 — migration must not move it to row 1
        $h = makeMigHelper([[
            'rowspanitem'     => '2',   // thumbnail
            'rowspanitemspan' => '4',
            'thumbnailrow'    => '2',   // already placed!
        ]]);
        $h->migrateRowspanImages();

        $saved = $h->getSavedParams();

        foreach ($saved as $json) {
            $params = json_decode($json, true);

            if (isset($params['thumbnailrow'])) {
                $this->assertSame('2', (string) $params['thumbnailrow'], 'Already-placed element must not be moved');
            }
        }

        $this->assertTrue(true);
    }

    // =========================================================================
    // Section 8 – Public API
    // =========================================================================

    public function testGetMigrationsReturnsArray(): void
    {
        $this->assertIsArray(makeMigHelper([])->getMigrations());
    }

    public function testAddMigrationInsertsCustomVersionEntry(): void
    {
        $h = makeMigHelper([]);
        $h->addMigration('11.0.0', ['new_feature' => '0']);

        $migrations = $h->getMigrations();
        $this->assertArrayHasKey('11.0.0', $migrations);
        $this->assertSame('0', $migrations['11.0.0']['new_feature']);
    }

    public function testAddMigrationVersionFilteringWorks(): void
    {
        $h = makeMigHelper([]);
        $h->addMigration('11.0.0', ['future_feature' => '1']);

        // From 10.0.0: both 10.1.0 and 11.0.0 migrations must run
        $this->assertArrayHasKey('11.0.0', $h->callGetMigrationsAfterVersion('10.0.0'));

        // From 11.0.0: 11.0.0 must NOT run (already at that version)
        $this->assertArrayNotHasKey('11.0.0', $h->callGetMigrationsAfterVersion('11.0.0'));
    }

    public function testMigrateAllRunsAllMigrations(): void
    {
        $h     = makeMigHelper(['{}']);
        $count = $h->migrateAll();

        $this->assertGreaterThan(0, $count, 'migrateAll() must update at least one template');
        $this->assertNotEmpty($h->getSavedParams(), 'migrateAll() must produce at least one write');
    }

    public function testMigrateAllAndMigrateFromVersionZeroProduceSameResult(): void
    {
        $h1 = makeMigHelper(['{}']);
        $h2 = makeMigHelper(['{}']);

        $c1 = $h1->migrateFromVersion('0.0.0');
        $c2 = $h2->migrateAll();

        $this->assertSame($c1, $c2, 'migrateAll() and migrateFromVersion(0.0.0) must produce same update count');
    }
}

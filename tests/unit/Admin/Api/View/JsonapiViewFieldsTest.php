<?php

/**
 * Unit tests for the REST API JsonapiView field lists.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Api\View;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Field-exposure contract for the API's JSON views.
 *
 * ⚠️ A name in `$fieldsToRender*` that the row does not carry is dropped by
 * `JoomlaSerializer::getAttributes()` — `array_intersect_key()` against the
 * declared names — with no null, no notice and no error. So a view can declare
 * a field that has never once appeared in a response, and nothing says so.
 * #1749 found six such fields across four views, all shipped since 10.3.0.
 *
 * Two ways a declared field can fail to arrive, and a check for each:
 *
 * 1. It is not a column of the table the view reads. Nothing can ever supply
 *    it, on either route. Checked against the install schema.
 * 2. It is a real column, but the list query does not select it. The item route
 *    carries it (Table::load reads every column) and the list route does not.
 *    Checked against the controller's LIST_SELECT.
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonapiViewFieldsTest extends ProclaimTestCase
{
    /**
     * View directory => the table its model reads.
     *
     * A view with no entry fails the coverage test below rather than being
     * skipped, so a new resource cannot join the API unchecked.
     *
     * @var array<string, string>
     * @since __DEPLOY_VERSION__
     */
    private const VIEW_TABLES = [
        'Comments'     => 'bsms_comments',
        'Locations'    => 'bsms_locations',
        'Media'        => 'bsms_mediafiles',
        'Messagetypes' => 'bsms_message_type',
        'Playlists'    => 'bsms_playlists',
        'Podcasts'     => 'bsms_podcast',
        'Series'       => 'bsms_series',
        'Sermons'      => 'bsms_studies',
        'Servers'      => 'bsms_servers',
        'Teachers'     => 'bsms_teachers',
        'Templates'    => 'bsms_templates',
        'Topics'       => 'bsms_topics',

        // Info is a synthetic singleton — it reports the installed version and
        // reads no table at all.
        'Info' => '',
    ];

    /**
     * Declared names that come from a join rather than the view's own table.
     *
     * @var array<string, string[]>
     * @since __DEPLOY_VERSION__
     */
    private const JOIN_ALIASES = [
        // CwmmediafilesModel selects study.studytitle and server.server_name
        // under those aliases.
        'Media' => ['studytitle', 'server_name'],

        // CwmmessagesModel selects `series.id AS series_id` off the series
        // join, so the name arrives without list.select naming it.
        'Sermons' => ['series_id'],
    ];

    /**
     * Columns of every table in the install schema, keyed by table name
     * without the `#__` prefix.
     *
     * @return  array<string, string[]>
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function schemaColumns(): array
    {
        static $tables = null;

        if ($tables !== null) {
            return $tables;
        }

        $sql = file_get_contents(self::repoRoot() . '/admin/sql/install.mysql.utf8.sql');

        $tables = [];
        preg_match_all('/CREATE TABLE (?:IF NOT EXISTS )?`#__(\w+)`\s*\((.*?)\n\)\s*ENGINE/s', $sql, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            preg_match_all('/^\s+`(\w+)`\s+(?!KEY|PRIMARY)/mi', $match[2], $columns);
            $tables[$match[1]] = $columns[1];
        }

        return $tables;
    }

    /**
     * @return  string
     * @since   __DEPLOY_VERSION__
     */
    private static function repoRoot(): string
    {
        return \dirname(__DIR__, 5);
    }

    /**
     * Every JsonapiView on disk, by view directory name.
     *
     * @return  array<string, array{0: string, 1: class-string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function viewProvider(): array
    {
        $cases = [];

        foreach (glob(self::repoRoot() . '/api/src/View/*/JsonapiView.php') as $file) {
            $view         = basename(\dirname($file));
            $cases[$view] = [$view, 'CWM\\Component\\Proclaim\\Api\\View\\' . $view . '\\JsonapiView'];
        }

        return $cases;
    }

    /**
     * The declared field names of a view, by route.
     *
     * @param   class-string  $viewClass  View to read
     *
     * @return  array{item: string[], list: string[]}
     *
     * @since   __DEPLOY_VERSION__
     */
    private function declaredFields(string $viewClass): array
    {
        $ref = new \ReflectionClass($viewClass);

        return [
            'item' => $ref->getProperty('fieldsToRenderItem')->getDefaultValue(),
            'list' => $ref->getProperty('fieldsToRenderList')->getDefaultValue(),
        ];
    }

    #[DataProvider('viewProvider')]
    #[TestDox('$view declares only fields its table actually has')]
    public function testDeclaredFieldsAreRealColumns(string $view, string $viewClass): void
    {
        $this->assertArrayHasKey(
            $view,
            self::VIEW_TABLES,
            "The $view API view has no entry in VIEW_TABLES, so nothing checks what it exposes. Add the table it "
            . 'reads (or an empty string if it reads none).'
        );

        $table = self::VIEW_TABLES[$view];

        if ($table === '') {
            $this->assertTrue(true, "$view reads no table.");

            return;
        }

        $columns = self::schemaColumns();
        $this->assertArrayHasKey($table, $columns, "#__$table is not in the install schema.");

        $available = array_merge($columns[$table], self::JOIN_ALIASES[$view] ?? []);
        $fields    = $this->declaredFields($viewClass);

        foreach (['item', 'list'] as $route) {
            $phantom = array_values(array_diff($fields[$route], $available));

            $this->assertSame(
                [],
                $phantom,
                \sprintf(
                    '%s declares %s in its %s response, but #__%s has no such column and no join supplies it. '
                    . 'array_intersect_key() drops the name silently, so the field has never appeared in a '
                    . 'response — it is not a contract, it is a typo.',
                    $view,
                    implode(', ', $phantom),
                    $route,
                    $table
                )
            );
        }
    }

    /**
     * A controller's LIST_SELECT, or null when it declares none.
     *
     * ⚠️ Reflection, not `defined()`/`constant()`. Those respect visibility, so
     * for a `protected const` they report "not defined" from out here — which
     * turned the check below into a silent pass when it was first written.
     *
     * @param   string  $view  View directory name
     *
     * @return  string[]|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function listSelectFor(string $view): ?array
    {
        $controller = 'CWM\\Component\\Proclaim\\Api\\Controller\\' . $view . 'Controller';

        if (!class_exists($controller)) {
            return null;
        }

        $ref = new \ReflectionClass($controller);

        return $ref->hasConstant('LIST_SELECT') ? (array) $ref->getConstant('LIST_SELECT') : null;
    }

    #[DataProvider('viewProvider')]
    #[TestDox('$view list route selects everything it declares')]
    public function testListRouteSelectsWhatItDeclares(string $view, string $viewClass): void
    {
        $listSelect = self::listSelectFor($view);

        if ($listSelect === null) {
            // The model's own default select covers this view. Asserting that
            // here would mean re-deriving the model's query without a database,
            // which is what the e2e API suite does for real instead.
            $this->assertTrue(true, "$view uses its model's default select.");

            return;
        }

        $selected = array_map(
            static fn (string $column) => str_contains($column, '.')
                ? substr($column, strrpos($column, '.') + 1)
                : $column,
            $listSelect
        );

        $columns = self::schemaColumns()[self::VIEW_TABLES[$view]] ?? [];
        $unknown = array_values(array_diff($selected, $columns));

        $this->assertSame([], $unknown, "$view LIST_SELECT names columns that do not exist: " . implode(', ', $unknown));

        $missing = array_values(array_diff(
            $this->declaredFields($viewClass)['list'],
            $selected,
            self::JOIN_ALIASES[$view] ?? []
        ));

        $this->assertSame(
            [],
            $missing,
            \sprintf(
                '%s declares %s in its list response but LIST_SELECT does not select it. The item route would '
                . 'carry it and the list route would drop it — the harder half of #1749 to notice.',
                $view,
                implode(', ', $missing)
            )
        );
    }

    #[DataProvider('viewProvider')]
    #[TestDox('$view keeps heavy fields out of list responses')]
    public function testHeavyFieldsStayOutOfListResponses(string $view, string $viewClass): void
    {
        $fields = $this->declaredFields($viewClass);

        // Long-form bodies belong on the detail route only; a list of fifty of
        // them is a payload nobody asked for.
        foreach (['studytext', 'information', 'message'] as $heavy) {
            $this->assertNotContains($heavy, $fields['list'], "$view list view should NOT include $heavy");
        }
    }

    #[DataProvider('viewProvider')]
    #[TestDox('$view exposes no internal bookkeeping columns')]
    public function testSensitiveFieldsExcludedFromView(string $view, string $viewClass): void
    {
        $fields    = $this->declaredFields($viewClass);
        $allFields = array_unique(array_merge($fields['item'], $fields['list']));

        foreach (['asset_id', 'checked_out', 'checked_out_time', 'created_by', 'modified_by'] as $field) {
            $this->assertNotContains($field, $allFields, "$view must NOT expose $field");
        }
    }

    #[TestDox('the schema parser finds columns, so the checks above are not vacuous')]
    public function testTheSchemaParserWorks(): void
    {
        $columns = self::schemaColumns();

        $this->assertGreaterThan(20, \count($columns), 'The install schema parser found almost no tables.');

        // Spot-check the two names #1749 turned on: #__bsms_series has `teacher`
        // and not `teacher_id`, which is the whole of the original report.
        $this->assertContains('teacher', $columns['bsms_series']);
        $this->assertNotContains('teacher_id', $columns['bsms_series']);
        $this->assertContains('teacher_id', $columns['bsms_study_teachers']);
    }

    #[TestDox('the controllers that widen the list select are actually being read')]
    public function testTheListSelectCheckIsNotVacuous(): void
    {
        // Without this the previous test is a pass either way: a lookup that
        // silently finds nothing returns early on every view, and the check
        // that studyintro/teacher/podcastimage reach the list route stops
        // existing. That is exactly how it first shipped.
        foreach (['Series', 'Sermons', 'Podcasts'] as $view) {
            $this->assertNotNull(
                self::listSelectFor($view),
                "$view widens list.select in its controller, but the test cannot see LIST_SELECT."
            );
        }
    }

    #[TestDox('every JsonapiView on disk is covered by these checks')]
    public function testEveryViewIsCovered(): void
    {
        $found = array_keys(self::viewProvider());

        $this->assertNotEmpty($found, 'No JsonapiView files were found — the glob is wrong and nothing is checked.');
        $this->assertSame([], array_values(array_diff($found, array_keys(self::VIEW_TABLES))));
    }
}

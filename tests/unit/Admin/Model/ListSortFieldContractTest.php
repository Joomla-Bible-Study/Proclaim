<?php

/**
 * Contract test binding admin list templates to their models' filter_fields.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression test for #1654.
 *
 * `ListModel::populateState()` validates the incoming `filter_order` against the
 * model's `filter_fields` whitelist and, when the value is absent, discards it
 * and restores the default -- silently. A column header that submits a name the
 * model does not list therefore reloads the page without reordering it, with no
 * error anywhere to notice.
 *
 * Fourteen headers across six views were in that state, three distinct ways:
 * the `Cwm` class prefix leaking into a string that has to be a database alias
 * (`cwmteacher.teachername` against alias `teacher`), a plural/singular slip
 * (`messagetypes.` against `messagetype.`), and columns simply never added.
 *
 * Every one of them is invisible to a test that exercises the model alone,
 * because the wrong name only exists in the template. This test reads the two
 * sides and asserts they agree.
 *
 * @since  __DEPLOY_VERSION__
 */
class ListSortFieldContractTest extends ProclaimTestCase
{
    /**
     * Every admin list template that renders sortable column headers, paired
     * with the model whose whitelist has to accept them.
     *
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function listViewProvider(): array
    {
        $root  = \dirname(__DIR__, 4);
        $cases = [];

        foreach (glob($root . '/admin/tmpl/*/default.php') as $template) {
            $view  = basename(\dirname($template));
            $model = $root . '/admin/src/Model/' . ucfirst($view) . 'Model.php';

            // Not every tmpl directory is a list view backed by a plural model.
            if (!is_file($model) || !self::sortFields($template)) {
                continue;
            }

            $cases[$view] = [$template, $model];
        }

        return $cases;
    }

    /**
     * Extract the ordering column each searchtools.sort call submits.
     *
     * @param   string  $template  Absolute path to the template.
     *
     * @return  string[]
     */
    private static function sortFields(string $template): array
    {
        preg_match_all(
            '/[\'"]searchtools\.sort[\'"],\s*\n\s*[\'"][^\'"]*[\'"],\s*\n\s*[\'"]([^\'"]+)[\'"]/',
            (string) file_get_contents($template),
            $matches
        );

        // An empty name is the drag-handle column, which submits nothing.
        return array_values(array_filter(array_unique($matches[1]), static fn ($f) => $f !== ''));
    }

    /**
     * Read a model's declared filter_fields whitelist.
     *
     * Parsed from source rather than instantiated: constructing a ListModel
     * needs an application, a database and an MVCFactory, none of which this
     * assertion depends on.
     *
     * @param   string  $model  Absolute path to the model.
     *
     * @return  string[]
     */
    private static function filterFields(string $model): array
    {
        if (!preg_match('/filter_fields.*?=\s*\[(.*?)\];/s', (string) file_get_contents($model), $block)) {
            return [];
        }

        preg_match_all('/[\'"]([A-Za-z_.]*)[\'"]/', $block[1], $matches);

        return $matches[1];
    }

    #[DataProvider('listViewProvider')]
    #[TestDox('Every sortable column header submits a name its model accepts')]
    public function testSortHeadersAreWhitelisted(string $template, string $model): void
    {
        $emitted   = self::sortFields($template);
        $whitelist = self::filterFields($model);

        $this->assertNotEmpty($emitted, 'The provider only yields templates that render sort headers');

        $missing = array_values(array_diff($emitted, $whitelist));

        $this->assertSame(
            [],
            $missing,
            \sprintf(
                "%s submits ordering columns %s does not accept, so clicking those headers does nothing.\n"
                . 'Dotted names must match the query alias in getListQuery(), not the Cwm class name.',
                basename(\dirname($template)) . '/default.php',
                basename($model)
            )
        );
    }

    #[DataProvider('listViewProvider')]
    #[TestDox('A dotted whitelist entry names a real column, so a crafted filter_order cannot 500')]
    public function testWhitelistHasNoDanglingColumns(string $template, string $model): void
    {
        // Aliases as bound in each getListQuery(). A dotted entry outside this
        // map is left alone rather than guessed at.
        $tables = [
            'teacher'      => 'bsms_teachers',
            'server'       => 'bsms_servers',
            'messagetype'  => 'bsms_message_type',
            'comment'      => 'bsms_comments',
            'location'     => 'bsms_locations',
            'podcast'      => 'bsms_podcast',
            'study'        => 'bsms_studies',
            'series'       => 'bsms_series',
            'mediafile'    => 'bsms_mediafiles',
            'playlist'     => 'bsms_playlists',
            'topic'        => 'bsms_topics',
            'template'     => 'bsms_templates',
            'templatecode' => 'bsms_templatecode',
        ];

        $install = (string) file_get_contents(\dirname(__DIR__, 4) . '/admin/sql/install.mysql.utf8.sql');
        $checked = 0;

        foreach (self::filterFields($model) as $field) {
            if (!str_contains($field, '.')) {
                // Bare names drive getActiveFilters(), not ordering.
                continue;
            }

            [$alias, $column] = explode('.', $field, 2);

            if (!isset($tables[$alias])) {
                continue;
            }

            // Read the column list straight out of the shipped CREATE TABLE.
            if (!preg_match(
                '/CREATE TABLE[^`]*`#__' . preg_quote($tables[$alias], '/') . '`\s*\((.*?)\n\)/s',
                $install,
                $ddl
            )) {
                continue;
            }

            $this->assertMatchesRegularExpression(
                '/^\s*`' . preg_quote($column, '/') . '`/m',
                $ddl[1],
                \sprintf(
                    '%s whitelists "%s", but #__%s has no such column — ordering by it raises a '
                    . 'database error rather than being ignored.',
                    basename($model),
                    $field,
                    $tables[$alias]
                )
            );

            $checked++;
        }

        $this->assertGreaterThan(
            0,
            $checked,
            basename($model) . ' declares no resolvable dotted ordering columns — the assertion above never ran'
        );
    }
}

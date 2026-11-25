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
 * @since  10.5.6
 */
class ListSortFieldContractTest extends ProclaimTestCase
{
    /**
     * Every template in an admin list view that renders sort headers, its modal
     * included, paired with the model whose whitelist has to accept them.
     *
     * @return  array<string, array{0: string, 1: string}>  A sortable template's absolute path, then its model's, keyed
     *                                                      by view, and for a layout besides default by view/layout
     *
     * @since   10.5.6
     */
    public static function listViewProvider(): array
    {
        $root  = \dirname(__DIR__, 4);
        $cases = [];

        foreach (glob($root . '/admin/tmpl/*/*.php') as $template) {
            $view   = basename(\dirname($template));
            $layout = basename($template, '.php');
            $model  = $root . '/admin/src/Model/' . ucfirst($view) . 'Model.php';

            // Not every tmpl directory is a list view backed by a plural model.
            if (!is_file($model) || !self::sortColumns((string) file_get_contents($template))) {
                continue;
            }

            $cases[$layout === 'default' ? $view : $view . '/' . $layout] = [$template, $model];
        }

        return $cases;
    }

    /**
     * Extract the ordering column each searchtools.sort call submits.
     *
     * @param   string  $template  Absolute path to the template.
     *
     * @return  string[]  The distinct non-empty columns its searchtools.sort calls submit
     *
     * @since   10.5.6
     */
    private static function sortFields(string $template): array
    {
        // An empty name is the drag-handle column, which submits nothing.
        $fields = array_filter(
            self::sortColumns((string) file_get_contents($template)),
            static fn (?string $field): bool => $field !== null && $field !== ''
        );

        return array_values(array_unique($fields));
    }

    /**
     * The column argument of every searchtools.sort call in a template, read
     * with the tokenizer, so a call written on one line or with a computed label
     * counts too.
     *
     * @param   string  $source  The template's PHP source
     *
     * @return  array<int, string|null>  Each call's column in source order: a string literal's value, or null when
     *                                   the argument is anything else and so cannot be read
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function sortColumns(string $source): array
    {
        $tokens  = token_get_all($source);
        $count   = \count($tokens);
        $columns = [];

        foreach ($tokens as $i => $token) {
            if (!\is_array($token) || $token[0] !== T_CONSTANT_ENCAPSED_STRING || trim($token[1], '\'"') !== 'searchtools.sort') {
                continue;
            }

            // Split the rest of the call at its top-level commas: the label, then the column.
            $args  = [[]];
            $depth = 0;

            for ($j = $i + 1; $j < $count; $j++) {
                $text = \is_array($tokens[$j]) ? $tokens[$j][1] : $tokens[$j];

                if ($text === '(' || $text === '[') {
                    $depth++;
                } elseif ($text === ')' || $text === ']') {
                    if ($depth === 0) {
                        break;
                    }

                    $depth--;
                } elseif ($text === ',' && $depth === 0) {
                    $args[] = [];

                    continue;
                }

                if (!\is_array($tokens[$j]) || $tokens[$j][0] !== T_WHITESPACE) {
                    $args[\count($args) - 1][] = $tokens[$j];
                }
            }

            $column    = $args[2] ?? [];
            $columns[] = \count($column) === 1 && \is_array($column[0]) && $column[0][0] === T_CONSTANT_ENCAPSED_STRING
                ? substr($column[0][1], 1, -1)
                : null;
        }

        return $columns;
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
     * @return  string[]  Each name quoted in the model's filter_fields array, or empty without one
     *
     * @since   10.5.6
     */
    private static function filterFields(string $model): array
    {
        if (!preg_match('/filter_fields.*?=\s*\[(.*?)\];/s', (string) file_get_contents($model), $block)) {
            return [];
        }

        preg_match_all('/[\'"]([A-Za-z_.]*)[\'"]/', $block[1], $matches);

        return $matches[1];
    }

    /**
     * Every sortable column header names its column as a string literal, which
     * its model accepts.
     *
     * @param   string  $template  The list template's path
     * @param   string  $model     The path of the model whose whitelist must accept it
     *
     * @return  void
     *
     * @since   10.5.6
     */
    #[DataProvider('listViewProvider')]
    #[TestDox('Every sortable column header submits a name its model accepts')]
    public function testSortHeadersAreWhitelisted(string $template, string $model): void
    {
        $name = basename(\dirname($template)) . '/' . basename($template);

        $this->assertNotContains(
            null,
            self::sortColumns((string) file_get_contents($template)),
            $name . ' has a searchtools.sort call whose column is not a string literal, so this test cannot check it '
            . 'against the whitelist.'
        );

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
                $name,
                basename($model)
            )
        );
    }

    /**
     * The column is read from a one-line call, a call with one argument per
     * line, and a call with a computed label. A column that is not a string
     * literal reads as null.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The sort column is read from every way a call can be written')]
    public function testSortColumnsAreReadFromEveryCallShape(): void
    {
        $source = <<<'PHP'
            <th><?php echo HTMLHelper::_('searchtools.sort', 'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?></th>
            <th><?php echo HTMLHelper::_(
                'searchtools.sort',
                'JGLOBAL_TITLE',
                'a.title',
                $listDirn,
                $listOrder
            ); ?></th>
            <th><?php echo HTMLHelper::_(
                'searchtools.sort',
                'JBS_HEADING_DATE_' . strtoupper($column),
                'a.created',
                $listDirn,
                $listOrder
            ); ?></th>
            <th><?php echo HTMLHelper::_('searchtools.sort', '', $column, $listDirn, $listOrder); ?></th>
            PHP;

        $this->assertSame(['a.published', 'a.title', 'a.created', null], self::sortColumns($source));
    }

    /**
     * A dotted whitelist entry names a real column, so a crafted filter_order cannot
     * cause a 500.
     *
     * @param   string  $template  The list template's path, unused here
     * @param   string  $model     The path of the model whose whitelist must accept it
     *
     * @return  void
     *
     * @since   10.5.6
     */
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

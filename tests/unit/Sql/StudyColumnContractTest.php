<?php

/**
 * Contract tests binding message template reads to the shipped study schema
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Unit\Sql;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Every `$this->item->x` in the message templates must name something the item
 * actually carries: a column of #__bsms_studies, or a property the model adds.
 *
 * The item is a plain stdClass built by `SELECT *`, so a property that no longer
 * exists reads as null and raises a PHP warning rather than failing. A template
 * therefore keeps rendering after the column behind it is retired — the field it
 * feeds simply goes empty, which looks like missing data rather than a defect.
 *
 * Retiring a column is a schema change with no compiler and no failing test to
 * connect it to the templates that read it, which is what this asserts instead.
 *
 * @since  __DEPLOY_VERSION__
 */
#[Group('sql')]
class StudyColumnContractTest extends ProclaimTestCase
{
    /**
     * The shipped schema, which is authoritative for what a study row holds.
     */
    private const INSTALL_SQL = JPATH_ROOT . '/admin/sql/install.mysql.utf8.sql';

    /**
     * Templates whose `$this->item` is a study row.
     */
    private const TEMPLATE_DIR = JPATH_ROOT . '/admin/tmpl/cwmmessage';

    /**
     * Properties CwmmessageModel::getItem() attaches to the row after loading it.
     *
     * These are subform payloads assembled from junction tables, so they are
     * legitimately absent from #__bsms_studies. Anything else must be a column.
     */
    private const MODEL_ADDED = [
        'scriptures',
        'teachers',
    ];

    /**
     * One case per distinct property read across the message templates.
     *
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function itemPropertyProvider(): array
    {
        $cases = [];

        foreach (glob(self::TEMPLATE_DIR . '/*.php') ?: [] as $file) {
            $source = (string) file_get_contents($file);

            preg_match_all('/\$this->item->([a-zA-Z_][a-zA-Z0-9_]*)/', $source, $matches);

            foreach (array_unique($matches[1]) as $property) {
                $cases[basename($file) . ': ' . $property] = [basename($file), $property];
            }
        }

        return $cases;
    }

    /**
     * Column names declared for #__bsms_studies by the install schema.
     *
     * @return  string[]
     */
    private static function studyColumns(): array
    {
        $sql = (string) file_get_contents(self::INSTALL_SQL);

        if (!preg_match('/CREATE TABLE[^`]*`#__bsms_studies`\s*\((.*?)\n\)\s*ENGINE/s', $sql, $table)) {
            self::fail('Could not find the CREATE TABLE block for #__bsms_studies in ' . self::INSTALL_SQL);
        }

        // Column definitions open with a backticked name; KEY and CONSTRAINT lines do not.
        preg_match_all('/^\s*`([a-z0-9_]+)`\s+/mi', $table[1], $columns);

        return $columns[1];
    }

    /**
     * A parser that silently matched nothing would pass every other case here.
     */
    #[TestDox('the schema and the templates both actually parse')]
    public function testTheInputsAreNotEmpty(): void
    {
        self::assertNotEmpty(
            self::studyColumns(),
            'No columns were parsed out of the install schema, so the contract below checks nothing.'
        );
        self::assertNotEmpty(
            self::itemPropertyProvider(),
            'No item reads were found in ' . self::TEMPLATE_DIR . ', so the contract below checks nothing.'
        );
    }

    #[DataProvider('itemPropertyProvider')]
    #[TestDox('$file reads $property, which the item carries')]
    public function testItemReadsNameSomethingTheItemCarries(string $file, string $property): void
    {
        if (\in_array($property, self::MODEL_ADDED, true)) {
            self::assertTrue(true, $property . ' is attached by the model rather than selected.');

            return;
        }

        self::assertContains(
            $property,
            self::studyColumns(),
            $file . ' reads $this->item->' . $property . ', which is neither a column of #__bsms_studies nor '
            . 'listed in MODEL_ADDED. On a live site that reads as null and warns, and the field it fills '
            . 'renders empty. Either the column was retired and this read needs a new source, or the model '
            . 'now attaches the property and it belongs in MODEL_ADDED.'
        );
    }
}

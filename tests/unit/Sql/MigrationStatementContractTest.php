<?php

/**
 * Contract tests for the shape of Proclaim's schema migration SQL
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

/**
 * Guards migration SQL against the shapes Joomla's schema checker cannot read.
 *
 * Joomla runs update SQL twice over a database's life, by two different
 * mechanisms. On install and update, Installer::parseSchemaUpdates() executes
 * each statement raw. On a restore, Cwmrestore clears #__schemas and
 * DatabaseModel::fix() replays everything through ChangeSet, which does not
 * execute a statement until MysqlChangeItem has parsed it into a check query and
 * that check has reported the change missing.
 *
 * That parser is far more literal than it looks. MysqlChangeItem::buildCheckQuery()
 * decides what an ALTER does from words three and four of the statement and
 * nothing else, so a compound ALTER is represented by its first clause alone.
 * Every later clause is then invisible: never verified, never repaired, and
 * silently counted as applied whenever the first clause happens to be in place.
 *
 * Nothing reports this. The migration runs correctly on the ordinary upgrade
 * path, System > Database shows a healthy schema, and the omission only surfaces
 * as a missing column or index long afterwards. #1664 found three such statements
 * in 10.1.0 — one hiding three podcast columns behind a fourth — which is why the
 * rule is asserted here rather than left to review.
 *
 * @since  __DEPLOY_VERSION__
 */
#[Group('sql')]
class MigrationStatementContractTest extends ProclaimTestCase
{
    /**
     * Directory holding the MySQL schema updates.
     */
    private const UPDATES_DIR = JPATH_ROOT . '/admin/sql/updates/mysql';

    /**
     * Every ALTER TABLE in the migration set, one case per statement.
     *
     * @return  array<string, array{0: string, 1: string}>
     */
    public static function alterStatementProvider(): array
    {
        $cases = [];

        foreach (glob(self::UPDATES_DIR . '/*.sql') ?: [] as $file) {
            $name  = basename($file);
            $index = 0;

            foreach (self::statementsIn((string) file_get_contents($file)) as $statement) {
                if (stripos($statement, 'ALTER TABLE') !== 0) {
                    continue;
                }

                $cases[$name . ' #' . ++$index] = [$name, $statement];
            }
        }

        // An empty provider would make this suite pass by doing nothing.
        self::assertNotSame([], $cases, 'No ALTER TABLE statements found — is the updates directory still there?');

        return $cases;
    }

    /**
     * An ALTER may carry exactly one clause, because Joomla only ever checks the
     * first one.
     *
     * @param   string  $file       Migration file the statement came from.
     * @param   string  $statement  The normalised statement.
     *
     * @return  void
     */
    #[DataProvider('alterStatementProvider')]
    public function testAlterStatementsCarryOneClause(string $file, string $statement): void
    {
        $clauses = self::topLevelClauses($statement);

        $this->assertSame(
            1,
            $clauses,
            "{$file} has an ALTER TABLE with {$clauses} clauses. Joomla builds its check query from "
            . "words 3-4 of the statement, so only the first is ever verified or repaired — split it "
            . "into one ALTER per clause.\n\n  " . $statement
        );
    }

    /**
     * MySQL has no DROP INDEX IF EXISTS, and ChangeSet reads a DROP INDEX as
     * "this index must be absent" — which is false the moment a later statement
     * re-adds the same name, leaving the check permanently unsatisfied and the
     * fix re-dropping a key the schema needs. Guarded PHP in proclaim.script.php
     * is where a conditional drop belongs.
     *
     * @param   string  $file       Migration file the statement came from.
     * @param   string  $statement  The normalised statement.
     *
     * @return  void
     */
    #[DataProvider('alterStatementProvider')]
    public function testNoUnguardableIndexDrops(string $file, string $statement): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/\bDROP\s+(INDEX|KEY)\b/i',
            $statement,
            "{$file} drops an index in migration SQL. That aborts the update with error 1091 on any "
            . "database where the index is already absent, and cannot be made conditional in MySQL. "
            . "Do it in proclaim.script.php behind a SHOW INDEX check instead.\n\n  " . $statement
        );
    }

    /**
     * Split a file into normalised statements.
     *
     * Comment lines go first so a semicolon inside one cannot split a statement,
     * and whitespace is collapsed so word positions match how MysqlChangeItem
     * tokenises.
     *
     * @param   string  $sql  File contents.
     *
     * @return  list<string>
     */
    private static function statementsIn(string $sql): array
    {
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? '';

        $statements = [];

        foreach (explode(';', $sql) as $statement) {
            $statement = trim(preg_replace('/\s+/', ' ', $statement) ?? '');

            if ($statement !== '') {
                $statements[] = $statement;
            }
        }

        return $statements;
    }

    /**
     * Count an ALTER's top-level clauses.
     *
     * Only a comma outside both quotes and parentheses separates clauses. A comma
     * inside COMMENT 'a, b' or inside an index's column list does not — reading
     * those as separators is how a first pass at this check reported two
     * single-clause statements as compound.
     *
     * @param   string  $statement  The normalised statement.
     *
     * @return  int
     */
    private static function topLevelClauses(string $statement): int
    {
        $clauses = 1;
        $depth   = 0;
        $quote   = null;

        for ($i = 0, $n = \strlen($statement); $i < $n; $i++) {
            $char = $statement[$i];

            if ($quote !== null) {
                if ($char === '\\') {
                    $i++;
                } elseif ($char === $quote) {
                    $quote = null;
                }

                continue;
            }

            if ($char === "'" || $char === '"') {
                $quote = $char;
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')') {
                $depth--;
            } elseif ($char === ',' && $depth === 0) {
                $clauses++;
            }
        }

        return $clauses;
    }
}

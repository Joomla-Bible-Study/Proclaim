<?php

/**
 * The two things a restore has to work out about a dump it did not write:
 * which statements it is allowed to run, and what to do about columns MySQL
 * will not let it write to.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmrestore;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmrestoreImportTest extends ProclaimTestCase
{
    /**
     * A mysqldump of #__bsms_studies, trimmed to the parts that matter: a
     * STORED generated column, a UNIQUE index spanning it, and ordinary
     * columns and indexes either side that must survive untouched.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private const string STUDIES = <<<'SQL'
CREATE TABLE `#__bsms_studies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `series_id` int(10) NOT NULL DEFAULT 0,
  `studynumber` varchar(100) NOT NULL DEFAULT '',
  `studynumber_uk` varchar(100) GENERATED ALWAYS AS (if(`series_id` > 0 and `studynumber` <> '',`studynumber`,NULL)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_series_studynumber` (`series_id`,`studynumber_uk`),
  KEY `idx_seriesid` (`series_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
SQL;

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A generated column is demoted so the dump\'s own INSERT is accepted')]
    public function testGeneratedColumnIsDemoted(): void
    {
        $result = Cwmrestore::normaliseGeneratedColumns(self::STUDIES);

        $this->assertStringNotContainsString(
            'GENERATED ALWAYS',
            $result['sql'],
            'The CREATE TABLE still declares a generated column, so the INSERT that names it will be rejected.'
        );

        $this->assertStringContainsString(
            '`studynumber_uk` varchar(100) DEFAULT NULL',
            $result['sql'],
            'The column has to remain, as an ordinary one — dropping it makes the INSERT fail as an unknown column instead.'
        );

        // Everything else is left alone.
        $this->assertStringContainsString('`series_id` int(10) NOT NULL DEFAULT 0', $result['sql']);
        $this->assertStringContainsString('PRIMARY KEY (`id`)', $result['sql']);
        $this->assertStringContainsString('KEY `idx_seriesid` (`series_id`)', $result['sql']);
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('An index spanning the generated column stays put for the load')]
    public function testSpanningIndexSurvivesTheLoad(): void
    {
        $result = Cwmrestore::normaliseGeneratedColumns(self::STUDIES);

        $this->assertStringContainsString(
            'UNIQUE KEY `uq_series_studynumber` (`series_id`,`studynumber_uk`)',
            $result['sql'],
            'A demoted column indexes exactly as the generated one did, and carries the same values. '
            . 'Taking the index out of the CREATE TABLE only makes the restore\'s own DROP INDEX fail.'
        );
    }

    /**
     * The order is the whole point. Dropping the column first would leave
     * UNIQUE (series_id) standing on its own, which no site with two messages
     * in one series can satisfy.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The restore statements drop the index before the column and rebuild in reverse')]
    public function testRestoreStatementOrder(): void
    {
        $restore = Cwmrestore::normaliseGeneratedColumns(self::STUDIES)['restore'];

        $this->assertCount(4, $restore, 'Expected drop index, drop column, add column, add index.');

        $positions = [];

        foreach ($restore as $i => $statement) {
            foreach (['DROP INDEX', 'DROP COLUMN', 'ADD COLUMN', 'ADD UNIQUE KEY'] as $needle) {
                if (str_contains($statement, $needle)) {
                    $positions[$needle] = $i;
                }
            }
        }

        $this->assertSame(
            ['DROP INDEX', 'DROP COLUMN', 'ADD COLUMN', 'ADD UNIQUE KEY'],
            array_keys($positions),
            'All four statements must be present, in this order.'
        );

        $this->assertLessThan($positions['DROP COLUMN'], $positions['DROP INDEX']);
        $this->assertLessThan($positions['ADD UNIQUE KEY'], $positions['ADD COLUMN']);
    }

    /**
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The rebuilt column carries the original expression')]
    public function testRestoredExpressionMatches(): void
    {
        $restore = Cwmrestore::normaliseGeneratedColumns(self::STUDIES)['restore'];
        $addCol  = '';

        foreach ($restore as $statement) {
            if (str_contains($statement, 'ADD COLUMN')) {
                $addCol = $statement;
            }
        }

        $this->assertStringContainsString('GENERATED ALWAYS AS', $addCol);
        $this->assertStringContainsString('STORED', $addCol);
        $this->assertStringContainsString("if(`series_id` > 0 and `studynumber` <> '',`studynumber`,NULL)", $addCol);
        $this->assertStringContainsString('`uq_series_studynumber`', implode("\n", $restore));
    }

    /**
     * Cwmbackup already leaves generated columns out of its own exports, so
     * the common case is a dump with nothing to rewrite. It must come back
     * byte for byte rather than lightly reformatted.
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A dump without generated columns is returned untouched')]
    public function testDumpWithoutGeneratedColumnsIsUnchanged(): void
    {
        $plain = "CREATE TABLE `#__bsms_topics` (\n"
            . "  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,\n"
            . "  `topic_text` varchar(255) NOT NULL DEFAULT '',\n"
            . "  PRIMARY KEY (`id`)\n"
            . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $result = Cwmrestore::normaliseGeneratedColumns($plain);

        $this->assertSame($plain, $result['sql']);
        $this->assertSame([], $result['restore']);
    }

    /**
     * Everything the core splitter treats specially, so a port that got any of
     * it wrong shows up here rather than as a corrupted import.
     *
     * @return  array<string, array{0: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function splitProvider(): array
    {
        return [
            'semicolon inside a string'       => ["INSERT INTO `a` VALUES ('x; y');\nINSERT INTO `b` VALUES (2);"],
            'backslash-escaped quote'         => ["INSERT INTO `a` VALUES ('it\\'s; fine');\nSELECT 1;"],
            'doubled quote'                   => ["INSERT INTO `a` VALUES ('it''s; fine');\nSELECT 1;"],
            'run of backslashes'              => ["INSERT INTO `a` VALUES ('c:\\\\\\\\');\nSELECT 1;"],
            'double-quoted string'            => ["INSERT INTO `a` VALUES (\"x; y\");\nSELECT 1;"],
            'line comment'                    => ["-- a; comment\nSELECT 1;\n-- another;\nSELECT 2;"],
            'hash comment'                    => ["# a; comment\nSELECT 1;"],
            'the #__ prefix is not a comment' => ["INSERT INTO `#__x` VALUES (1);\nSELECT 2;"],
            'block comment'                   => ["/* a; b */ SELECT 1;\n/*!40101 SET x=1 */;\nSELECT 2;"],
            'versioned comment runs'          => ["/*!40000 ALTER TABLE `a` DISABLE KEYS */;\nSELECT 1;"],
            'no trailing semicolon'           => ['SELECT 1'],
            'empty input'                     => [''],
            'only whitespace'                 => ["\n\n  \n"],
        ];
    }

    /**
     * @param   string  $sql  Input to split both ways
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('The fast splitter returns exactly what the core splitter returns')]
    #[\PHPUnit\Framework\Attributes\DataProvider('splitProvider')]
    public function testSplitMatchesCore(string $sql): void
    {
        $this->assertSame(
            \Joomla\Database\DatabaseDriver::splitSql($sql),
            Cwmrestore::splitSqlFast($sql),
            'splitSqlFast() is a port of DatabaseDriver::splitSql(). A statement split in a '
            . 'different place would corrupt an import silently, so the two must not diverge.'
        );
    }

    /**
     * @return  array<string, array{0: string, 1: string}>
     *
     * @since __DEPLOY_VERSION__
     */
    public static function statementProvider(): array
    {
        return [
            'insert into a Proclaim table' => [
                'INSERT INTO `#__bsms_studies` (`id`) VALUES (1)',
                Cwmrestore::STATEMENT_ALLOWED,
            ],
            'create of a Proclaim table' => [
                'CREATE TABLE `#__bsms_teachers` (`id` int)',
                Cwmrestore::STATEMENT_ALLOWED,
            ],
            'a core table the export legitimately writes' => [
                'INSERT INTO `#__assets` (`id`) VALUES (1)',
                Cwmrestore::STATEMENT_ALLOWED,
            ],
            'downloaded bible verses are never restored' => [
                'DROP TABLE IF EXISTS `#__bsms_bible_verses`',
                Cwmrestore::STATEMENT_PRESERVED,
            ],
            'nor is the translation catalogue that travels with them' => [
                'INSERT INTO `#__bsms_bible_translations` (`id`) VALUES (1)',
                Cwmrestore::STATEMENT_PRESERVED,
            ],
            'dump preamble names no table' => [
                'SET NAMES utf8mb4',
                Cwmrestore::STATEMENT_SESSION,
            ],
            'a wrapped session statement is preamble too' => [
                '/*!40101 SET character_set_client = utf8mb4 */',
                Cwmrestore::STATEMENT_SESSION,
            ],
            'a table belonging to another extension' => [
                'DROP TABLE `#__users`',
                Cwmrestore::STATEMENT_REJECTED,
            ],
        ];
    }

    /**
     * @param   string  $statement  The statement to classify
     * @param   string  $expected   The verdict it should get
     *
     * @return  void
     *
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Statements are classified for what the restore should do with them')]
    #[\PHPUnit\Framework\Attributes\DataProvider('statementProvider')]
    public function testStatementClassification(string $statement, string $expected): void
    {
        $this->assertSame($expected, Cwmrestore::classifyStatement($statement));
    }
}

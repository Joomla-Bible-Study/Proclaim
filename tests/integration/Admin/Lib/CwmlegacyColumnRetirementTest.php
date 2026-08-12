<?php

/**
 * The 9.x upgrade path: flat columns full, junction empty, nothing in
 * sql/updates ever replayed.
 *
 * A site coming from 9.x takes the INSTALL route, so the migration files never
 * run top to bottom for it. The retirement therefore lives in PHP, and this
 * rebuilds the shape a 9.x database is actually in and runs it.
 *
 * ⚠️ Why it cannot live in migration SQL. Joomla's ChangeSet builds a
 * verification check only for ALTER TABLE, CREATE TABLE and RENAME TABLE. An
 * INSERT has none, so it is skipped and never replayed, while every DROP COLUMN
 * beside it is checked and is replayed. Database Maintenance -> Fix, and
 * CwmupgradeHelper::runSchemaMigration(), would run the drops without the
 * backfill and destroy every reference (#1623).
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\CwmscriptureMigration;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CwmscriptureMigration::class)]
class CwmlegacyColumnRetirementTest extends IntegrationTestCase
{
    /**
     * The columns a 9.x #__bsms_studies carries, as it declared them.
     *
     * @var array<string, string>
     * @since __DEPLOY_VERSION__
     */
    private const LEGACY_COLUMNS = [
        'booknumber'     => "INT(3) DEFAULT '101'",
        'chapter_begin'  => "INT(3) DEFAULT '1'",
        'verse_begin'    => "INT(3) DEFAULT '1'",
        'chapter_end'    => "INT(3) DEFAULT '1'",
        'verse_end'      => "INT(3) DEFAULT '1'",
        'bible_version'  => 'VARCHAR(20) DEFAULT NULL',
        'booknumber2'    => 'VARCHAR(4) DEFAULT NULL',
        'chapter_begin2' => 'VARCHAR(4) DEFAULT NULL',
        'verse_begin2'   => 'VARCHAR(4) DEFAULT NULL',
        'chapter_end2'   => 'VARCHAR(4) DEFAULT NULL',
        'verse_end2'     => 'VARCHAR(4) DEFAULT NULL',
        'bible_version2' => 'VARCHAR(20) DEFAULT NULL',
    ];

    /**
     * @var DatabaseDriver|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var int[]
     * @since __DEPLOY_VERSION__
     */
    private array $studyIds = [];

    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private string $sqlMode = '';

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);

        // ⚠️ Force strict mode. A real site upgrading from 9.x is very likely
        // to have STRICT_TRANS_TABLES on, where CAST('' AS SIGNED) raises
        // "Truncated incorrect INTEGER value" and aborts the upgrade. Neither
        // this machine nor CI runs strict by default, so without this the
        // blank-chapter test passes whether the casts are guarded or not.
        $this->sqlMode = (string) $this->db->setQuery('SELECT @@SESSION.sql_mode')->loadResult();
        $this->db->setQuery(
            "SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',STRICT_TRANS_TABLES')"
        )->execute();

        // ⚠️ No transaction. ALTER TABLE commits implicitly in MySQL, so a
        // rollback would not undo any of this; tearDown cleans up by hand.
        $this->addLegacyColumns();
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        foreach ($this->studyIds as $id) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__bsms_study_scriptures')
                . ' WHERE ' . $this->db->quoteName('study_id') . ' = ' . $id
            )->execute();
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__bsms_studies')
                . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
            )->execute();
        }

        $this->dropLegacyColumns();

        $this->db->setQuery('SET SESSION sql_mode = ' . $this->db->quote($this->sqlMode))->execute();

        parent::tearDown();
    }

    /**
     * Put the 9.x columns back on the table.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    private function addLegacyColumns(): void
    {
        foreach (self::LEGACY_COLUMNS as $column => $definition) {
            if ($this->columnExists($column)) {
                continue;
            }

            $this->db->setQuery(
                'ALTER TABLE ' . $this->db->quoteName('#__bsms_studies')
                . ' ADD COLUMN ' . $this->db->quoteName($column) . ' ' . $definition
            )->execute();
        }
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    private function dropLegacyColumns(): void
    {
        foreach (array_keys(self::LEGACY_COLUMNS) as $column) {
            if (!$this->columnExists($column)) {
                continue;
            }

            $this->db->setQuery(
                'ALTER TABLE ' . $this->db->quoteName('#__bsms_studies')
                . ' DROP COLUMN ' . $this->db->quoteName($column)
            )->execute();
        }
    }

    /**
     * @param   string  $column  Column name
     *
     * @return  bool
     * @since __DEPLOY_VERSION__
     */
    private function columnExists(string $column): bool
    {
        $this->db->setQuery(
            'SHOW COLUMNS FROM ' . $this->db->quoteName('#__bsms_studies')
            . ' LIKE ' . $this->db->quote($column)
        );

        return $this->db->loadRow() !== null;
    }

    /**
     * A study as 9.x stored it: everything in the flat columns, nothing in the
     * junction.
     *
     * @param   array<string, string|int>  $values  Legacy column values
     *
     * @return  int  The new study's primary key
     *
     * @since __DEPLOY_VERSION__
     */
    private function legacyStudy(array $values): int
    {
        $this->db->setQuery(
            'INSERT INTO ' . $this->db->quoteName('#__bsms_studies')
            . ' (' . $this->db->quoteName('studytitle') . ', ' . $this->db->quoteName('language') . ', '
            . $this->db->quoteName('published') . ') VALUES ('
            . $this->db->quote('cwm1623 9.x fixture') . ', ' . $this->db->quote('*') . ', 0)'
        )->execute();

        $id               = (int) $this->db->insertid();
        $this->studyIds[] = $id;

        $set = [];

        foreach ($values as $column => $value) {
            $set[] = $this->db->quoteName($column) . ' = ' . $this->db->quote((string) $value);
        }

        $this->db->setQuery(
            'UPDATE ' . $this->db->quoteName('#__bsms_studies') . ' SET ' . implode(', ', $set)
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
        )->execute();

        return $id;
    }

    /**
     * @param   int  $studyId  Study primary key
     *
     * @return  array<int, array{int, int}>  [booknumber, chapter_begin] by ordering
     *
     * @since __DEPLOY_VERSION__
     */
    private function junction(int $studyId): array
    {
        $rows = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName(['ordering', 'booknumber', 'chapter_begin']))
                ->from($this->db->quoteName('#__bsms_study_scriptures'))
                ->where($this->db->quoteName('study_id') . ' = ' . $studyId)
                ->order($this->db->quoteName('ordering'))
        )->loadObjectList() ?: [];

        $out = [];

        foreach ($rows as $row) {
            $out[(int) $row->ordering] = [(int) $row->booknumber, (int) $row->chapter_begin];
        }

        return $out;
    }

    #[TestDox('a 9.x study keeps both references, and the columns go')]
    public function testBothReferencesSurviveTheRetirement(): void
    {
        $id = $this->legacyStudy([
            'booknumber'     => 143,
            'chapter_begin'  => 3,
            'verse_begin'    => 16,
            'chapter_end'    => 3,
            'verse_end'      => 16,
            'booknumber2'    => '145',
            'chapter_begin2' => '8',
            'verse_begin2'   => '1',
            'chapter_end2'   => '8',
            'verse_end2'     => '4',
        ]);

        $this->assertSame([], $this->junction($id), 'A 9.x study starts with nothing in the junction.');

        CwmscriptureMigration::retireLegacyColumns();

        $this->assertSame(
            [0 => [143, 3], 1 => [145, 8]],
            $this->junction($id),
            'Both references must survive. The 9.x path never replays sql/updates, so if the retirement '
            . 'does not move them here nothing else will.'
        );
        $this->assertFalse($this->columnExists('booknumber'), 'The columns should be gone afterwards.');
    }

    #[TestDox("the sentinel values 9.x really stores do not abort the retirement")]
    public function testSentinelSecondaryValuesAreTolerated(): void
    {
        // ⚠️ These are the real contents of a 9.x row with no second reference.
        // CAST('' AS SIGNED) raises "Truncated incorrect INTEGER value" under
        // STRICT_TRANS_TABLES, which would take the whole upgrade down.
        $id = $this->legacyStudy([
            'booknumber'     => 151,
            'chapter_begin'  => 3,
            'verse_begin'    => 5,
            'chapter_end'    => 3,
            'verse_end'      => 11,
            'booknumber2'    => '',
            'chapter_begin2' => '',
            'verse_begin2'   => '',
            'chapter_end2'   => '',
            'verse_end2'     => '',
        ]);

        CwmscriptureMigration::retireLegacyColumns();

        $this->assertSame(
            [0 => [151, 3]],
            $this->junction($id),
            'The primary reference survives, and an empty booknumber2 does not become a second one. '
            . 'This is the row shape that raises "Truncated incorrect INTEGER value" if the WHERE casts '
            . 'it without a REGEXP guard.'
        );
    }

    #[TestDox('a real second reference with blank chapter fields still migrates')]
    public function testBlankChapterFieldsBesideARealSecondBook(): void
    {
        // ⚠️ The case the -1 fixture never reaches: booknumber2 IS numeric, so
        // the row is not filtered out, and the blank chapter columns beside it
        // are read. CAST('' AS SIGNED) raises "Truncated incorrect INTEGER
        // value" under STRICT_TRANS_TABLES and aborts the whole upgrade; without
        // strict mode it silently yields 0. Asserting the value covers both,
        // since CI and a developer machine do not agree on sql_mode.
        $id = $this->legacyStudy([
            'booknumber'     => 143,
            'chapter_begin'  => 1,
            'booknumber2'    => '145',
            'chapter_begin2' => '',
            'verse_begin2'   => '',
            'chapter_end2'   => '',
            'verse_end2'     => '',
        ]);

        CwmscriptureMigration::retireLegacyColumns();

        $this->assertSame(
            [0 => [143, 1], 1 => [145, 0]],
            $this->junction($id),
            'The second reference must arrive with chapter 0 rather than taking the upgrade down.'
        );
    }

    #[TestDox('a study already in the junction is not duplicated')]
    public function testAlreadyMigratedStudiesAreLeftAlone(): void
    {
        $id = $this->legacyStudy(['booknumber' => 160, 'chapter_begin' => 1]);

        $row = (object) [
            'study_id'       => $id,
            'ordering'       => 0,
            'booknumber'     => 160,
            'chapter_begin'  => 1,
            'verse_begin'    => 0,
            'chapter_end'    => 1,
            'verse_end'      => 0,
            'bible_version'  => '',
            'reference_text' => 'already here',
        ];
        $this->db->insertObject('#__bsms_study_scriptures', $row);

        CwmscriptureMigration::retireLegacyColumns();

        $this->assertCount(1, $this->junction($id), 'The backfill must skip studies the junction knows.');
    }

    #[TestDox('running it again once the columns are gone does nothing')]
    public function testItIsSafeToRunRepeatedly(): void
    {
        $id = $this->legacyStudy(['booknumber' => 161, 'chapter_begin' => 2]);

        CwmscriptureMigration::retireLegacyColumns();
        $after = $this->junction($id);

        $this->assertSame(
            0,
            CwmscriptureMigration::retireLegacyColumns(),
            'Postflight, Backup, Restore and the control panel data fixes all call this.'
        );
        $this->assertSame($after, $this->junction($id));
    }
}

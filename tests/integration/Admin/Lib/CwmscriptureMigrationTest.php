<?php

/**
 * The scripture migration has to be safe to run more than once, and has to
 * still find work when the junction table is already partly populated.
 *
 * It used to guard on `SELECT COUNT(*) FROM #__bsms_study_scriptures > 0` and
 * report "already completed". That is table-wide, so the first study saved
 * through the editor switched the migration off for the whole site. Studies
 * written by the PreachIT and Sermon Speaker importers -- which populate the
 * flat columns and nothing else -- were then stranded with no way to repair
 * them, and an interrupted run could never resume (#1623).
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
class CwmscriptureMigrationTest extends IntegrationTestCase
{
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
        $this->db->transactionStart(true);
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        try {
            $this->db?->transactionRollback(true);
        } catch (\Throwable) {
            // Connection lost; nothing to roll back.
        }

        parent::tearDown();
    }

    /**
     * A study carrying legacy flat values and no junction rows.
     *
     * @param   int       $book   Proclaim book number for the primary reference
     * @param   int|null  $book2  Book number for the secondary reference, if any
     *
     * @return  int  The new study's primary key
     *
     * @since __DEPLOY_VERSION__
     */
    private function legacyStudy(int $book = 101, ?int $book2 = null): int
    {
        $study = (object) [
            'published'      => 0,
            'studytitle'     => 'cwm1623 migration fixture',
            'booknumber'     => $book,
            'chapter_begin'  => 1,
            'verse_begin'    => 1,
            'chapter_end'    => 1,
            'verse_end'      => 5,
            'bible_version'  => 'kjv',
            'booknumber2'    => $book2,
            'chapter_begin2' => $book2 === null ? null : 2,
            'verse_begin2'   => $book2 === null ? null : 3,
            'chapter_end2'   => $book2 === null ? null : 2,
            'verse_end2'     => $book2 === null ? null : 9,
            'language'       => '*',
        ];

        $this->db->insertObject('#__bsms_studies', $study, 'id');

        $id               = (int) $this->db->insertid();
        $this->studyIds[] = $id;

        return $id;
    }

    /**
     * @param   int  $studyId  Study primary key
     *
     * @return  int  Junction rows belonging to that study
     *
     * @since __DEPLOY_VERSION__
     */
    private function junctionRows(int $studyId): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_study_scriptures'))
                ->where($this->db->quoteName('study_id') . ' = ' . $studyId)
        )->loadResult();
    }

    #[TestDox('a study already holding junction rows does not stop a later one being migrated')]
    public function testAPopulatedJunctionDoesNotDisableTheMigration(): void
    {
        // Stands in for any study saved through the editor: it has junction rows
        // already, which is what the old table-wide COUNT(*) guard tripped on.
        $saved = $this->legacyStudy(102);
        CwmscriptureMigration::migrateStudy($saved);
        $this->assertGreaterThan(0, $this->junctionRows($saved), 'Fixture did not migrate.');

        $stranded = $this->legacyStudy(103);

        CwmscriptureMigration::migrate();

        $this->assertSame(
            1,
            $this->junctionRows($stranded),
            'The migration reported nothing to do because some other study already had junction rows. '
            . 'That is the defect: one saved study strands every importer-written study on the site.'
        );
    }

    #[TestDox('running the migration twice does not duplicate rows')]
    public function testMigrationIsIdempotent(): void
    {
        $study = $this->legacyStudy(104, 105);

        CwmscriptureMigration::migrate();
        $afterFirst = $this->junctionRows($study);

        CwmscriptureMigration::migrate();

        $this->assertSame(2, $afterFirst, 'Both flat references should have become junction rows.');
        $this->assertSame(
            $afterFirst,
            $this->junctionRows($study),
            'The second run migrated the same study again. Rerunning is normal — Backup, Restore and the '
            . 'control panel data fixes all call this.'
        );
    }

    #[TestDox('a run that stopped part way is picked up by the next one')]
    public function testAnInterruptedRunResumes(): void
    {
        $done    = $this->legacyStudy(106);
        $pending = $this->legacyStudy(107);

        // Stands in for the rest of the site. A freshly installed database has
        // one of these for real — the seeded study — so without it this test
        // passes locally and fails on CI.
        $this->legacyStudy(110);

        // What an insertBatch() failure leaves behind: some studies migrated,
        // the rest still holding only flat values.
        CwmscriptureMigration::migrateStudy($done);

        // ⚠️ Assert on the fixtures, never on migrate()'s return count. It works
        // over the whole table, and a freshly installed database carries the
        // seeded study from install.mysql.utf8.sql — which is itself unmigrated,
        // and is the very defect under test.
        CwmscriptureMigration::migrate();

        $this->assertSame(1, $this->junctionRows($pending), 'The stranded study was never resumed.');
        $this->assertSame(1, $this->junctionRows($done), 'The already-migrated study was migrated a second time.');
    }

    #[TestDox('a study with no legacy values is left alone')]
    public function testStudiesWithoutLegacyValuesAreSkipped(): void
    {
        $empty = $this->legacyStudy(0);

        CwmscriptureMigration::migrate();

        $this->assertSame(0, $this->junctionRows($empty));
        $this->assertSame(0, CwmscriptureMigration::migrateStudy($empty));
    }

    #[TestDox('migrateStudy only touches the study it was given')]
    public function testMigrateStudyIsScopedToOneStudy(): void
    {
        $target = $this->legacyStudy(108);
        $other  = $this->legacyStudy(109);

        $this->assertSame(1, CwmscriptureMigration::migrateStudy($target));

        $this->assertSame(1, $this->junctionRows($target));
        $this->assertSame(
            0,
            $this->junctionRows($other),
            'The importers call this per study as they insert. Migrating anything else would be a full-table '
            . 'scan on every imported row.'
        );
    }
}

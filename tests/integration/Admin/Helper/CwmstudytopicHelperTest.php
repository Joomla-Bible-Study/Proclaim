<?php

/**
 * Integration tests for study/topic association saving.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmstudytopicHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1574.
 *
 * saveTopics() deleted a study's topic rows and reinserted the new set with no
 * transaction, so a failure partway through left the study with neither the
 * old associations nor the intended ones. Its only caller discards the return
 * value, so the message save still reported success.
 *
 * The junction table also had only a non-unique KEY on (study_id, topic_id),
 * letting concurrent saves duplicate rows -- 68 duplicate pairs were present on
 * a development database when this was written. And nothing removed the
 * associations when a study was deleted, leaving rows that topic counts and
 * filter dropdowns still join against.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmstudytopicHelper::class)]
class CwmstudytopicHelperTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    private int $studyId = 0;

    private int $rowsAtStart = 0;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        $this->rowsAtStart = $this->countAllRows();

        $study = (object) [
            'published'  => 0,
            'studytitle' => 'cwm1574 fixture',
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $this->studyId = (int) $this->db->insertid();
    }

    protected function tearDown(): void
    {
        $leaked = null;

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
                $after = $this->countAllRows();

                if ($after !== $this->rowsAtStart) {
                    $leaked = \sprintf(
                        'Test isolation broke: #__bsms_studytopics went from %d to %d rows.',
                        $this->rowsAtStart,
                        $after
                    );
                }
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();

        if ($leaked !== null) {
            $this->fail($leaked);
        }
    }

    /**
     * @return  int  Total rows in the junction table.
     */
    private function countAllRows(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_studytopics'))
        )->loadResult();
    }

    /**
     * @return  int[]  topic_ids currently associated with the fixture study.
     */
    private function topicIds(): array
    {
        return array_map('intval', $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('topic_id'))
                ->from($this->db->quoteName('#__bsms_studytopics'))
                ->where($this->db->quoteName('study_id') . ' = ' . $this->studyId)
                ->order($this->db->quoteName('topic_id'))
        )->loadColumn());
    }

    /**
     * Seed association rows directly, bypassing the Table layer.
     *
     * @param   int[]  $topicIds  Topic ids to associate
     *
     * @return  void
     */
    private function seedTopics(array $topicIds): void
    {
        foreach ($topicIds as $topicId) {
            // insertObject() takes its second argument by reference, so this
            // has to be a variable rather than an inline literal.
            $row = (object) [
                'study_id' => $this->studyId,
                'topic_id' => $topicId,
                'asset_id' => 0,
                'access'   => 1,
            ];
            $this->db->insertObject('#__bsms_studytopics', $row, 'id');
        }
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- atomicity
    // -------------------------------------------------------------------------

    /**
     * The guarantee that was missing: a failure partway through must leave the
     * study with the associations it had.
     */
    #[TestDox('A failed save leaves the original topics intact')]
    public function testFailedSaveRollsBack(): void
    {
        // Seeded directly: this harness resolves com_proclaim through a
        // LegacyFactory whose createTable() returns false, so the helper cannot
        // write rows here. That is also what makes the failure deterministic --
        // in a full application the same save fails at store() on the
        // out-of-range topic id below. Either way the property under test is
        // the same: the DELETE must not survive a failed insert phase.
        $this->seedTopics([11, 12]);

        $this->assertSame([11, 12], $this->topicIds(), 'Precondition');

        // topic_id is INT(3); this value is beyond the column's range.
        $result = CwmstudytopicHelper::saveTopics($this->studyId, [21, 999999999999999, 23]);

        $this->assertFalse($result, 'A failed save must report failure rather than a fatal error');
        $this->assertSame(
            [11, 12],
            $this->topicIds(),
            'The delete committed independently of the inserts, losing the original topics — see #1574'
        );
    }

    #[TestDox('An unresolvable table object is handled, not fatal')]
    public function testUnresolvableTableIsHandled(): void
    {
        // Guards `$tagRow->study_id = ...` against createTable() having
        // returned false, which is a fatal rather than a handled failure.
        $this->assertFalse(
            CwmstudytopicHelper::saveTopics($this->studyId, [5]),
            'Expected a handled failure in this harness'
        );
        $this->assertSame([], $this->topicIds(), 'Nothing should be left behind');
    }

    // -------------------------------------------------------------------------
    // Finding 3 -- the missing delete cascade
    // -------------------------------------------------------------------------

    #[TestDox('deleteTopics removes every association for a study')]
    public function testDeleteTopicsRemovesAssociations(): void
    {
        $this->seedTopics([31, 32, 33]);
        $this->assertCount(3, $this->topicIds(), 'Precondition');

        CwmstudytopicHelper::deleteTopics($this->studyId);

        $this->assertSame([], $this->topicIds(), 'Deleting a study left permanent orphan rows — see #1574');
    }

    /**
     * The cascade only helps if the table actually calls it. Deleting a real
     * message needs far more scaffolding than this fixture, so the wiring is
     * pinned in the source next to the sibling cleanup calls it belongs with.
     */
    #[TestDox('CwmmessageTable::delete() cleans up topic associations')]
    public function testDeleteCascadeIsWiredIn(): void
    {
        $source = file_get_contents(JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php');

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'CwmstudytopicHelper::deleteTopics((int) $pk);',
            $source,
            'Teacher and scripture rows were cleaned on delete but topic rows were not — see #1574'
        );
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- the missing unique constraint
    // -------------------------------------------------------------------------

    #[TestDox('The schema declares a unique constraint on (study_id, topic_id)')]
    public function testSchemaDeclaresTheUniqueConstraint(): void
    {
        $install = file_get_contents(JPATH_ROOT . '/admin/sql/install.mysql.utf8.sql');

        $this->assertIsString($install);
        $this->assertStringContainsString(
            'UNIQUE KEY `uq_study_topic` (`study_id`, `topic_id`)',
            $install,
            'A fresh install must not be able to accumulate duplicate associations — see #1574'
        );

        $migrations = glob(JPATH_ROOT . '/admin/sql/updates/mysql/*.sql') ?: [];
        $adds       = array_filter(
            $migrations,
            // The statement, not the name. A comment in another migration that
            // explains why the index exists also contains the word.
            static fn (string $f): bool => str_contains(
                (string) file_get_contents($f),
                'ADD UNIQUE KEY `uq_study_topic`'
            )
        );

        $this->assertNotEmpty($adds, 'Existing installs need a migration, not just a changed install file');
    }

    /**
     * The de-duplication has to run before the index is added, or the ALTER
     * fails on any site that already has duplicates -- which is most of them.
     */
    #[TestDox('The migration de-duplicates before adding the constraint')]
    public function testMigrationDeduplicatesFirst(): void
    {
        $migrations = glob(JPATH_ROOT . '/admin/sql/updates/mysql/*.sql') ?: [];

        foreach ($migrations as $file) {
            $sql = (string) file_get_contents($file);

            // Selected by the statement rather than the name: glob() returns
            // these in order, so prose mentioning the index in an earlier file
            // used to win the scan and be asserted against instead.
            if (!str_contains($sql, 'ADD UNIQUE KEY `uq_study_topic`')) {
                continue;
            }

            $dedupeAt = strpos($sql, 'DELETE t1 FROM');
            $addAt    = strpos($sql, 'ADD UNIQUE KEY `uq_study_topic`');

            $this->assertNotFalse($dedupeAt, 'Expected a de-duplication step in ' . basename($file));
            $this->assertNotFalse($addAt, 'Expected the index to be added in ' . basename($file));
            $this->assertLessThan(
                $addAt,
                $dedupeAt,
                'ADD UNIQUE fails on any site with existing duplicates unless they are removed first'
            );

            return;
        }

        $this->fail('No migration adds uq_study_topic');
    }
}

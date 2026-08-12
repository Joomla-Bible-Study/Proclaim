<?php

/**
 * Integration tests for atomic study/teacher association saving.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmstudyteacherHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1575.
 *
 * saveTeachers() deleted a study's teacher rows then inserted the new set one
 * at a time with nothing wrapping the two. CwmmessageModel::save() commits the
 * study row before calling it, so a failure partway through left the study
 * saved but its teacher list truncated, while the administrator saw a fatal as
 * though the save had not happened.
 *
 * #__bsms_studies.teacher_id duplicates the primary teacher, and callers wrote
 * it in a separate statement that never ran when the first threw -- the same
 * divergence fixed for scripture references in #1573.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmstudyteacherHelper::class)]
class CwmstudyteacherSaveTest extends IntegrationTestCase
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
            'studytitle' => 'cwm1575 fixture',
            'language'   => '*',
            'teacher_id' => 0,
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
                        'Test isolation broke: #__bsms_study_teachers went from %d to %d rows.',
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
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_study_teachers'))
        )->loadResult();
    }

    /**
     * @return  int[]  teacher_ids associated with the fixture study, in order.
     */
    private function teacherIds(): array
    {
        return array_map('intval', $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('teacher_id'))
                ->from($this->db->quoteName('#__bsms_study_teachers'))
                ->where($this->db->quoteName('study_id') . ' = ' . $this->studyId)
                ->order($this->db->quoteName('ordering'))
        )->loadColumn());
    }


    /**
     * @param   int[]  $ids  Teacher ids
     *
     * @return  array[]  Entries shaped as saveTeachers() expects
     */
    private function entries(array $ids): array
    {
        return array_map(static fn (int $id): array => ['teacher_id' => $id], $ids);
    }

    #[TestDox('A successful save replaces the teacher list')]
    public function testSuccessfulSaveReplacesTeachers(): void
    {
        CwmstudyteacherHelper::saveTeachers($this->studyId, $this->entries([4, 5]));
        $this->assertSame([4, 5], $this->teacherIds());

        CwmstudyteacherHelper::saveTeachers($this->studyId, $this->entries([9]));
        $this->assertSame([9], $this->teacherIds(), 'The previous list must be replaced, not appended to');
    }

    #[TestDox('Duplicate and empty entries are skipped')]
    public function testInputIsNormalised(): void
    {
        CwmstudyteacherHelper::saveTeachers($this->studyId, $this->entries([7, 7, 0, 8]));
        $this->assertSame([7, 8], $this->teacherIds());
    }

    /**
     * The guarantee that was missing: the study row is already committed by the
     * time this runs, so a half-written teacher list is not recoverable.
     */
    #[TestDox('A failure mid-loop leaves the original teacher list intact')]
    public function testFailedSaveRollsBack(): void
    {
        CwmstudyteacherHelper::saveTeachers($this->studyId, $this->entries([11, 12, 13]));
        $this->assertSame([11, 12, 13], $this->teacherIds(), 'Precondition');

        // teacher_id is an INT column; a value beyond its range makes the
        // second insert fail after the first has been written.
        try {
            CwmstudyteacherHelper::saveTeachers(
                $this->studyId,
                [['teacher_id' => 21], ['teacher_id' => 999999999999999], ['teacher_id' => 23]]
            );
            $this->fail('Expected the out-of-range teacher id to raise');
        } catch (\Throwable) {
            // Expected — callers still see the failure.
        }

        $this->assertSame(
            [11, 12, 13],
            $this->teacherIds(),
            'The delete committed independently of the inserts, truncating the teacher list — see #1575'
        );
    }

    #[TestDox('Saving writes the teachers and leaves nothing behind')]
    public function testSaveWritesTheJunction(): void
    {
        CwmstudyteacherHelper::saveTeachers($this->studyId, $this->entries([42, 43]));

        $this->assertSame([42, 43], $this->teacherIds(), 'Order matters: the first entry is the primary.');
    }

    #[TestDox('A failed save restores the teachers it replaced')]
    public function testFailedSaveRestoresThePrevious(): void
    {
        CwmstudyteacherHelper::saveTeachers($this->studyId, $this->entries([11]));

        try {
            CwmstudyteacherHelper::saveTeachers(
                $this->studyId,
                [['teacher_id' => 20], ['teacher_id' => 999999999999999]]
            );
            $this->fail('Expected the out-of-range teacher id to raise');
        } catch (\Throwable) {
            // Expected.
        }

        // Assert which teacher survived, not merely how many: a count of one is
        // satisfied both by the restored row and by an orphan from the failed
        // write, which are opposite outcomes.
        $this->assertSame(
            [11],
            $this->teacherIds(),
            'saveTeachers() deletes then inserts. Without a transaction around the pair, a failing insert '
            . 'leaves the study with its old teachers gone and only some of the new ones written — see #1575.'
        );
    }
}

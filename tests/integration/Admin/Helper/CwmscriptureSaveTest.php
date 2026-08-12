<?php

/**
 * Integration tests for atomic scripture reference saving.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1573.
 *
 * saveScriptures() deleted a study's references and then inserted the new set
 * one row at a time with nothing wrapping the two. An insert failing partway
 * through left the old references gone and only some of the new ones written.
 *
 * The flat columns those callers also maintained are no longer written (#1623);
 * what remains to protect is the delete-then-insert pair itself.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmscriptureHelper::class)]
class CwmscriptureSaveTest extends IntegrationTestCase
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

        $this->rowsAtStart = $this->countAllReferences();

        // A study of our own, so no real content is touched.
        $study = (object) [
            'published'     => 0,
            'studytitle'    => 'cwm1573 fixture',
            'booknumber'    => 1,
            'chapter_begin' => 1,
            'verse_begin'   => 1,
            'chapter_end'   => 1,
            'verse_end'     => 1,
            'bible_version' => 'kjv',
            'language'      => '*',
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

                $after = $this->countAllReferences();

                if ($after !== $this->rowsAtStart) {
                    $leaked = \sprintf(
                        'Test isolation broke: #__bsms_study_scriptures went from %d to %d rows.',
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
    private function countAllReferences(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_study_scriptures'))
        )->loadResult();
    }

    /**
     * @return  int  Reference rows for the fixture study.
     */
    private function countReferences(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_study_scriptures'))
                ->where($this->db->quoteName('study_id') . ' = ' . $this->studyId)
        )->loadResult();
    }

    /**
     * @return  int  booknumber currently on the flat columns.
     */
    private function legacyBookNumber(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('booknumber'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('id') . ' = ' . $this->studyId)
        )->loadResult();
    }

    /**
     * Build a reference object shaped like ScriptureReference.
     *
     * @param   int   $book   Book number
     * @param   mixed $verse  verse_begin value; a non-numeric value makes the
     *                        INSERT fail, which is how a mid-loop failure is
     *                        provoked without mocking the driver.
     *
     * @return  object
     */
    private function ref(int $book, mixed $verse = 1): object
    {
        return (object) [
            'booknumber'    => $book,
            'chapterBegin'  => 1,
            'verseBegin'    => $verse,
            'chapterEnd'    => 1,
            'verseEnd'      => 2,
            'bibleVersion'  => 'kjv',
            'referenceText' => 'Book ' . $book . ' 1:1-2',
        ];
    }

    #[TestDox('A successful save replaces the reference set')]
    public function testSuccessfulSaveReplacesReferences(): void
    {
        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(1), $this->ref(2)]);
        $this->assertSame(2, $this->countReferences());

        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(5)]);
        $this->assertSame(1, $this->countReferences(), 'The previous set must be replaced, not appended to');
    }

    /**
     * The core guarantee: a failure partway through must leave the study with
     * the references it had, not a half-written set.
     */
    #[TestDox('A failure mid-loop leaves the original references intact')]
    public function testFailedSaveRollsBackToTheOriginalReferences(): void
    {
        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(1), $this->ref(2), $this->ref(3)]);
        $this->assertSame(3, $this->countReferences(), 'Precondition');

        // Second reference carries a value the column will not accept, so the
        // loop throws after the first insert has already been written.
        try {
            CwmscriptureHelper::saveScriptures(
                $this->studyId,
                [$this->ref(7), $this->ref(8, 'not-a-number'), $this->ref(9)]
            );
            $this->fail('Expected the invalid reference to raise');
        } catch (\Throwable) {
            // Expected — the contract is that callers still see the failure.
        }

        $this->assertSame(
            3,
            $this->countReferences(),
            'The delete committed independently of the inserts, losing the old references — see #1573'
        );

        $books = $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('booknumber'))
                ->from($this->db->quoteName('#__bsms_study_scriptures'))
                ->where($this->db->quoteName('study_id') . ' = ' . $this->studyId)
                ->order($this->db->quoteName('ordering'))
        )->loadColumn();

        $this->assertSame(['1', '2', '3'], array_map('strval', $books), 'The original set must be exactly restored');
    }

    #[TestDox('The exception still reaches the caller after rollback')]
    public function testFailureStillPropagates(): void
    {
        $this->expectException(\Throwable::class);

        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(1, 'not-a-number')]);
    }

    // -------------------------------------------------------------------------
    // The junction table and the flat columns must not diverge
    // -------------------------------------------------------------------------

    #[TestDox('Saving writes the references and leaves the legacy columns alone')]
    public function testSaveWritesTheJunctionOnly(): void
    {
        $before = $this->legacyBookNumber();

        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(42)]);

        $this->assertSame(1, $this->countReferences());
        $this->assertSame(
            $before,
            $this->legacyBookNumber(),
            'The flat columns are no longer written (#1623). Every reader prefers the junction, so a stale '
            . 'value here is invisible -- but writing one would put the retirement back where it started.'
        );
    }

    /**
     * The divergence this issue is really about: references written, flat
     * columns left describing the previous set.
     */
    #[TestDox('A failed save restores the references it replaced')]
    public function testFailedSaveLeavesTheJunctionIntact(): void
    {
        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(11)]);

        try {
            CwmscriptureHelper::saveScriptures(
                $this->studyId,
                [$this->ref(20), $this->ref(21, 'not-a-number')]
            );
            $this->fail('Expected the invalid reference to raise');
        } catch (\Throwable) {
            // Expected.
        }

        // Assert the surviving reference, not just how many there are: a bare
        // count of 1 is satisfied both by the restored reference and by a
        // single orphaned row from the failed write, which are opposite
        // outcomes.
        $books = array_map('intval', $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('booknumber'))
                ->from($this->db->quoteName('#__bsms_study_scriptures'))
                ->where($this->db->quoteName('study_id') . ' = ' . $this->studyId)
        )->loadColumn());

        $this->assertSame(
            [11],
            $books,
            'saveScriptures() deletes then inserts. Without a transaction around the pair, a failing '
            . 'insert leaves the old references gone and only some of the new ones written — see #1573.'
        );
    }
}

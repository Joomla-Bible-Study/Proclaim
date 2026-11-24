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
 * @since  10.5.6
 */
#[CoversClass(CwmscriptureHelper::class)]
class CwmscriptureSaveTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back. tearDown() checks that the
     * rollback restored the scripture references.
     *
     * @var    DatabaseDriver|null
     * @since  10.5.6
     */
    private ?DatabaseDriver $db = null;

    /**
     * The id of the unpublished study setUp() inserts, which the references belong to.
     *
     * @var    int
     * @since  10.5.6
     */
    private int $studyId = 0;

    /**
     * How many scripture references there were when setUp() ran.
     *
     * @var    int
     * @since  10.5.6
     */
    private int $rowsAtStart = 0;

    /**
     * Skip without a test database. Otherwise open a transaction, count the scripture
     * references, and insert an unpublished study.
     *
     * @return  void
     *
     * @since   10.5.6
     */
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
            'published'  => 0,
            'studytitle' => 'cwm1573 fixture',
            'alias'      => $this->uniqueAlias('cwmscripturesave-study'),
            'language'   => '*',
        ];
        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $this->studyId = (int) $this->db->insertid();
    }

    /**
     * Roll back the transaction setUp() opened, then fail the test if the scripture
     * references don't number what they did at setUp().
     *
     * @return  void
     *
     * @since   10.5.6
     */
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
     * The number of scripture references, across every study.
     *
     * @return  int  Total rows in the junction table.
     *
     * @since   10.5.6
     */
    private function countAllReferences(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_study_scriptures'))
        )->loadResult();
    }

    /**
     * The number of scripture references the test's study has.
     *
     * @return  int  Reference rows for the fixture study.
     *
     * @since   10.5.6
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
     * Build a reference object shaped like ScriptureReference.
     *
     * @param   int   $book   Book number
     * @param   mixed $verse  verse_begin value; a non-numeric value makes the
     *                        INSERT fail, which is how a mid-loop failure is
     *                        provoked without mocking the driver.
     *
     * @return  object
     *
     * @since   10.5.6
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

    /**
     * saveScriptures() stores two references, then replaces them with the one it's given
     * next.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \Throwable
     */
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
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \Throwable
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

    /**
     * saveScriptures() lets the failure an invalid reference causes reach its caller.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    #[TestDox('The exception still reaches the caller after rollback')]
    public function testFailureStillPropagates(): void
    {
        $this->expectException(\Throwable::class);

        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(1, 'not-a-number')]);
    }

    // -------------------------------------------------------------------------
    // The junction table and the flat columns must not diverge
    // -------------------------------------------------------------------------

    /**
     * saveScriptures() stores the reference it's given.
     *
     * @return  void
     *
     * @since   10.5.8
     * @throws  \Throwable
     */
    #[TestDox('Saving writes the references')]
    public function testSaveWritesTheJunction(): void
    {
        CwmscriptureHelper::saveScriptures($this->studyId, [$this->ref(42)]);

        $this->assertSame(1, $this->countReferences());
    }

    /**
     * The divergence this issue is really about: references written, flat
     * columns left describing the previous set.
     *
     * @return  void
     *
     * @since   10.5.8
     * @throws  \Throwable
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

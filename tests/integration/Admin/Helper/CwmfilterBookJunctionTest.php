<?php

/**
 * The cross-filter for Book matches against the scripture junction.
 *
 * It used to compare the two flat columns:
 *
 *     (s.booknumber = X OR s.booknumber2 = X)
 *
 * which can only answer for a study's first two references. Filtering on a
 * study's third book found nothing, and every dropdown that cross-filters --
 * Teacher, Series, Location, Topic, Year, Message Type -- inherited the gap
 * (#1623).
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since 10.5.8
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmfilterHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * applyCrossFilters() matches the Book filter against every book a study references in
 * the scripture junction, not just its first two, and leaves it out when the caller
 * excludes it.
 *
 * @since  10.5.8
 */
#[CoversClass(CwmfilterHelper::class)]
class CwmfilterBookJunctionTest extends IntegrationTestCase
{
    /**
     * The list context the filter state is kept under.
     *
     * @var    string
     * @since  10.5.8
     */
    private const CONTEXT = 'com_proclaim.cwm1623.list';

    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseDriver|null
     * @since  10.5.8
     */
    private ?DatabaseDriver $db = null;

    /**
     * The application setUp() replaced with the stub, for tearDown() to put back.
     *
     * @var    mixed
     * @since  10.5.8
     */
    private mixed $realApplication = null;

    /**
     * Filter values the stub application hands back, keyed by state key.
     *
     * @var array<string, mixed>
     * @since 10.5.8
     */
    private array $filterState = [];

    /**
     * Skip without a test database. Otherwise open a transaction, and swap the application
     * for a stub whose getUserStateFromRequest() reads the test's filter state.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        // The test harness runs a ConsoleApplication, which has no user state
        // at all, and applyCrossFilters() reads its values through
        // getUserStateFromRequest(). Standing up a real SiteApplication needs a
        // session and an input; supplying just that one method is enough to
        // drive the code under test, and Factory::$application is a plain
        // untyped static, so it can be put back afterwards.
        $this->realApplication = Factory::$application;
        $state                 = &$this->filterState;

        Factory::$application = new class ($state) {
            /**
             * Hold the test's filter state by reference.
             *
             * @param   array  $state  Filter values by state key
             *
             * @since   10.5.8
             */
            public function __construct(private array &$state)
            {
            }

            /**
             * The filter value kept under the key, or the default when there is none.
             *
             * @param   string  $key      State key
             * @param   string  $request  Request variable name, unused here
             * @param   mixed   $default  Value when nothing is set
             * @param   string  $type     Filter type, unused here
             *
             * @return  mixed  The kept value, or the default
             *
             * @since   10.5.8
             */
            public function getUserStateFromRequest($key, $request, $default = null, $type = 'none'): mixed
            {
                return $this->state[$key] ?? $default;
            }
        };
    }

    /**
     * Put the real application back, and roll back the transaction setUp() opened.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    protected function tearDown(): void
    {
        Factory::$application = $this->realApplication;

        try {
            $this->db?->transactionRollback(true);
        } catch (\Throwable) {
            // Connection lost; nothing to roll back.
        }

        parent::tearDown();
    }

    /**
     * A study whose references live in the junction, in the order given.
     *
     * The flat columns are deliberately left alone. They can hold the first two
     * of these and nothing else, which is the whole point.
     *
     * @param   int[]  $books  Proclaim book numbers, in reference order
     *
     * @return  int  The new study's primary key
     *
     * @since 10.5.8
     */
    private function studyWithReferences(array $books): int
    {
        $study = (object) [
            'published'  => 1,
            'access'     => 1,
            'studytitle' => 'cwm1623 filter fixture',
            'alias'      => $this->uniqueAlias('cwmfilterbookjunction-study'),
            'language'   => '*',
        ];

        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $studyId = (int) $this->db->insertid();

        foreach ($books as $ordering => $book) {
            $row = (object) [
                'study_id'       => $studyId,
                'ordering'       => $ordering,
                'booknumber'     => $book,
                'chapter_begin'  => 1,
                'verse_begin'    => 1,
                'chapter_end'    => 1,
                'verse_end'      => 5,
                'bible_version'  => 'kjv',
                'reference_text' => 'cwm1623 fixture reference',
            ];
            $this->db->insertObject('#__bsms_study_scriptures', $row);
        }

        return $studyId;
    }

    /**
     * Study ids surviving the Book cross-filter.
     *
     * Mirrors what a *ListField does: build a query over the studies aliased
     * `s`, then hand it to applyCrossFilters().
     *
     * @param   int  $book  Book number to filter on
     *
     * @return  int[]
     *
     * @since 10.5.8
     */
    private function filteredStudyIds(int $book): array
    {
        $this->filterState[self::CONTEXT . '.filter.book'] = $book;

        $query = $this->db->createQuery()
            ->select('DISTINCT ' . $this->db->quoteName('s.id'))
            ->from($this->db->quoteName('#__bsms_studies', 's'));

        CwmfilterHelper::applyCrossFilters($query, 'teacher', self::CONTEXT);

        return array_map('intval', $this->db->setQuery($query)->loadColumn() ?: []);
    }

    /**
     * Filtering on the third of a study's three books finds the study.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    #[TestDox('filtering on a study\'s third book finds it')]
    public function testTheThirdReferenceIsFilterable(): void
    {
        // 170-172 are deuterocanonical numbers the sample content never uses,
        // so a match can only come from this fixture.
        $studyId = $this->studyWithReferences([170, 171, 172]);

        $this->assertContains(
            $studyId,
            $this->filteredStudyIds(172),
            'Filtering on the third reference found nothing. The flat pair holds two, so everything after '
            . 'the second was unreachable from every dropdown that cross-filters.'
        );
    }

    /**
     * Filtering on either of a study's first two books finds it.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    #[TestDox('the first two references still filter as they always did')]
    public function testTheFirstTwoReferencesStillMatch(): void
    {
        $studyId = $this->studyWithReferences([170, 171, 172]);

        $this->assertContains($studyId, $this->filteredStudyIds(170), 'The primary reference stopped matching.');
        $this->assertContains($studyId, $this->filteredStudyIds(171), 'The secondary reference stopped matching.');
    }

    /**
     * Filtering on a book the study doesn't reference leaves it out.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    #[TestDox('a book the study does not reference does not match it')]
    public function testUnrelatedBooksDoNotMatch(): void
    {
        $studyId = $this->studyWithReferences([170, 171, 172]);

        $this->assertNotContains(
            $studyId,
            $this->filteredStudyIds(169),
            'The join matched a book the study has no reference to.'
        );
    }

    /**
     * With the book filter excluded, as BookListField excludes it, the query still finds a
     * study whose only book isn't the one the filter holds.
     *
     * @return  void
     *
     * @since   10.5.8
     */
    #[TestDox('the filter is skipped entirely when the caller excludes it')]
    public function testExcludingBookSkipsTheJoin(): void
    {
        $studyId = $this->studyWithReferences([170]);

        $this->filterState[self::CONTEXT . '.filter.book'] = 169;

        $query = $this->db->createQuery()
            ->select('DISTINCT ' . $this->db->quoteName('s.id'))
            ->from($this->db->quoteName('#__bsms_studies', 's'))
            ->where($this->db->quoteName('s.id') . ' = ' . $studyId);

        // What BookListField itself passes, so its own dropdown does not
        // self-filter down to the option already chosen.
        CwmfilterHelper::applyCrossFilters($query, 'book', self::CONTEXT);

        $this->assertSame(
            [$studyId],
            array_map('intval', $this->db->setQuery($query)->loadColumn() ?: []),
            'Excluding the book filter still constrained the query, so BookListField would only ever offer '
            . 'the book already selected.'
        );
    }
}

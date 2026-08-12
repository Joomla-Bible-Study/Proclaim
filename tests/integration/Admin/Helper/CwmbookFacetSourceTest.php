<?php

/**
 * The book facets are driven by the scripture junction, not by the flat
 * columns on #__bsms_studies.
 *
 * Those columns hold at most two references, so a study covering three or more
 * passages only ever offered its first two as filter options -- the rest were
 * invisible to every book list on the site even though the junction knew about
 * them (#1623).
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CwmproclaimHelper::class)]
class CwmbookFacetSourceTest extends IntegrationTestCase
{
    /**
     * @var DatabaseDriver|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseDriver $db = null;

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
     * A published study whose references live only in the junction.
     *
     * The flat columns are left at their defaults on purpose: a study saved
     * after the columns are retired looks exactly like this, and the facets
     * still have to find it.
     *
     * @param   int[]  $books  Proclaim book numbers, in reference order
     *
     * @return  int  The new study's primary key
     *
     * @since __DEPLOY_VERSION__
     */
    private function studyWithReferences(array $books): int
    {
        $study = (object) [
            'published'  => 1,
            'access'     => 1,
            'studytitle' => 'cwm1623 facet fixture',
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
     * @return  int[]  Book numbers offered by getStudyBooks()
     *
     * @since __DEPLOY_VERSION__
     */
    private function offeredBooks(): array
    {
        return array_map(
            static fn ($option) => (int) $option->value,
            CwmproclaimHelper::getStudyBooks()
        );
    }

    /**
     * Book numbers no study currently covers, so an assertion cannot be
     * satisfied by real fixture content happening to use them.
     *
     * @param   int  $count  How many to return
     *
     * @return  int[]
     *
     * @since __DEPLOY_VERSION__
     */
    private function unusedBooks(int $count): array
    {
        $free = array_values(array_diff(range(101, 166), $this->offeredBooks()));

        $this->assertGreaterThanOrEqual($count, \count($free), 'No spare book numbers left in the fixture data.');

        return \array_slice($free, 0, $count);
    }

    #[TestDox('a study with three references offers all three, not the two the flat columns held')]
    public function testAllReferencesBecomeFacetOptions(): void
    {
        $books = $this->unusedBooks(3);

        $this->studyWithReferences($books);

        $after = $this->offeredBooks();

        foreach ($books as $i => $book) {
            $this->assertContains(
                $book,
                $after,
                \sprintf(
                    'Reference %d of 3 is missing from the book facet. The flat columns hold two references, '
                    . 'so reading them drops every one after the second.',
                    $i + 1
                )
            );
        }
    }

    #[TestDox('the facet follows the junction even when the flat column says otherwise')]
    public function testTheJunctionWinsOverTheFlatColumn(): void
    {
        $book    = $this->unusedBooks(1)[0];
        $studyId = $this->studyWithReferences([$book]);

        // ⚠️ booknumber is `DEFAULT '101'`, so a study nothing wrote a book to
        // still reads as Genesis. The flat column can never say "no reference",
        // which is half of why it is being retired.
        $flat = (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('booknumber'))
                ->from($this->db->quoteName('#__bsms_studies'))
                ->where($this->db->quoteName('id') . ' = ' . $studyId)
        )->loadResult();

        $this->assertSame(101, $flat, 'The column default changed; this fixture no longer sets up the conflict.');
        $this->assertNotSame(101, $book, 'Pick a fixture book other than the column default.');

        $this->assertContains(
            $book,
            $this->offeredBooks(),
            'The facet did not offer the junction\'s book, so it is still reading the flat column.'
        );
    }

    #[TestDox('a study with no references contributes nothing')]
    public function testStudiesWithoutReferencesAreExcluded(): void
    {
        $before = $this->offeredBooks();

        $this->studyWithReferences([]);

        $this->assertSame(
            $before,
            $this->offeredBooks(),
            'A study with no scripture references gained a facet entry. The INNER JOIN is what keeps it out.'
        );
    }
}

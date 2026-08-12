<?php

/**
 * The Book filter must not let a study escape the conditions around it.
 *
 * `addBookChapterWhere()` built its clause as
 *
 *     (booknumber = X AND chapter_begin >= C) OR booknumber2 = X
 *
 * and handed it to `QueryInterface::where()`, which appends the string and glues
 * it with AND without bracketing it. AND binds tighter than OR, so the finished
 * SQL read
 *
 *     WHERE published = 1 AND access IN (...) AND (...) OR booknumber2 = X
 *     -- i.e. ( everything AND (...) ) OR ( booknumber2 = X )
 *
 * Any study whose *secondary* reference matched the filtered book was returned
 * regardless of published state, view level or language. `minChapt` and
 * `maxChapt` come straight off the request, so an anonymous visitor reached it
 * by appending them to a URL.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmsermonsModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CwmsermonsModel::class)]
class CwmsermonsBookFilterScopeTest extends IntegrationTestCase
{
    /**
     * A book number the sample content never uses, so only fixtures match it.
     *
     * @var int
     * @since __DEPLOY_VERSION__
     */
    private const BOOK = 160;

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
        $input = Factory::getApplication()->getInput();
        $input->set('minChapt', null);
        $input->set('maxChapt', null);

        try {
            $this->db?->transactionRollback(true);
        } catch (\Throwable) {
            // Connection lost; nothing to roll back.
        }

        parent::tearDown();
    }

    /**
     * A study, plus the scripture references the filter now matches on.
     *
     * ⚠️ The references must be real junction rows. A study with none can never
     * match, so a fixture without them makes the two exclusion tests below pass
     * whatever the filter does.
     *
     * @param   array<string, mixed>       $overrides   Column values for the study
     * @param   array<int, array{int,int}> $references  [book, chapter] per reference
     *
     * @return  int  The new study's primary key
     *
     * @since __DEPLOY_VERSION__
     */
    private function study(array $overrides = [], array $references = [[self::BOOK, 100]]): int
    {
        $study = (object) array_merge([
            'studytitle' => 'cwm1623 scope fixture',
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
        ], $overrides);

        $this->db->insertObject('#__bsms_studies', $study, 'id');
        $studyId = (int) $this->db->insertid();

        foreach ($references as $ordering => [$book, $chapter]) {
            // insertObject() takes its object by reference, so this cannot be inlined.
            $reference = (object) [
                'study_id'       => $studyId,
                'ordering'       => $ordering,
                'booknumber'     => $book,
                'chapter_begin'  => $chapter,
                'chapter_end'    => $chapter,
                'bible_version'  => 'kjv',
                'reference_text' => 'cwm1623 fixture reference',
            ];

            $this->db->insertObject('#__bsms_study_scriptures', $reference);
        }

        return $studyId;
    }

    /**
     * Run the real filter over a public, published-only query.
     *
     * The model is built without its constructor: `addBookChapterWhere()` uses
     * only its arguments and the application input, so standing up an MVCFactory
     * would add nothing.
     *
     * @param   int  $minChapt  Lower chapter bound to put on the request
     *
     * @return  int[]  Study ids the filter allows through
     *
     * @since __DEPLOY_VERSION__
     */
    private function visibleToGuest(int $minChapt): array
    {
        Factory::getApplication()->getInput()->set('minChapt', $minChapt);

        $query = $this->db->createQuery()
            ->select($this->db->quoteName('study.id'))
            ->from($this->db->quoteName('#__bsms_studies', 'study'))
            ->where($this->db->quoteName('study.published') . ' = 1')
            ->whereIn($this->db->quoteName('study.access'), [1, 5]);

        $model  = (new \ReflectionClass(CwmsermonsModel::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(CwmsermonsModel::class, 'addBookChapterWhere');
        $method->invoke($model, $query, $this->db, self::BOOK);

        return array_map('intval', $this->db->setQuery($query)->loadColumn() ?: []);
    }

    #[TestDox('an unpublished study does not escape through a matching reference')]
    public function testUnpublishedStudiesStayHidden(): void
    {
        $hidden = $this->study(['published' => 0]);

        $this->assertNotContains(
            $hidden,
            $this->visibleToGuest(99),
            'An unpublished study reached a published-only list. The OR was not bracketed, so it bound '
            . 'looser than the published condition and overrode it.'
        );
    }

    #[TestDox('a study above the visitor\'s view level does not escape through a matching reference')]
    public function testRestrictedStudiesStayHidden(): void
    {
        // 6 is Super Users on a stock Joomla install; the query asks for 1 and 5.
        $restricted = $this->study(['access' => 6]);

        $this->assertNotContains(
            $restricted,
            $this->visibleToGuest(99),
            'A study restricted to a privileged view level was returned to a guest.'
        );
    }

    #[TestDox('a published, public study matches on a reference other than its first')]
    public function testANonFirstReferenceStillMatches(): void
    {
        // The filter must not privilege the first reference. Under the flat
        // columns this was the booknumber2 case, and anything past it was
        // unreachable; there is no such distinction now.
        $allowed = $this->study([], [[101, 100], [102, 100], [self::BOOK, 100]]);

        $this->assertContains(
            $allowed,
            $this->visibleToGuest(99),
            'A study matching on its third reference was not returned.'
        );
    }

    #[TestDox('a multi-book menu param matches a study referencing any one of them')]
    public function testMultiBookParamMatchesAnyOfThem(): void
    {
        $second = $this->study([], [[161, 1]]);
        $none   = $this->study([], [[162, 1]]);

        $query = $this->db->createQuery()
            ->select($this->db->quoteName('study.id'))
            ->from($this->db->quoteName('#__bsms_studies', 'study'))
            ->where($this->db->quoteName('study.published') . ' = 1');

        // The menu/template param path: several books, no user filter.
        $model  = (new \ReflectionClass(CwmsermonsModel::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(CwmsermonsModel::class, 'addBookFilter');
        $method->invoke($model, $query, $this->db, [160, 161], 0);

        $visible = array_map('intval', $this->db->setQuery($query)->loadColumn() ?: []);

        $this->assertContains($second, $visible, 'A study referencing the second of the listed books was dropped.');
        $this->assertNotContains($none, $visible, 'A study referencing none of the listed books was returned.');
    }

    #[TestDox('the chapter range constrains whichever reference matched')]
    public function testChapterRangeStillApplies(): void
    {
        $inRange  = $this->study([], [[self::BOOK, 100]]);
        $tooEarly = $this->study([], [[self::BOOK, 1]]);

        $visible = $this->visibleToGuest(99);

        $this->assertContains($inRange, $visible, 'A study inside the chapter range was dropped.');
        $this->assertNotContains($tooEarly, $visible, 'A study below the chapter range was returned.');
    }
}

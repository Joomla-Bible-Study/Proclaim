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
     * @param   array<string, mixed>  $overrides  Column values for the fixture
     *
     * @return  int  The new study's primary key
     *
     * @since __DEPLOY_VERSION__
     */
    private function study(array $overrides): int
    {
        $study = (object) array_merge([
            'studytitle'    => 'cwm1623 scope fixture',
            'published'     => 1,
            'access'        => 1,
            'language'      => '*',
            'booknumber'    => 999,
            'booknumber2'   => 0,
            'chapter_begin' => 1,
            'chapter_end'   => 1,
        ], $overrides);

        $this->db->insertObject('#__bsms_studies', $study, 'id');

        return (int) $this->db->insertid();
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

    #[TestDox('an unpublished study does not escape through its secondary reference')]
    public function testUnpublishedStudiesStayHidden(): void
    {
        $hidden = $this->study(['published' => 0, 'booknumber2' => self::BOOK]);

        $this->assertNotContains(
            $hidden,
            $this->visibleToGuest(99),
            'An unpublished study reached a published-only list. The OR was not bracketed, so it bound '
            . 'looser than the published condition and overrode it.'
        );
    }

    #[TestDox('a study above the visitor\'s view level does not escape through its secondary reference')]
    public function testRestrictedStudiesStayHidden(): void
    {
        // 6 is Super Users on a stock Joomla install; the query asks for 1 and 5.
        $restricted = $this->study(['access' => 6, 'booknumber2' => self::BOOK]);

        $this->assertNotContains(
            $restricted,
            $this->visibleToGuest(99),
            'A study restricted to a privileged view level was returned to a guest.'
        );
    }

    #[TestDox('a published, public study still matches on its secondary reference')]
    public function testSecondaryReferenceStillMatches(): void
    {
        // The clause exists to find these; bracketing must not cost it.
        $allowed = $this->study(['booknumber2' => self::BOOK]);

        $this->assertContains(
            $allowed,
            $this->visibleToGuest(99),
            'Bracketing the disjunction stopped the secondary reference matching at all.'
        );
    }

    #[TestDox('the chapter range still constrains the primary reference')]
    public function testChapterRangeStillApplies(): void
    {
        $inRange  = $this->study(['booknumber' => self::BOOK, 'chapter_begin' => 100]);
        $tooEarly = $this->study(['booknumber' => self::BOOK, 'chapter_begin' => 1]);

        $visible = $this->visibleToGuest(99);

        $this->assertContains($inRange, $visible, 'A study inside the chapter range was dropped.');
        $this->assertNotContains($tooEarly, $visible, 'A study below the chapter range was returned.');
    }
}

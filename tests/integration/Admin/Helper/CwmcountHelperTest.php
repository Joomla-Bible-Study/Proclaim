<?php

/**
 * Integration tests for CwmcountHelper count queries.
 *
 * Focuses on the unpublished (state 0) count path used by the CPanel
 * "Content Awaiting Review" notice, plus the state-count vs. total invariant.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for CwmcountHelper.
 *
 * Seeds known rows inside a rolled-back transaction and asserts exact count
 * deltas. The prior version of this file asserted only invariants
 * (non-negative, subset-of-total) with no fixture — on CI's empty database
 * every count is 0, so 0 <= 0 held even if getCountByState() ignored $state
 * entirely or double-counted rows; the tests could not fail from the
 * regressions they were written to guard.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmcountHelperTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    /**
     * Skip the whole class when no live database is available.
     *
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Live database not available for count integration test.');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();

        // The helper caches counts per request keyed by table+state; without a
        // reset, a count taken before this test's fixtures were inserted would
        // be served back after them.
        $this->resetStaticCache(CwmcountHelper::class, 'cache', []);
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost — nothing to roll back.
            }
        }

        $this->resetStaticCache(CwmcountHelper::class, 'cache', []);

        parent::tearDown();
    }

    /**
     * Insert a study row in the given published state.
     *
     * @param   int  $published  Published state value
     *
     * @return  void
     */
    private function insertStudy(int $published): void
    {
        $row = (object) [
            'studytitle'  => 'Count Fixture ' . uniqid('', true),
            'alias'       => 'count-fixture-' . uniqid('', true),
            'studydate'   => '2026-01-15 10:00:00',
            'teacher_id'  => 0,
            'series_id'   => 0,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'params'      => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $row);
    }

    #[TestDox('Each published state is counted independently and exactly')]
    public function testStateCountsMatchSeededFixturesExactly(): void
    {
        // Baselines first: the dev DB has pre-existing rows, CI has none.
        // Exact-delta assertions work identically in both environments.
        $basePublished   = CwmcountHelper::getCountByState('#__bsms_studies', 1);
        $baseUnpublished = CwmcountHelper::getCountByState('#__bsms_studies', 0);
        $baseArchived    = CwmcountHelper::getCountByState('#__bsms_studies', 2);
        $baseTotal       = CwmcountHelper::getTotalCount('#__bsms_studies');

        $this->insertStudy(1);
        $this->insertStudy(0);
        $this->insertStudy(0);
        $this->insertStudy(2);
        $this->insertStudy(-2);

        $this->resetStaticCache(CwmcountHelper::class, 'cache', []);

        $this->assertSame(
            $basePublished + 1,
            CwmcountHelper::getCountByState('#__bsms_studies', 1),
            'Exactly one published fixture was added — a wrong delta means $state is being ignored or rows double-counted'
        );
        $this->assertSame(
            $baseUnpublished + 2,
            CwmcountHelper::getCountByState('#__bsms_studies', 0),
            'Exactly two unpublished fixtures were added'
        );
        $this->assertSame(
            $baseArchived + 1,
            CwmcountHelper::getCountByState('#__bsms_studies', 2),
            'Exactly one archived fixture was added'
        );
        $this->assertSame(
            $baseTotal + 4,
            CwmcountHelper::getTotalCount('#__bsms_studies'),
            'The non-trashed total must include published+unpublished+archived fixtures but exclude the trashed one'
        );
    }

    #[TestDox('The per-request cache serves the cached value until reset')]
    public function testCountIsCachedPerRequest(): void
    {
        $before = CwmcountHelper::getCountByState('#__bsms_studies', 0);

        $this->insertStudy(0);

        // Same key, no reset: must serve the cached pre-insert value. This is
        // the documented per-request caching contract (sendQuickIconResponse()
        // and the cpanel stats intentionally share one query per request).
        $this->assertSame(
            $before,
            CwmcountHelper::getCountByState('#__bsms_studies', 0),
            'Without a cache reset the helper must serve the per-request cached count'
        );

        $this->resetStaticCache(CwmcountHelper::class, 'cache', []);

        $this->assertSame(
            $before + 1,
            CwmcountHelper::getCountByState('#__bsms_studies', 0),
            'After a cache reset the new row must be visible'
        );
    }
}

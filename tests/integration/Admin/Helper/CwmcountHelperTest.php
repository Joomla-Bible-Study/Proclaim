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
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for CwmcountHelper.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmcountHelperTest extends IntegrationTestCase
{
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
    }

    #[TestDox('getCountByState(state 0) returns a non-negative unpublished count')]
    public function testUnpublishedCountIsNonNegative(): void
    {
        $unpublished = CwmcountHelper::getCountByState('#__bsms_studies', 0);

        $this->assertIsInt($unpublished);
        $this->assertGreaterThanOrEqual(0, $unpublished, 'Unpublished study count must not be negative');
    }

    #[TestDox('Unpublished count never exceeds the non-trashed total')]
    public function testUnpublishedCountWithinTotal(): void
    {
        $unpublished = CwmcountHelper::getCountByState('#__bsms_studies', 0);
        $total       = CwmcountHelper::getTotalCount('#__bsms_studies');

        $this->assertLessThanOrEqual(
            $total,
            $unpublished,
            'Unpublished count should be a subset of the non-trashed total'
        );
    }

    #[TestDox('Published, unpublished and archived are counted independently')]
    public function testStateCountsAreIndependent(): void
    {
        // Each state is a distinct, separately-cached query; their sum cannot
        // exceed the non-trashed total (which excludes only state -2).
        $published   = CwmcountHelper::getCountByState('#__bsms_studies', 1);
        $unpublished = CwmcountHelper::getCountByState('#__bsms_studies', 0);
        $archived    = CwmcountHelper::getCountByState('#__bsms_studies', 2);
        $total       = CwmcountHelper::getTotalCount('#__bsms_studies');

        $this->assertLessThanOrEqual(
            $total,
            $published + $unpublished + $archived,
            'Sum of published/unpublished/archived must not exceed the non-trashed total'
        );
    }
}

<?php

/**
 * Cwmstats filters its aggregates by a date range bound into the query. The
 * range comes in as a parameter (an admin date filter), so it is exactly the
 * kind of value that should be bound rather than interpolated. These run the
 * date-filtered paths against the real DB so a mis-bound placeholder throws.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmstatsDateBindTest extends IntegrationTestCase
{
    /**
     * @var User|null
     * @since __DEPLOY_VERSION__
     */
    private ?User $savedIdentity = null;

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

        // These stats methods read the current identity for the access filter;
        // the harness has none, so stand in a guest and restore afterwards.
        $app                 = Factory::getApplication();
        $this->savedIdentity = $app->getIdentity();
        $app->loadIdentity(new User());
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        if ($this->savedIdentity !== null) {
            Factory::getApplication()->loadIdentity($this->savedIdentity);
        }

        parent::tearDown();
    }

    /**
     * A fresh range differs from the cached one, so the guard re-runs the query
     * instead of returning a memoised count -- which is what reaches the bind.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('getTotalMessages()/getTotalTopics() date-range filter executes with bound values')]
    public function testDateRangeBindsExecute(): void
    {
        $start = '2000-01-01 00:00:00';
        $end   = '2035-01-01 00:00:00';

        // Both throw "Unknown column 's.time'" before the fix, and throw on a
        // mis-bound :start/:end after it — a clean run returns a count.
        $this->assertIsInt(Cwmstats::getTotalMessages($start, $end));
        $this->assertIsInt(Cwmstats::getTotalTopics($start, $end));
    }
}

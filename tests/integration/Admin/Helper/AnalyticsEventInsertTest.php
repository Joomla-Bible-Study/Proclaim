<?php

/**
 * Integration test executing the analytics event INSERT.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmanalyticsHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * logEvent()'s INSERT moved to bound parameters, and none of the existing
 * analytics tests reach it — proven by mutating a bind out and watching the
 * suite stay green. logEvent() swallows every exception so a page is never
 * broken by analytics, which means a bind mistake here is a silent end to
 * event collection: the worst failure is the one nothing reports.
 *
 * So this drives the real thing and reads the row back, including the
 * columns the old code spelled as "or NULL" ternaries — a null local must
 * arrive as SQL NULL, not the string that PHP would coerce.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmanalyticsHelper::class)]
class AnalyticsEventInsertTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);

        // The classifier reads the request environment straight from
        // superglobals; give it one with the characters quoting exists for.
        $_SERVER['HTTP_USER_AGENT']      = "Mozilla/5.0 (Macintosh; Intel Mac OS X) Test's Agent";
        $_SERVER['HTTP_REFERER']         = "https://search.example.org/find?q=o'brien";
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
    }

    protected function tearDown(): void
    {
        unset($_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTP_REFERER'], $_SERVER['HTTP_ACCEPT_LANGUAGE']);

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();
    }

    #[TestDox('logEvent writes a row, and the nullable columns arrive as real NULLs')]
    public function testEventRowLands(): void
    {
        $before = $this->eventCount();

        CwmanalyticsHelper::logEvent('page_view', 987654);

        $after = $this->eventCount();

        // ⚠️ The assertion that cannot be argued with: logEvent swallows all
        // exceptions, so "no new row" is the only visible symptom of a broken
        // INSERT — which is precisely why it must be asserted rather than
        // assumed. If this fails with no obvious cause, the gates at the top
        // of logEvent (analytics_enabled, consent) are worth checking first.
        $this->assertSame($before + 1, $after, 'logEvent() wrote nothing. Its INSERT died silently.');

        $row = $this->db->setQuery(
            $this->db->createQuery()
                ->select('*')
                ->from($this->db->quoteName('#__bsms_analytics_events'))
                ->where($this->db->quoteName('study_id') . ' = 987654')
        )->loadObject();

        $this->assertNotNull($row);
        $this->assertSame('page_view', $row->event_type);
        $this->assertSame('desktop', $row->device_type);

        // No UTM parameters in this request: the old ternaries wrote NULL and
        // the binds must too — a PHP null coerced to '' would satisfy a
        // looser assertion and silently change every UTM breakdown.
        $this->assertNull($row->utm_source, 'An absent UTM value must land as SQL NULL.');
        $this->assertNull($row->utm_campaign);
    }

    private function eventCount(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_analytics_events'))
        )->loadResult();
    }
}

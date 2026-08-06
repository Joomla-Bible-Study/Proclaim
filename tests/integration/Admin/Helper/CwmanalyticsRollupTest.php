<?php

/**
 * Integration tests for the analytics referrer parsing and monthly rollup.
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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1571.
 *
 * 1. ltrim($host, 'www.') treats its second argument as a character mask, not
 *    a prefix, so any host starting with 'w' lost leading characters --
 *    'worship.example.org' became 'orship.example.org'. That corrupted the
 *    stored referrer_domain column and the internal-vs-external host
 *    comparison in classifyReferrer().
 *
 * 2. rollupAndPurge() ran its aggregate INSERT and its purge DELETE as
 *    independent statements. A task killed between them left the raw events in
 *    place, so the next run aggregated them again and inflated the monthly
 *    totals permanently.
 *
 * 3. uq_aggregate spans five NULLable columns and MySQL treats NULL as
 *    distinct from NULL in a unique index, so ON DUPLICATE KEY UPDATE never
 *    fired and each run inserted duplicate rows instead of consolidating.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmanalyticsHelper::class)]
class CwmanalyticsRollupTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    private int $eventCountAtStart = 0;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();

        $this->eventCountAtStart = $this->countEvents();
    }

    protected function tearDown(): void
    {
        $leaked = null;

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();

                // Verify the rollback actually restored the table rather than
                // trusting that it did. This class drives a method whose whole
                // job is to DELETE rows, so a broken isolation boundary
                // destroys real data silently -- which is exactly what
                // happened: an earlier bare transactionStart() inside
                // rollupAndPurge() implicitly committed this transaction (MySQL
                // commits on BEGIN) and permanently deleted 74 events from a
                // developer database. Fail loudly instead.
                $after = $this->countEvents();

                if ($after !== $this->eventCountAtStart) {
                    $leaked = \sprintf(
                        'Test isolation broke: #__bsms_analytics_events went from %d to %d rows and the '
                        . 'rollback did not restore them. Rows have been permanently deleted.',
                        $this->eventCountAtStart,
                        $after
                    );
                }
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        parent::tearDown();

        if ($leaked !== null) {
            $this->fail($leaked);
        }
    }

    /**
     * @return  int  Total rows in the raw events table.
     */
    private function countEvents(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_analytics_events'))
        )->loadResult();
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- www. prefix stripping
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function hostProvider(): array
    {
        return [
            // The actual bug: a leading 'w' was eaten by the character mask.
            'worship subdomain' => ['worship.example.org', 'worship.example.org'],
            'watch subdomain'   => ['watch.church.tv', 'watch.church.tv'],
            'wordoflife domain' => ['wordoflife.org', 'wordoflife.org'],
            'wesleyan domain'   => ['wesleyan.org', 'wesleyan.org'],
            // Still has to do its actual job.
            'www stripped'       => ['www.example.org', 'example.org'],
            'www with subdomain' => ['www.church.example.org', 'church.example.org'],
            // Untouched cases.
            'no prefix' => ['example.org', 'example.org'],
            'empty'     => ['', ''],
            // 'www' without the dot is not the prefix and must survive intact.
            'wwwfoo not a prefix' => ['wwwfoo.org', 'wwwfoo.org'],
        ];
    }

    #[DataProvider('hostProvider')]
    #[TestDox('Only a literal leading "www." is stripped from a hostname')]
    public function testStripWwwPrefix(string $host, string $expected): void
    {
        $this->assertSame(
            $expected,
            CwmanalyticsHelper::stripWwwPrefix($host),
            'ltrim() used a character mask here, silently corrupting w-initial domains — see #1571'
        );
    }

    #[TestDox('classifyReferrer reports a w-initial domain uncorrupted')]
    public function testClassifyReferrerKeepsWInitialDomainsIntact(): void
    {
        $result = CwmanalyticsHelper::classifyReferrer('https://worship.example.org/page');

        $this->assertSame(
            'worship.example.org',
            $result['domain'],
            'The stored referrer_domain must match the real host — see #1571'
        );
    }

    // -------------------------------------------------------------------------
    // Findings 2 & 3 -- rollup consolidates and is atomic
    // -------------------------------------------------------------------------

    /**
     * Insert a raw event old enough for the rollup cutoff to include it.
     *
     * @param   string  $type     Event type
     * @param   int     $studyId  Study id (0 = NULL dimension)
     *
     * @return  void
     */
    private function insertOldEvent(string $type = 'page_view', int $studyId = 0): void
    {
        $row = (object) [
            'event_type' => $type,
            'created'    => (new \DateTime('-200 days'))->format('Y-m-d H:i:s'),
        ];

        if ($studyId > 0) {
            $row->study_id = $studyId;
        }

        $this->db->insertObject('#__bsms_analytics_events', $row);
    }

    /**
     * Count monthly rows sharing the all-NULL dimension tuple this fixture
     * produces, for the month the fixture lands in.
     *
     * @return  array{rows: int, total: int}
     */
    private function monthlyStateForFixture(): array
    {
        $when = new \DateTime('-200 days');

        $row = $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*) AS rows_found, COALESCE(SUM(' . $this->db->quoteName('count') . '), 0) AS total')
                ->from($this->db->quoteName('#__bsms_analytics_monthly'))
                ->where($this->db->quoteName('event_type') . ' = ' . $this->db->quote('page_view'))
                ->where($this->db->quoteName('year') . ' = ' . (int) $when->format('Y'))
                ->where($this->db->quoteName('month') . ' = ' . (int) $when->format('n'))
                ->where($this->db->quoteName('study_id') . ' IS NULL')
                ->where($this->db->quoteName('series_id') . ' IS NULL')
                ->where($this->db->quoteName('country_code') . ' IS NULL')
        )->loadObject();

        return ['rows' => (int) $row->rows_found, 'total' => (int) $row->total];
    }

    #[TestDox('Re-running the rollup consolidates instead of inserting duplicate rows')]
    public function testRollupConsolidatesAcrossRuns(): void
    {
        $before = $this->monthlyStateForFixture();

        $this->insertOldEvent();
        $this->insertOldEvent();
        CwmanalyticsHelper::rollupAndPurge(90);

        $afterFirst = $this->monthlyStateForFixture();

        $this->assertSame(
            $before['rows'] + 1,
            $afterFirst['rows'],
            'The first rollup writes exactly one monthly row for this dimension tuple'
        );
        $this->assertSame($before['total'] + 2, $afterFirst['total'], 'Both events must be counted');

        // A second run over fresh events for the same month must update that
        // row, not add another. Pre-fix, uq_aggregate never matched (NULL
        // dimensions) so this produced a second row.
        $this->insertOldEvent();
        $this->insertOldEvent();
        $this->insertOldEvent();
        CwmanalyticsHelper::rollupAndPurge(90);

        $afterSecond = $this->monthlyStateForFixture();

        $this->assertSame(
            $afterFirst['rows'],
            $afterSecond['rows'],
            'A second rollup must not create a duplicate monthly row — uq_aggregate cannot match NULL dimensions, see #1571'
        );
    }

    #[TestDox('Rolled-up raw events are purged in the same operation')]
    public function testRollupPurgesTheRawEventsItAggregated(): void
    {
        $this->insertOldEvent();
        $this->insertOldEvent();

        $cutoff    = (new \DateTime('-90 days'))->format('Y-m-d H:i:s');
        $remaining = fn (): int => (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__bsms_analytics_events'))
                ->where($this->db->quoteName('created') . ' < ' . $this->db->quote($cutoff))
        )->loadResult();

        $this->assertGreaterThan(0, $remaining(), 'Precondition: the fixture events are older than the cutoff');

        CwmanalyticsHelper::rollupAndPurge(90);

        $this->assertSame(
            0,
            $remaining(),
            'Leaving rolled-up events behind is what let the next run double-count them — see #1571'
        );
    }

    #[TestDox('Recent events are left alone by the rollup')]
    public function testRecentEventsAreNotRolledUp(): void
    {
        $row = (object) [
            'event_type' => 'page_view',
            'created'    => (new \DateTime('-1 day'))->format('Y-m-d H:i:s'),
        ];
        $this->db->insertObject('#__bsms_analytics_events', $row);
        $id = (int) $this->db->insertid();

        CwmanalyticsHelper::rollupAndPurge(90);

        $this->assertSame(
            1,
            (int) $this->db->setQuery(
                $this->db->createQuery()
                    ->select('COUNT(*)')
                    ->from($this->db->quoteName('#__bsms_analytics_events'))
                    ->where($this->db->quoteName('id') . ' = ' . $id)
            )->loadResult(),
            'Events inside the retention window must survive'
        );
    }

    // -------------------------------------------------------------------------
    // Purge safety -- a misconfigured retention window must not wipe the table
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{0: int, 1: bool}>
     */
    public static function retentionProvider(): array
    {
        return [
            // (int) "" -- a blank field in the Scheduler UI. The lethal case:
            // cutoff lands on "now", so the DELETE matches every raw event.
            'blank field casts to zero' => [0, false],
            // (int) "abc" is also 0.
            'negative puts cutoff in the future' => [-5, false],
            'one day still below the floor'      => [1, false],
            'six days below the floor'           => [6, false],
            // The form's own min, and the documented default.
            'seven days is the floor'    => [7, true],
            'ninety days is the default' => [90, true],
            'a long window is fine'      => [3650, true],
        ];
    }

    /**
     * Exercised as a pure function deliberately: proving this guard by calling
     * rollupAndPurge(0) would run the real DELETE if the guard were wrong.
     */
    #[DataProvider('retentionProvider')]
    #[TestDox('Only a retention window at or above the floor permits deletion')]
    public function testIsPurgeSafe(int $days, bool $expected): void
    {
        $this->assertSame(
            $expected,
            CwmanalyticsHelper::isPurgeSafe($days),
            'A window of zero or less makes the purge match every row in the table — see #1571'
        );
    }

    #[TestDox('A below-floor retention deletes nothing and reports nothing rolled')]
    public function testUnsafeRetentionIsARefusalNotAWipe(): void
    {
        $this->insertOldEvent();
        $this->insertOldEvent();

        $before = $this->countEvents();
        $result = CwmanalyticsHelper::rollupAndPurge(0);

        $this->assertSame(
            $before,
            $this->countEvents(),
            'A zero-day retention must delete nothing at all — it previously matched the whole table'
        );
        $this->assertSame(0, $result['purged'], 'Nothing was purged, and the return value must say so');
        $this->assertSame(
            0,
            $result['rolled'],
            'The cutoff itself is invalid, so aggregating would inflate the monthly rows too'
        );
    }

    #[TestDox('Disabling the purge keeps the raw events but still aggregates')]
    public function testPurgeCanBeDisabled(): void
    {
        $this->insertOldEvent();
        $this->insertOldEvent();

        $before = $this->countEvents();
        $result = CwmanalyticsHelper::rollupAndPurge(90, false);

        $this->assertSame(
            $before,
            $this->countEvents(),
            'enable_purge=0 was computed then ignored, so "Purge: No" still deleted events — see #1571'
        );
        $this->assertSame(0, $result['purged']);
        $this->assertGreaterThan(0, $result['rolled'], 'Aggregation is non-destructive and should still happen');
    }

    /**
     * With the purge disabled the raw events stay put, so every run re-reads
     * the same rows. Finding 3 arriving through a different door.
     */
    #[TestDox('Re-running with the purge disabled does not double the monthly totals')]
    public function testDisabledPurgeIsIdempotentAcrossRuns(): void
    {
        $this->insertOldEvent();
        $this->insertOldEvent();

        CwmanalyticsHelper::rollupAndPurge(90, false);
        $afterFirst = $this->monthlyStateForFixture();

        CwmanalyticsHelper::rollupAndPurge(90, false);
        $afterSecond = $this->monthlyStateForFixture();

        $this->assertSame(
            $afterFirst['rows'],
            $afterSecond['rows'],
            'The same events must not produce a second monthly row'
        );
        $this->assertSame(
            $afterFirst['total'],
            $afterSecond['total'],
            'Re-aggregating events that were never purged must replace the row, not add to it — see #1571'
        );
    }

    #[TestDox('The rollup brackets both statements in one transaction')]
    public function testRollupIsTransactional(): void
    {
        // A task killed mid-run is not reproducible in-process, so pin the
        // structure: both statements must sit inside one transaction and the
        // failure path must roll back.
        $ref   = new \ReflectionMethod(CwmanalyticsHelper::class, 'rollupAndPurge');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $startAt  = strpos($body, 'transactionStart(true)');
        $purgeAt  = strpos($body, 'delete($db->quoteName(\'#__bsms_analytics_events\')');
        $commitAt = strpos($body, 'transactionCommit(true)');

        $this->assertNotFalse($startAt, 'The rollup must open a transaction — see #1571');
        $this->assertNotFalse($commitAt, '…and commit it');
        $this->assertNotFalse($purgeAt, 'Expected the purge to still be present');

        $this->assertLessThan($purgeAt, $startAt, 'The transaction must open before the purge');
        $this->assertLessThan($commitAt, $purgeAt, 'The commit must come after the purge');
        $this->assertStringContainsString(
            'transactionRollback(true)',
            $body,
            'A failure must roll back, or a partial rollup is left for the next run to double-count'
        );

        // The savepoint flag is load-bearing, not decoration.
        // MysqliDriver::transactionStart() calls begin_transaction()
        // unconditionally without it, and MySQL implicitly commits any
        // in-progress transaction on BEGIN -- so a bare call silently commits
        // a caller's open transaction. That is not hypothetical: it committed
        // this very test class's isolating transaction and leaked fixture rows
        // into the developer database until the flag was added.
        // Match actual calls ($db->...), not the prose above that names the
        // bare form while explaining why it must not be used.
        $this->assertDoesNotMatchRegularExpression(
            '/\$db->transaction(Start|Commit|Rollback)\(\s*\)/',
            $body,
            'Transaction calls must be savepoint-aware so the rollup can nest inside a caller\'s transaction'
        );
    }
}

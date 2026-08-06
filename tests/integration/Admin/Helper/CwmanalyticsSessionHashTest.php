<?php

/**
 * Integration tests for the daily-rotating session hash.
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
 * Regression coverage for #1611.
 *
 * session_hash was a bare hash('sha256', $sessionId): unkeyed, so the digest
 * was reproducible by anyone holding a candidate session ID, and stable
 * forever, so one visitor could be followed across the entire history of the
 * table. Measured against 82,565 real events, it also linked two events in
 * only 1.6% of sessions -- carrying the whole consent burden for almost no
 * analytical return.
 *
 * It is now keyed with the site secret and rotated daily, mirroring the
 * approach Plausible and Fathom use. Within a day the value is stable, so
 * COUNT(DISTINCT session_hash) -- the Sessions KPI -- is unaffected; across
 * days the same visitor is unlinkable.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmanalyticsHelper::class)]
class CwmanalyticsSessionHashTest extends IntegrationTestCase
{
    private const string SESSION = 'abc123sessionidentifier';
    private const string SECRET  = 'site-secret-value';

    // -------------------------------------------------------------------------
    // The rotation property
    // -------------------------------------------------------------------------

    #[TestDox('The same visitor on the same day produces a stable hash')]
    public function testStableWithinADay(): void
    {
        $this->assertSame(
            CwmanalyticsHelper::deriveSessionHash(self::SESSION, self::SECRET, '2026-08-06'),
            CwmanalyticsHelper::deriveSessionHash(self::SESSION, self::SECRET, '2026-08-06'),
            'Sessions counting depends on same-day stability'
        );
    }

    /**
     * The point of the change: history cannot be joined across days.
     */
    #[TestDox('The same visitor on a different day is unlinkable')]
    public function testRotatesAcrossDays(): void
    {
        $day1 = CwmanalyticsHelper::deriveSessionHash(self::SESSION, self::SECRET, '2026-08-06');
        $day2 = CwmanalyticsHelper::deriveSessionHash(self::SESSION, self::SECRET, '2026-08-07');

        $this->assertNotSame(
            $day1,
            $day2,
            'A stable hash let one visitor be traced across the whole table — see #1611'
        );
    }

    #[TestDox('Different visitors on the same day differ')]
    public function testDistinctVisitorsDiffer(): void
    {
        $this->assertNotSame(
            CwmanalyticsHelper::deriveSessionHash('session-one', self::SECRET, '2026-08-06'),
            CwmanalyticsHelper::deriveSessionHash('session-two', self::SECRET, '2026-08-06')
        );
    }

    #[TestDox('The digest is keyed, not a plain SHA-256 of the session id')]
    public function testIsKeyed(): void
    {
        $derived = CwmanalyticsHelper::deriveSessionHash(self::SESSION, self::SECRET, '2026-08-06');

        $this->assertNotSame(
            hash('sha256', self::SESSION),
            $derived,
            'An unkeyed digest is reproducible by anyone holding a candidate session ID — see #1611'
        );

        // A different site secret must yield a different value, or the key is
        // not actually participating.
        $this->assertNotSame(
            $derived,
            CwmanalyticsHelper::deriveSessionHash(self::SESSION, 'another-secret', '2026-08-06'),
            'The site secret must be load-bearing'
        );
    }

    #[TestDox('The stored value never contains the raw session id')]
    public function testRawSessionIdIsNotStored(): void
    {
        $derived = CwmanalyticsHelper::deriveSessionHash(self::SESSION, self::SECRET, '2026-08-06');

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $derived, 'Expected a hex sha256 digest');
        $this->assertStringNotContainsString(self::SESSION, $derived);
    }

    /**
     * An empty secret must not silently degrade to an unkeyed hash. logEvent()
     * stores NULL in that case; this pins that the derivation itself still
     * keys rather than collapsing to hash('sha256', $id).
     */
    #[TestDox('An empty secret does not collapse to an unkeyed digest')]
    public function testEmptySecretDoesNotCollapseToPlainHash(): void
    {
        $this->assertNotSame(
            hash('sha256', self::SESSION),
            CwmanalyticsHelper::deriveSessionHash(self::SESSION, '', '2026-08-06')
        );
    }

    #[TestDox('logEvent leaves the hash NULL when no site secret is available')]
    public function testLogEventRefusesToWriteAnUnkeyedHash(): void
    {
        $ref   = new \ReflectionMethod(CwmanalyticsHelper::class, 'logEvent');
        $lines = file($ref->getFileName());
        $body  = implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));

        $this->assertStringContainsString(
            "\$secret !== ''",
            $body,
            'Without a secret the hash must stay NULL rather than fall back to an unkeyed digest — see #1611'
        );
        $this->assertStringNotContainsString(
            "hash('sha256', \$sessionId)",
            $body,
            'The bare unkeyed digest must not reappear'
        );
    }

    // -------------------------------------------------------------------------
    // The migration must not move any historical Sessions figure
    // -------------------------------------------------------------------------

    /**
     * The 10.5.6 migration re-keys existing rows with
     * SHA2(CONCAT(session_hash, DATE(created)), 256) so old data gains the
     * same cross-day unlinkability. That must leave COUNT(DISTINCT
     * session_hash) untouched per day, or historical Sessions numbers would
     * shift under administrators.
     *
     * Runs against a TEMPORARY table: it exists only for this connection and
     * disappears with it, so there is no path from this test to real rows.
     */
    #[TestDox('Re-keying historical rows preserves the per-day Sessions count')]
    public function testMigrationPreservesPerDaySessionCounts(): void
    {
        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $db = Factory::getContainer()->get(DatabaseDriver::class);

        $db->setQuery(
            'CREATE TEMPORARY TABLE cwm1611_rekey ('
            . ' session_hash VARCHAR(64) NULL, created DATETIME NOT NULL)'
        )->execute();

        // Two visitors, each seen on two days, plus a repeat visit and a NULL.
        $rows = [
            ['aaa', '2026-08-06 09:00:00'],
            ['aaa', '2026-08-06 11:00:00'], // same visitor, same day
            ['aaa', '2026-08-07 09:00:00'], // same visitor, next day
            ['bbb', '2026-08-06 10:00:00'],
            [null,  '2026-08-06 12:00:00'],
        ];

        foreach ($rows as [$hash, $created]) {
            $db->setQuery(
                'INSERT INTO cwm1611_rekey (session_hash, created) VALUES ('
                . ($hash === null ? 'NULL' : $db->quote($hash)) . ', ' . $db->quote($created) . ')'
            )->execute();
        }

        $perDay = static fn (): array => $db->setQuery(
            'SELECT DATE(created) d, COUNT(DISTINCT session_hash) n FROM cwm1611_rekey GROUP BY DATE(created) ORDER BY d'
        )->loadAssocList('d', 'n');

        $before = $perDay();

        $db->setQuery(
            'UPDATE cwm1611_rekey SET session_hash = SHA2(CONCAT(session_hash, DATE(created)), 256)'
            . ' WHERE session_hash IS NOT NULL'
        )->execute();

        $this->assertSame($before, $perDay(), 'Historical Sessions figures must not move — see #1611');

        // And the visitor must no longer be joinable across days.
        $crossDay = (int) $db->setQuery(
            'SELECT COUNT(*) FROM (SELECT session_hash FROM cwm1611_rekey WHERE session_hash IS NOT NULL'
            . ' GROUP BY session_hash HAVING COUNT(DISTINCT DATE(created)) > 1) x'
        )->loadResult();

        $this->assertSame(0, $crossDay, 'No hash may span two days after re-keying');

        // NULLs must survive as NULL rather than becoming a digest of "".
        $this->assertSame(
            1,
            (int) $db->setQuery('SELECT COUNT(*) FROM cwm1611_rekey WHERE session_hash IS NULL')->loadResult(),
            'CONCAT() propagates NULL; a NULL hash must not become a real one'
        );

        $db->setQuery('DROP TEMPORARY TABLE cwm1611_rekey')->execute();
    }
}

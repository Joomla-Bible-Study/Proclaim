<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeQuota;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression tests for #1549, found during a Helper-folder-wide bug-hunting
 * review, tier 2 (external I/O + security surface, sequel to #1537-#1542).
 *
 * No test file existed for this class before.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmyoutubeQuotaTest extends ProclaimTestCase
{
    /**
     * A serverId well outside any real server range, used for every test so
     * fixtures never collide with real quota data on a dev DB.
     */
    private const TEST_SERVER_ID = 999999;

    private string $quotaFile;

    protected function setUp(): void
    {
        parent::setUp();

        // Mirrors the private CwmyoutubeQuota::quotaFilePath() path scheme
        // (siteId = first 8 chars of md5(JPATH_CACHE)) so tests can seed/
        // inspect/corrupt the file directly.
        $siteId          = substr(md5(JPATH_CACHE), 0, 8);
        $dir             = JPATH_ROOT . '/media/com_proclaim/youtube_cache/' . $siteId;
        $this->quotaFile = $dir . '/quota_' . self::TEST_SERVER_ID . '.json';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        CwmyoutubeQuota::resetQuota(self::TEST_SERVER_ID);
    }

    protected function tearDown(): void
    {
        // Restore permissions before deleting -- a 0000-mode file/dir left
        // over from the fail-closed test would otherwise refuse unlink().
        if (is_dir(\dirname($this->quotaFile))) {
            @chmod(\dirname($this->quotaFile), 0755);
        }

        if (file_exists($this->quotaFile)) {
            @chmod($this->quotaFile, 0644);
            @unlink($this->quotaFile);
        }

        CwmyoutubeQuota::resetQuota(self::TEST_SERVER_ID);

        parent::tearDown();
    }

    public function testRecordUsageAccumulatesAcrossCalls(): void
    {
        CwmyoutubeQuota::recordUsage(self::TEST_SERVER_ID, 100);
        CwmyoutubeQuota::recordUsage(self::TEST_SERVER_ID, 50);

        $this->assertSame(150, CwmyoutubeQuota::getUsedToday(self::TEST_SERVER_ID));
    }

    public function testMarkExhaustedSetsUsedToTheDailyBudget(): void
    {
        // No server row exists for this fake ID, so getDailyBudget() falls
        // back to the documented default of 10000.
        CwmyoutubeQuota::markExhausted(self::TEST_SERVER_ID);

        $this->assertSame(10000, CwmyoutubeQuota::getUsedToday(self::TEST_SERVER_ID));
        $this->assertFalse(CwmyoutubeQuota::hasQuota(self::TEST_SERVER_ID, 1));
    }

    public function testHasQuotaReturnsFalseOnceRemainingIsBelowNeededUnits(): void
    {
        CwmyoutubeQuota::recordUsage(self::TEST_SERVER_ID, 9999);

        $this->assertTrue(CwmyoutubeQuota::hasQuota(self::TEST_SERVER_ID, 1));
        $this->assertFalse(CwmyoutubeQuota::hasQuota(self::TEST_SERVER_ID, 2));
    }

    /**
     * The core #1549 regression: a write failure must not silently and
     * permanently disable quota enforcement. Before the fix, hasQuota()
     * derived its answer purely from loadQuotaFile() -- if every write
     * silently no-ops (disk full, permissions), the file never reflects
     * any usage, getUsedToday() stays 0 forever, and hasQuota() is
     * unconditionally true.
     *
     * Reproduced by making the quota file read-only so mutateQuotaFile()'s
     * fopen($file, 'c+') fails to open for writing -- recordUsage() must
     * then flip the in-process fail-closed flag, and hasQuota() must
     * report false even though the (unwritable, stale) file still shows
     * plenty of remaining budget.
     */
    public function testHasQuotaFailsClosedAfterAWriteFailureInsteadOfStayingOpenForever(): void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped('Running as root bypasses filesystem permission checks.');
        }

        file_put_contents($this->quotaFile, json_encode(['date' => 'not-today', 'used' => 0]));
        chmod($this->quotaFile, 0444);

        CwmyoutubeQuota::recordUsage(self::TEST_SERVER_ID, 100);

        $this->assertFalse(
            CwmyoutubeQuota::hasQuota(self::TEST_SERVER_ID, 1),
            'a write failure must fail quota checks closed, not leave them silently permissive -- see #1549'
        );
    }

    /**
     * Corrupt/partial JSON in the quota file (e.g. from a crash mid-write)
     * must not silently look identical to "no usage recorded yet" -- it
     * should still be usable (falls back to a fresh day) but is logged as
     * a distinct condition. This test only asserts the fallback behavior
     * still works safely; the logging itself isn't asserted since
     * CwmyoutubeLogHelper writes to a shared file-based log outside this
     * test's fixture scope.
     */
    public function testCorruptQuotaFileFallsBackToFreshRatherThanCrashing(): void
    {
        file_put_contents($this->quotaFile, '{not valid json');

        $used = CwmyoutubeQuota::getUsedToday(self::TEST_SERVER_ID);

        $this->assertSame(0, $used);
    }

    /**
     * Two sequential recordUsage() calls must both land -- i.e. the second
     * call's read-modify-write must see the first call's write, not a
     * stale pre-write snapshot. This is the structural guarantee that
     * closes the TOCTOU lost-update race: recordUsage() now performs its
     * load+modify+save entirely inside one flock(LOCK_EX)-held critical
     * section (mutateQuotaFile()) instead of two separate file operations.
     */
    public function testSequentialRecordUsageCallsDoNotLoseEitherIncrement(): void
    {
        for ($i = 0; $i < 5; $i++) {
            CwmyoutubeQuota::recordUsage(self::TEST_SERVER_ID, 10);
        }

        $this->assertSame(50, CwmyoutubeQuota::getUsedToday(self::TEST_SERVER_ID));
    }

    public function testResetQuotaClearsUsageBackToZero(): void
    {
        CwmyoutubeQuota::recordUsage(self::TEST_SERVER_ID, 500);
        CwmyoutubeQuota::resetQuota(self::TEST_SERVER_ID);

        $this->assertSame(0, CwmyoutubeQuota::getUsedToday(self::TEST_SERVER_ID));
    }

    /**
     * The quota-day boundary is meant to reflect YouTube's actual reset
     * (midnight Pacific Time), which the fix now derives from the real
     * America/Los_Angeles timezone rather than a manually-configured UTC
     * offset. Sanity-checks the format and that it's a real, parseable
     * calendar date -- exact value depends on "now", which the test can't
     * freeze (Date::now()-style calls aren't available in this harness).
     */
    public function testGetUsedTodayIsScopedToTheCurrentPacificTimeQuotaDay(): void
    {
        CwmyoutubeQuota::recordUsage(self::TEST_SERVER_ID, 1);

        $this->assertSame(1, CwmyoutubeQuota::getUsedToday(self::TEST_SERVER_ID));

        $raw  = json_decode(file_get_contents($this->quotaFile), true);
        $date = \DateTimeImmutable::createFromFormat('Y-m-d', $raw['date'] ?? '');

        $this->assertNotFalse($date, 'stored quota date must be a valid Y-m-d string');
    }
}

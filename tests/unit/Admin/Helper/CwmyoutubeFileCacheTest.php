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

use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeFileCache;
use CWM\Component\Proclaim\Administrator\Helper\CwmyoutubeLogHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Regression tests for #1551, found during a Helper-folder-wide bug-hunting
 * review, tier 2 (external I/O + security surface, sequel to #1537-#1542).
 *
 * No test file existed for this class before.
 *
 * @since  10.5.6
 */
class CwmyoutubeFileCacheTest extends ProclaimTestCase
{
    /**
     * A server id well outside any real server range, so the fixture never collides
     * with a real cache file.
     *
     * @var    int
     * @since  10.5.6
     */
    private const TEST_SERVER_ID = 999999;

    /**
     * This site's YouTube cache directory.
     *
     * @var    string
     * @since  10.5.6
     */
    private string $cacheDir;

    /**
     * Point at this site's cache directory, remove it, and clear the YouTube log.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    protected function setUp(): void
    {
        parent::setUp();

        $siteId         = substr(md5(JPATH_CACHE), 0, 8);
        $this->cacheDir = JPATH_ROOT . '/media/com_proclaim/youtube_cache/' . $siteId;

        // Start from a clean slate so ensureDir() deterministically hits its
        // "directory did not exist yet" branch in the access-control test.
        $this->removeCacheDir();

        CwmyoutubeLogHelper::clear();
    }

    /**
     * Restore the permissions a write-failure test may have removed, then delete the cache directory.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    protected function tearDown(): void
    {
        // Restore permissions before deleting -- a 0000-mode fixture left
        // over from a fail-write test would otherwise refuse removal.
        if (is_dir($this->cacheDir)) {
            @chmod($this->cacheDir, 0755);
        }

        $file = $this->cacheDir . '/last_video_' . self::TEST_SERVER_ID . '.json';

        if (file_exists($file)) {
            @chmod($file, 0644);
        }

        $this->removeCacheDir();

        parent::tearDown();
    }

    /**
     * Delete the cache directory, its files and its now-empty parent directories.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    private function removeCacheDir(): void
    {
        if (!is_dir($this->cacheDir)) {
            return;
        }

        foreach (scandir($this->cacheDir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            @chmod($this->cacheDir . '/' . $entry, 0644);
            @unlink($this->cacheDir . '/' . $entry);
        }

        @rmdir($this->cacheDir);

        // Also drop the now-empty parents. A leftover media/com_proclaim/ makes
        // cwm-verify read this flat-layout component as namespaced, so every dev
        // site's media symlink is reported as a conflict. @rmdir is a no-op on a
        // directory that still has contents.
        @rmdir(\dirname($this->cacheDir));
        @rmdir(\dirname($this->cacheDir, 2));
    }

    /**
     * A stored last-known video can be read back.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testStoreAndRetrieveLastKnownVideo(): void
    {
        CwmyoutubeFileCache::storeLastKnownVideo(self::TEST_SERVER_ID, ['id' => 'abc123', 'title' => 'Test']);

        $video = CwmyoutubeFileCache::getLastKnownVideo(self::TEST_SERVER_ID);

        $this->assertSame('abc123', $video['id'] ?? null);
    }

    /**
     * A cached video can be read back while its TTL lasts.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testStoreAndRetrieveVideoCacheWithinTtl(): void
    {
        CwmyoutubeFileCache::storeVideoCache('test-key', ['id' => 'xyz789'], 300);

        $video = CwmyoutubeFileCache::getVideoCache('test-key');

        $this->assertSame('xyz789', $video['id'] ?? null);
    }

    /**
     * A stored scheduled start time can be read back.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testStoreAndRetrieveScheduledStart(): void
    {
        CwmyoutubeFileCache::storeScheduledStart(self::TEST_SERVER_ID, 'vid123', '2026-08-05T12:00:00Z');

        $this->assertSame(
            '2026-08-05T12:00:00Z',
            CwmyoutubeFileCache::getStoredScheduledStart(self::TEST_SERVER_ID, 'vid123')
        );
    }

    /**
     * The cache directory (quota counters, cached video metadata,
     * scheduled-stream times) sat inside the public webroot with no
     * .htaccess/web.config -- unlike the sibling media/backup/ convention
     * -- meaning its contents were directly web-servable with no auth
     * check. ensureDir() must now write deny-all access files the first
     * time it creates the directory. See #1551.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testEnsureDirWritesAccessDenyFilesOnFirstCreation(): void
    {
        $this->assertDirectoryDoesNotExist($this->cacheDir, 'setUp() must have removed the cache dir first');

        // Any public method that calls ensureDir() triggers the creation.
        CwmyoutubeFileCache::storeLastKnownVideo(self::TEST_SERVER_ID, ['id' => 'trigger']);

        $this->assertFileExists($this->cacheDir . '/.htaccess');
        $this->assertStringContainsString('Deny from all', file_get_contents($this->cacheDir . '/.htaccess'));
        $this->assertStringContainsString('Require all denied', file_get_contents($this->cacheDir . '/.htaccess'));

        $this->assertFileExists($this->cacheDir . '/web.config');
    }

    /**
     * A write failure to the last-resort "always show a video" fallback
     * cache must not vanish silently -- the class docblock promises this
     * cache "ensures module can always show a video instead of 'no video
     * available'"; if a write failure isn't even logged, an admin has no
     * way to discover why that guarantee stopped holding. See #1551.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    public function testStoreLastKnownVideoLogsOnWriteFailure(): void
    {
        if (posix_getuid() === 0) {
            $this->markTestSkipped('Running as root bypasses filesystem permission checks.');
        }

        CwmyoutubeFileCache::storeLastKnownVideo(self::TEST_SERVER_ID, ['id' => 'first']);

        $file = $this->cacheDir . '/last_video_' . self::TEST_SERVER_ID . '.json';
        chmod($file, 0444);

        CwmyoutubeFileCache::storeLastKnownVideo(self::TEST_SERVER_ID, ['id' => 'second']);

        $entries = CwmyoutubeLogHelper::getEntries(10, CwmyoutubeLogHelper::LEVEL_ERROR);
        $found   = false;

        foreach ($entries as $entry) {
            if (str_contains($entry['message'] ?? '', 'last-known-video')) {
                $found = true;

                break;
            }
        }

        $this->assertTrue($found, 'a write failure must be logged, not silently dropped -- see #1551');
    }

    /**
     * Structural guard: getLastKnownVideo(), getVideoCache(), and
     * getStoredScheduledStart() must read under the same shared-lock
     * pattern getSearchThrottle() already used correctly, instead of a
     * bare file_get_contents() that can observe a torn write. Verified by
     * source inspection since a true concurrent-write race isn't
     * reproducible deterministically in a single-threaded test.
     *
     * @return  void
     *
     * @since   10.5.6
     * @throws  \ReflectionException
     */
    public function testReadMethodsUseTheSharedLockHelperNotBareFileGetContents(): void
    {
        $reflection = new \ReflectionClass(CwmyoutubeFileCache::class);
        $lines      = file($reflection->getFileName());

        foreach (['getLastKnownVideo', 'getVideoCache', 'getStoredScheduledStart'] as $method) {
            $m    = $reflection->getMethod($method);
            $body = implode(
                '',
                \array_slice($lines, $m->getStartLine() - 1, $m->getEndLine() - $m->getStartLine() + 1)
            );

            $this->assertStringContainsString(
                'readWithSharedLock',
                $body,
                "{$method}() must read via readWithSharedLock() -- see #1551"
            );
            $this->assertStringNotContainsString(
                '@file_get_contents(',
                $body,
                "{$method}() must not use a bare file_get_contents() -- see #1551"
            );
        }
    }
}

<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * File-based persistent cache for YouTube API data.
 *
 * Centralizes all file-cache operations that were previously scattered
 * across the YouTube module helper. This allows the admin addon, scheduled
 * tasks, and the module to share the same cache files.
 *
 * Cache directory: JPATH_ROOT/media/com_proclaim/youtube_cache/
 *
 * Stored outside JPATH_CACHE so files survive Joomla "Clear Cache" actions.
 * Throttle, quota, and last-known-video files are critical for quota
 * protection — wiping them causes immediate API call spikes.
 *
 * @since  10.2.0
 */
class CwmyoutubeFileCache
{
    /**
     * Cache directory relative to JPATH_ROOT.
     *
     * Outside JPATH_CACHE so Joomla cache clearing doesn't wipe
     * quota-critical files (throttle, quota counter, last known video).
     *
     * @since  10.2.0
     */
    private const CACHE_DIR = '/media/com_proclaim/youtube_cache';

    /**
     * Store the last successfully fetched video in a persistent file cache.
     *
     * Survives Joomla cache clears and quota exhaustion, ensuring the
     * module can always show a video instead of "no video available."
     *
     * @param   int    $serverId  Server ID
     * @param   array  $video     Video data array
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public static function storeLastKnownVideo(int $serverId, array $video): void
    {
        self::ensureDir();

        $file = self::dir() . '/last_video_' . $serverId . '.json';
        $data = [
            'video'    => $video,
            'storedAt' => time(),
        ];

        try {
            @file_put_contents($file, json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES), LOCK_EX);
        } catch (\JsonException $e) {
            // Video data contained unencodable values — skip caching
        }
    }

    /**
     * Retrieve the last known good video from the persistent file cache.
     *
     * No TTL — the file persists until overwritten by a newer successful fetch.
     *
     * @param   int  $serverId  Server ID
     *
     * @return  array|null  Video data array or null if not found
     *
     * @since   10.2.0
     */
    public static function getLastKnownVideo(int $serverId): ?array
    {
        $file = self::dir() . '/last_video_' . $serverId . '.json';

        if (!file_exists($file)) {
            return null;
        }

        $raw = @file_get_contents($file);

        if ($raw === false) {
            return null;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return null;
        }

        return $data['video'] ?? null;
    }

    /**
     * Store a scheduled start time in a persistent file cache.
     *
     * Joomla's output cache can be flushed at any time, and there may not be
     * a linked Proclaim media record when an upcoming stream is first detected.
     * This file-based store survives cache clears and persists for 24 hours.
     *
     * @param   int     $serverId           Server ID
     * @param   string  $videoId            YouTube video ID
     * @param   string  $scheduledStartTime ISO 8601 scheduled start time
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public static function storeScheduledStart(int $serverId, string $videoId, string $scheduledStartTime): void
    {
        self::ensureDir();

        $file = self::dir() . '/schedule_' . $serverId . '_' . self::sanitizeId($videoId) . '.json';
        $data = [
            'scheduledStartTime' => $scheduledStartTime,
            'storedAt'           => time(),
        ];

        try {
            @file_put_contents($file, json_encode($data, JSON_THROW_ON_ERROR), LOCK_EX);
        } catch (\JsonException $e) {
            // Should never happen with string data — skip caching
        }
    }

    /**
     * Retrieve a cached scheduled start time from the persistent file store.
     *
     * Returns the ISO 8601 time string if found and not expired (24h TTL),
     * or an empty string if missing/expired.
     *
     * @param   int     $serverId  Server ID
     * @param   string  $videoId   YouTube video ID
     *
     * @return  string  Scheduled start time or empty string
     *
     * @since   10.2.0
     */
    public static function getStoredScheduledStart(int $serverId, string $videoId): string
    {
        $file = self::dir() . '/schedule_' . $serverId . '_' . self::sanitizeId($videoId) . '.json';

        if (!file_exists($file)) {
            return '';
        }

        $raw = @file_get_contents($file);

        if ($raw === false) {
            return '';
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            @unlink($file);

            return '';
        }

        if (empty($data['scheduledStartTime'])) {
            return '';
        }

        // 24-hour TTL
        if (isset($data['storedAt']) && (time() - $data['storedAt']) > 86400) {
            @unlink($file);

            return '';
        }

        return $data['scheduledStartTime'];
    }

    /**
     * Set a search throttle to prevent repeated expensive search.list calls.
     *
     * When search.list returns no live/upcoming streams, we record the time
     * so subsequent calls within the TTL window skip the search entirely.
     * This saves 200 quota units (2 x search.list) per throttle hit.
     *
     * Uses exclusive file locking to prevent race conditions where concurrent
     * requests could both miss the throttle and trigger parallel searches.
     *
     * @param   int  $serverId  Server ID
     * @param   int  $ttl       Throttle duration in seconds (default 3600 = 1 hour)
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public static function setSearchThrottle(int $serverId, int $ttl = 3600): void
    {
        self::ensureDir();

        $file = self::dir() . '/search_throttle_' . $serverId . '.json';
        $data = [
            'timestamp' => time(),
            'ttl'       => $ttl,
        ];

        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return;
        }

        // Use fopen + flock for atomic read-write (prevents race conditions)
        $fp = @fopen($file, 'c');

        if ($fp === false) {
            return;
        }

        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $json);
            fflush($fp);
            flock($fp, LOCK_UN);
        }

        fclose($fp);
    }

    /**
     * Get the current search throttle data for a server.
     *
     * Uses shared file locking to prevent reading while a write is in progress.
     *
     * @param   int  $serverId  Server ID
     *
     * @return  array|null  Throttle data with 'timestamp' and 'ttl', or null if not throttled
     *
     * @since   10.2.0
     */
    public static function getSearchThrottle(int $serverId): ?array
    {
        $file = self::dir() . '/search_throttle_' . $serverId . '.json';

        if (!file_exists($file)) {
            return null;
        }

        // Use fopen + flock for consistent reads (prevents torn reads during writes)
        $fp = @fopen($file, 'r');

        if ($fp === false) {
            return null;
        }

        $raw = '';

        if (flock($fp, LOCK_SH)) {
            $raw = stream_get_contents($fp);
            flock($fp, LOCK_UN);
        }

        fclose($fp);

        if ($raw === '' || $raw === false) {
            return null;
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            @unlink($file);

            return null;
        }

        if (!\is_array($data) || empty($data['timestamp'])) {
            return null;
        }

        return $data;
    }

    /**
     * Clear the search throttle (e.g. when a live/upcoming stream is found).
     *
     * @param   int  $serverId  Server ID
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public static function clearSearchThrottle(int $serverId): void
    {
        $file = self::dir() . '/search_throttle_' . $serverId . '.json';

        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Scrub the cache directory: remove expired and stale files.
     *
     * Cleans up:
     * - Expired search throttle files (past their TTL)
     * - Expired schedule files (past 24h TTL)
     * - Quota files for past dates (no longer relevant)
     * - Any corrupted JSON files
     *
     * Safe to call periodically (e.g., from a scheduled task or on admin load).
     *
     * @return  array{removed: int, kept: int}  Cleanup statistics
     *
     * @since   10.2.0
     */
    public static function scrub(): array
    {
        $dir = self::dir();

        if (!is_dir($dir)) {
            return ['removed' => 0, 'kept' => 0];
        }

        $files   = @scandir($dir);
        $removed = 0;
        $kept    = 0;

        if ($files === false) {
            return ['removed' => 0, 'kept' => 0];
        }

        $now = time();

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . '/' . $file;

            if (!is_file($path)) {
                continue;
            }

            $shouldRemove = false;

            // Read and validate JSON
            $raw = @file_get_contents($path);

            if ($raw === false || $raw === '') {
                $shouldRemove = true;
            } else {
                try {
                    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException $e) {
                    // Corrupted JSON — remove
                    $shouldRemove = true;
                    $data         = null;
                }

                if (\is_array($data)) {
                    // Search throttle: remove if expired
                    if (str_starts_with($file, 'search_throttle_')) {
                        $age = $now - ($data['timestamp'] ?? 0);
                        $ttl = (int) ($data['ttl'] ?? 3600);

                        if ($age > $ttl * 2) {
                            $shouldRemove = true;
                        }
                    }

                    // Schedule files: remove if past 48h (2× the 24h TTL)
                    if (str_starts_with($file, 'schedule_')) {
                        $age = $now - ($data['storedAt'] ?? 0);

                        if ($age > 172800) {
                            $shouldRemove = true;
                        }
                    }

                    // Quota files: remove if date is more than 2 days old
                    if (str_starts_with($file, 'quota_') && !empty($data['date'])) {
                        try {
                            $quotaDate = new \DateTimeImmutable($data['date']);
                            $daysOld   = (int) $quotaDate->diff(new \DateTimeImmutable('now'))->days;

                            if ($daysOld > 2) {
                                $shouldRemove = true;
                            }
                        } catch (\Exception $e) {
                            $shouldRemove = true;
                        }
                    }

                    // Last-known-video: keep unless very old (30 days)
                    if (str_starts_with($file, 'last_video_')) {
                        $age = $now - ($data['storedAt'] ?? 0);

                        if ($age > 2592000) {
                            $shouldRemove = true;
                        }
                    }
                }
            }

            if ($shouldRemove) {
                @unlink($path);
                $removed++;
            } else {
                $kept++;
            }
        }

        return ['removed' => $removed, 'kept' => $kept];
    }

    /**
     * Get the absolute path to the cache directory.
     *
     * @return  string
     *
     * @since   10.2.0
     */
    private static function dir(): string
    {
        // Namespace by site identity so symlinked dev installs don't
        // share quota counters. In production each site has its own
        // media directory, so the hash is just extra safety.
        static $dir = null;

        if ($dir === null) {
            $siteId = substr(md5(JPATH_CACHE), 0, 8);
            $dir    = JPATH_ROOT . self::CACHE_DIR . '/' . $siteId;
        }

        return $dir;
    }

    /**
     * Migrate files from the old JPATH_CACHE location to the new location.
     *
     * Called once during ensureDir() to preserve existing throttle/quota files
     * after the upgrade that moved the cache directory.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    private static function migrateFromOldLocation(): void
    {
        $oldDir = JPATH_CACHE . '/mod_proclaim_youtube';
        $newDir = self::dir();

        if (!is_dir($oldDir)) {
            return;
        }

        $files = @scandir($oldDir);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $oldPath = $oldDir . '/' . $file;
            $newPath = $newDir . '/' . $file;

            // Only migrate if the file doesn't already exist in the new location
            if (is_file($oldPath) && !file_exists($newPath)) {
                @rename($oldPath, $newPath);
            }
        }

        // Clean up old directory
        @rmdir($oldDir);
    }

    /**
     * Ensure the cache directory exists.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    private static function ensureDir(): void
    {
        $dir = self::dir();

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException(\sprintf('Directory "%s" was not created', $dir));
        }

        // One-time migration from old JPATH_CACHE location
        static $migrated = false;

        if (!$migrated) {
            $migrated = true;
            self::migrateFromOldLocation();
        }
    }

    /**
     * Sanitize a video ID for use in filenames.
     *
     * @param   string  $id  Video ID
     *
     * @return  string  Sanitized ID
     *
     * @since   10.2.0
     */
    private static function sanitizeId(string $id): string
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '', $id);
    }
}

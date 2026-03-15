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
 * Cache directory: JPATH_CACHE/mod_proclaim_youtube/
 *
 * @since  10.2.0
 */
class CwmyoutubeFileCache
{
    /**
     * Cache directory relative to JPATH_CACHE.
     *
     * @since  10.2.0
     */
    private const CACHE_DIR = '/mod_proclaim_youtube';

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
     * @param   int  $serverId  Server ID
     * @param   int  $ttl       Throttle duration in seconds (default 900 = 15 min)
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public static function setSearchThrottle(int $serverId, int $ttl = 900): void
    {
        self::ensureDir();

        $file = self::dir() . '/search_throttle_' . $serverId . '.json';
        $data = [
            'timestamp' => time(),
            'ttl'       => $ttl,
        ];

        try {
            @file_put_contents($file, json_encode($data, JSON_THROW_ON_ERROR), LOCK_EX);
        } catch (\JsonException $e) {
            // Should never happen with scalar data — skip caching
        }
    }

    /**
     * Get the current search throttle data for a server.
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

        $raw = @file_get_contents($file);

        if ($raw === false) {
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
     * Get the absolute path to the cache directory.
     *
     * @return  string
     *
     * @since   10.2.0
     */
    private static function dir(): string
    {
        return JPATH_CACHE . self::CACHE_DIR;
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

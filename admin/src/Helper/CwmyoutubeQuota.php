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

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Shared YouTube API quota tracker.
 *
 * Both the frontend live-status polling (mod_proclaim_youtube) and the
 * backend platform-stats task (proclaim.platformstats) draw from the same
 * YouTube Data API v3 daily quota.  This class provides a file-based
 * counter that both systems read/write so neither can starve the other.
 *
 * The daily budget and reset hour are configured per-server on the YouTube
 * server record (youtube_daily_quota and youtube_quota_reset_hour params).
 *
 * Quota file location: JPATH_CACHE/mod_proclaim_youtube/quota_{serverId}.json
 *
 * YouTube API costs (v3):
 *   search.list  = 100 units per call
 *   videos.list  =   1 unit  per call
 *   channels.list =  1 unit  per call
 *
 * @since  10.1.0
 */
class CwmyoutubeQuota
{
    /**
     * Known API costs by operation type.
     *
     * @since  10.1.0
     */
    public const COST_SEARCH            = 100;
    public const COST_VIDEOS            = 1;
    public const COST_CHANNELS          = 1;
    public const COST_PLAYLISTS         = 1;
    public const COST_PLAYLIST_ITEMS    = 1;
    public const COST_VIDEO_UPDATE      = 50;
    public const COST_CAPTIONS_LIST     = 50;
    public const COST_CAPTIONS_DOWNLOAD = 200;

    /**
     * In-memory cache for server params to avoid repeated DB queries
     * within the same request.
     *
     * @var    array<int, Registry>
     * @since  10.1.0
     */
    private static array $serverParamsCache = [];

    /**
     * Get the configured daily quota budget from the server's params.
     *
     * Falls back to 10 000 (the YouTube default for new API keys).
     *
     * @param   int  $serverId  YouTube server record ID
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getDailyBudget(int $serverId): int
    {
        $params = self::getServerParams($serverId);

        return max(1, (int) $params->get('youtube_daily_quota', 10000));
    }

    /**
     * Get the UTC hour when the quota resets (YouTube resets at midnight PT).
     *
     * @param   int  $serverId  YouTube server record ID
     *
     * @return  int  0-23
     *
     * @since   10.1.0
     */
    public static function getResetHour(int $serverId): int
    {
        $params = self::getServerParams($serverId);

        return max(0, min(23, (int) $params->get('youtube_quota_reset_hour', 7)));
    }

    /**
     * Record that API units were consumed for a given server.
     *
     * @param   int  $serverId  The YouTube server record ID
     * @param   int  $units     Number of quota units consumed
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function recordUsage(int $serverId, int $units): void
    {
        $data          = self::loadQuotaFile($serverId);
        $currentDate   = self::currentQuotaDate($serverId);

        if (($data['date'] ?? '') !== $currentDate) {
            $data = ['date' => $currentDate, 'used' => 0];
        }

        $data['used'] = ($data['used'] ?? 0) + max(0, $units);

        self::saveQuotaFile($serverId, $data);
    }

    /**
     * Get the number of units already consumed today.
     *
     * @param   int  $serverId  The YouTube server record ID
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getUsedToday(int $serverId): int
    {
        $data = self::loadQuotaFile($serverId);

        if (($data['date'] ?? '') !== self::currentQuotaDate($serverId)) {
            return 0;
        }

        return (int) ($data['used'] ?? 0);
    }

    /**
     * Get the number of units remaining today.
     *
     * @param   int  $serverId  The YouTube server record ID
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getRemaining(int $serverId): int
    {
        return max(0, self::getDailyBudget($serverId) - self::getUsedToday($serverId));
    }

    /**
     * Check whether enough quota remains for a planned operation.
     *
     * @param   int  $serverId    The YouTube server record ID
     * @param   int  $neededUnits Units the operation will consume
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function hasQuota(int $serverId, int $neededUnits = 1): bool
    {
        return self::getRemaining($serverId) >= $neededUnits;
    }

    /**
     * Mark the quota as fully exhausted for the current quota day.
     *
     * Call this when YouTube returns a 403 quotaExceeded error, which means
     * the real quota was consumed by other applications sharing the same API
     * key.  This syncs our local counter to reality so we stop wasting calls.
     *
     * @param   int  $serverId  The YouTube server record ID
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function markExhausted(int $serverId): void
    {
        $budget = self::getDailyBudget($serverId);
        $data   = [
            'date' => self::currentQuotaDate($serverId),
            'used' => $budget,
        ];

        self::saveQuotaFile($serverId, $data);
    }

    /**
     * Check whether an exception message indicates a YouTube quota exceeded error.
     *
     * @param   string  $message  Exception message or error string
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function isQuotaExceededError(string $message): bool
    {
        // Check for Google's specific daily quota error reason
        if (stripos($message, 'quotaExceeded') !== false) {
            // Exclude transient rate limits (rateLimitExceeded, userRateLimitExceeded)
            // which share the 403 status but don't mean daily quota is gone
            if (stripos($message, 'rateLimitExceeded') !== false
                || stripos($message, 'userRateLimitExceeded') !== false) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Reset the local quota counter for a given server.
     *
     * Deletes the quota file so the next check starts fresh at 0.
     * This does not affect actual Google API usage — it only resets
     * the local tracker.
     *
     * @param   int  $serverId  The YouTube server record ID
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function resetQuota(int $serverId): void
    {
        $file = self::quotaFilePath($serverId);

        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Compute the "quota date" string based on the configured reset hour.
     *
     * YouTube resets at midnight Pacific Time (UTC-7 / UTC-8 depending on DST).
     * The reset hour config lets sites align with the actual reset.
     *
     * @param   int  $serverId  YouTube server record ID
     *
     * @return  string  Date string like "2026-02-28"
     *
     * @since   10.1.0
     */
    private static function currentQuotaDate(int $serverId): string
    {
        $resetHour = self::getResetHour($serverId);
        $now       = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        // If we haven't passed the reset hour yet, we're still on "yesterday's" quota day
        if ((int) $now->format('G') < $resetHour) {
            $now = $now->modify('-1 day');
        }

        return $now->format('Y-m-d');
    }

    /**
     * Resolve the path to a server's quota file.
     *
     * @param   int  $serverId  Server ID
     *
     * @return  string
     *
     * @since   10.1.0
     */
    private static function quotaFilePath(int $serverId): string
    {
        return JPATH_ROOT . '/media/com_proclaim/youtube_cache/quota_' . $serverId . '.json';
    }

    /**
     * Load the quota data from the file.
     *
     * @param   int  $serverId  Server ID
     *
     * @return  array{date: string, used: int}
     *
     * @since   10.1.0
     */
    private static function loadQuotaFile(int $serverId): array
    {
        $file = self::quotaFilePath($serverId);

        if (!file_exists($file)) {
            return ['date' => '', 'used' => 0];
        }

        $raw = @file_get_contents($file);

        if ($raw === false) {
            return ['date' => '', 'used' => 0];
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return ['date' => '', 'used' => 0];
        }

        return \is_array($data) ? $data : ['date' => '', 'used' => 0];
    }

    /**
     * Persist the quota data to disk.
     *
     * @param   int    $serverId  Server ID
     * @param   array  $data      Quota data
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private static function saveQuotaFile(int $serverId, array $data): void
    {
        $dir = JPATH_ROOT . '/media/com_proclaim/youtube_cache';

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        @file_put_contents(self::quotaFilePath($serverId), json_encode($data), LOCK_EX);
    }

    /**
     * Load a YouTube server's params from the database (cached per-request).
     *
     * @param   int  $serverId  Server ID
     *
     * @return  Registry
     *
     * @since   10.1.0
     */
    private static function getServerParams(int $serverId): Registry
    {
        if (isset(self::$serverParamsCache[$serverId])) {
            return self::$serverParamsCache[$serverId];
        }

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_servers'))
                ->where($db->quoteName('id') . ' = ' . $serverId);

            $db->setQuery($query);
            $result = $db->loadResult();

            $params = $result ? new Registry($result) : new Registry();
        } catch (\Exception $e) {
            $params = new Registry();
        }

        self::$serverParamsCache[$serverId] = $params;

        return $params;
    }
}

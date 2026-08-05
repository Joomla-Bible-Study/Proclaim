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
 * The daily budget is configured per-server on the YouTube server record
 * (youtube_daily_quota param). The quota-day boundary itself is derived
 * from the real America/Los_Angeles timezone, not a configurable value.
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
    public const COST_PLAYLIST_UPDATE   = 50;
    public const COST_PLAYLIST_INSERT   = 50;
    public const COST_PLAYLIST_DELETE   = 50;
    public const COST_CAPTIONS_LIST     = 50;
    public const COST_CAPTIONS_DOWNLOAD = 200;
    public const COST_BROADCAST_INSERT  = 50;
    public const COST_STREAM_INSERT     = 50;
    public const COST_BROADCAST_BIND    = 50;
    public const COST_BROADCAST_DELETE  = 50;

    /**
     * In-memory cache for server params to avoid repeated DB queries
     * within the same request.
     *
     * @var    array<int, Registry>
     * @since  10.1.0
     */
    private static array $serverParamsCache = [];

    /**
     * Per-server flag set when a write to the quota file has failed during
     * this request. Once set, hasQuota() fails closed (denies further
     * usage) instead of silently behaving as though nothing was consumed.
     *
     * This is process-local, in-memory state -- it resets every request,
     * so a broken disk is only caught after at least one write attempt
     * fails within the current request. That's still a meaningful
     * improvement over the prior behavior (permanently and silently
     * failing open forever): it stops a long-running batch (e.g. the
     * platform-stats sync task, which can make many API calls in one
     * process run) from continuing unprotected after the first failure.
     * See #1549.
     *
     * @var    array<int, bool>
     * @since  __DEPLOY_VERSION__
     */
    private static array $persistenceBroken = [];

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
     * @deprecated __DEPLOY_VERSION__ No longer consulted by currentQuotaDate()
     *             -- the quota-day boundary is now computed directly from the
     *             America/Los_Angeles timezone (DST-aware), since no single
     *             static UTC hour is correct year-round. See #1549. Will be
     *             removed in a future major version.
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
        $currentDate = self::currentQuotaDate();

        $ok = self::mutateQuotaFile($serverId, static function (array $data) use ($currentDate, $units): array {
            if (($data['date'] ?? '') !== $currentDate) {
                $data = ['date' => $currentDate, 'used' => 0];
            }

            $data['used'] = ($data['used'] ?? 0) + max(0, $units);

            return $data;
        });

        if (!$ok) {
            self::$persistenceBroken[$serverId] = true;
        }
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

        if (($data['date'] ?? '') !== self::currentQuotaDate()) {
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
        // A write failure this request means we can no longer trust the
        // local counter to reflect real usage -- fail closed rather than
        // silently letting unlimited calls through. See #1549.
        if (!empty(self::$persistenceBroken[$serverId])) {
            return false;
        }

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
        $budget      = self::getDailyBudget($serverId);
        $currentDate = self::currentQuotaDate();

        $ok = self::mutateQuotaFile($serverId, static function (array $data) use ($currentDate, $budget): array {
            return ['date' => $currentDate, 'used' => $budget];
        });

        if (!$ok) {
            self::$persistenceBroken[$serverId] = true;
        }
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
            // which share status 403 but don't mean daily quota is gone
            return !(stripos($message, 'rateLimitExceeded') !== false
                || stripos($message, 'userRateLimitExceeded') !== false);
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

        unset(self::$persistenceBroken[$serverId]);
    }

    /**
     * Compute the "quota date" string.
     *
     * YouTube resets at midnight Pacific Time. Derived directly from the
     * real IANA timezone (DST-aware) rather than a static UTC "reset hour"
     * -- no single fixed offset is correct for both PST and PDT, and the
     * seasonal boundary itself moves twice a year. Being off by even one
     * hour around that boundary, combined with a genuine quota-exceeded
     * response landing in the mismatched window, could self-amplify into
     * up to ~23 hours of falsely reporting "exhausted" after Google's real
     * quota had already reset. See #1549.
     *
     * @return  string  Date string like "2026-02-28"
     *
     * @throws \DateMalformedStringException
     * @since   10.1.0
     */
    private static function currentQuotaDate(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('America/Los_Angeles')))->format('Y-m-d');
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
        // Namespace by site identity (matches CwmyoutubeFileCache::dir())
        $siteId = substr(md5(JPATH_CACHE), 0, 8);

        return JPATH_ROOT . '/media/com_proclaim/youtube_cache/' . $siteId . '/quota_' . $serverId . '.json';
    }

    /**
     * Load the quota data from the file, using a shared lock so a read
     * cannot observe a torn write from mutateQuotaFile().
     *
     * @param   int  $serverId  Server ID
     *
     * @return  array{date: string, used: int}
     *
     * @throws \JsonException
     * @since   10.1.0
     */
    private static function loadQuotaFile(int $serverId): array
    {
        $file = self::quotaFilePath($serverId);

        if (!file_exists($file)) {
            return ['date' => '', 'used' => 0];
        }

        $fp = @fopen($file, 'rb');

        if ($fp === false) {
            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_ERROR,
                'Quota file could not be opened for reading',
                ['server_id' => $serverId, 'file' => $file]
            );

            return ['date' => '', 'used' => 0];
        }

        $raw = false;

        if (flock($fp, LOCK_SH)) {
            $raw = stream_get_contents($fp);
            flock($fp, LOCK_UN);
        }

        fclose($fp);

        if ($raw === '' || $raw === false) {
            return ['date' => '', 'used' => 0];
        }

        try {
            $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            // File exists but isn't valid JSON -- distinct from "no file
            // yet" and worth surfacing, since it silently drops today's
            // accumulated usage count. See #1549.
            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_WARNING,
                'Quota file contained invalid JSON, resetting to zero',
                ['server_id' => $serverId, 'file' => $file]
            );

            return ['date' => '', 'used' => 0];
        }

        return \is_array($data) ? $data : ['date' => '', 'used' => 0];
    }

    /**
     * Atomically load, mutate, and persist the quota data for a server
     * under a single exclusive lock held for the whole read-modify-write
     * sequence -- prevents the lost-update race where two concurrent
     * recordUsage()/markExhausted() calls both read the same starting
     * value and one silently overwrites the other's increment. See #1549.
     *
     * @param   int       $serverId  Server ID
     * @param   callable  $mutator   array $data -> array $newData
     *
     * @return  bool  True if the write succeeded
     *
     * @throws \JsonException
     * @since   __DEPLOY_VERSION__
     */
    private static function mutateQuotaFile(int $serverId, callable $mutator): bool
    {
        $siteId = substr(md5(JPATH_CACHE), 0, 8);
        $dir    = JPATH_ROOT . '/media/com_proclaim/youtube_cache/' . $siteId;

        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_ERROR,
                'Quota directory could not be created',
                ['server_id' => $serverId, 'dir' => $dir]
            );

            return false;
        }

        $file = self::quotaFilePath($serverId);
        $fp   = @fopen($file, 'cb+');

        if ($fp === false) {
            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_ERROR,
                'Quota file could not be opened for writing',
                ['server_id' => $serverId, 'file' => $file]
            );

            return false;
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);

            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_ERROR,
                'Quota file could not be locked for writing',
                ['server_id' => $serverId, 'file' => $file]
            );

            return false;
        }

        $raw     = stream_get_contents($fp);
        $current = ['date' => '', 'used' => 0];

        if (\is_string($raw) && $raw !== '') {
            try {
                $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

                if (\is_array($decoded)) {
                    $current = $decoded;
                }
            } catch (\JsonException $e) {
                // Corrupt content under the lock -- treat as fresh, same as
                // loadQuotaFile()'s handling outside the lock.
            }
        }

        $new     = $mutator($current);
        $encoded = json_encode($new);
        $written = false;

        if ($encoded !== false) {
            rewind($fp);
            $written = ftruncate($fp, 0) && fwrite($fp, $encoded) !== false;
            fflush($fp);
        }

        flock($fp, LOCK_UN);
        fclose($fp);

        if (!$written) {
            CwmyoutubeLogHelper::log(
                CwmyoutubeLogHelper::LEVEL_ERROR,
                'Quota file write failed',
                ['server_id' => $serverId, 'file' => $file]
            );
        }

        return $written;
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
            $query = $db->createQuery()
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

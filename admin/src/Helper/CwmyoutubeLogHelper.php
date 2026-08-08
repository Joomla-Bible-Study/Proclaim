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
 * Lightweight file-based event logger for YouTube API operations.
 *
 * Stores structured JSON entries (one per line, JSONL format) in
 * JPATH_CACHE/mod_proclaim_youtube/youtube_events.log.
 *
 * Auto-rotates when the file exceeds 500 KB (keeps one backup).
 *
 * @since  10.1.0
 */
class CwmyoutubeLogHelper
{
    /**
     * Log levels.
     *
     * @since  10.1.0
     */
    public const LEVEL_INFO    = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR   = 'error';

    /**
     * Maximum log file size before rotation (500 KB).
     *
     * @since  10.1.0
     */
    private const MAX_FILE_SIZE = 512000;

    /**
     * Log file path relative to JPATH_CACHE.
     *
     * @since  10.1.0
     */
    private const LOG_DIR  = '/mod_proclaim_youtube';
    private const LOG_FILE = '/mod_proclaim_youtube/youtube_events.log';

    /**
     * Append an event to the YouTube log file.
     *
     * @param   string  $level    One of LEVEL_INFO, LEVEL_WARNING, LEVEL_ERROR
     * @param   string  $message  Human-readable event description
     * @param   array   $context  Optional structured data (server_id, quota, etc.)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        $dir = JPATH_CACHE . self::LOG_DIR;

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            error_log(\sprintf('[Proclaim] CwmyoutubeLogHelper: directory "%s" was not created', $dir));

            return;
        }

        $file = JPATH_CACHE . self::LOG_FILE;

        // Auto-rotate before writing
        self::rotate($file);

        try {
            $entry = json_encode([
                'timestamp' => (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('c'),
                'level'     => $level,
                'message'   => $message,
                'context'   => $context,
            ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        } catch (\JsonException $e) {
            error_log(\sprintf('[Proclaim] CwmyoutubeLogHelper: failed to encode log entry: %s', $e->getMessage()));

            return;
        }

        @file_put_contents($file, $entry . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Read the most recent log entries.
     *
     * @param   int          $limit  Maximum entries to return (default 100)
     * @param   string|null  $level  Filter by level (null = all levels)
     *
     * @return  array  Array of decoded log entries, newest first
     *
     * @since   10.1.0
     */
    public static function getEntries(int $limit = 100, ?string $level = null): array
    {
        $file    = JPATH_CACHE . self::LOG_FILE;
        $entries = [];

        // Newest first, so the active file is read before the rotated backup.
        // Reading only the active file meant that the moment rotation fired the
        // admin log viewer went nearly empty, with the recent history sitting
        // unread one file away and nothing to say it existed.
        self::readInto($entries, $file, $limit, $level);

        if (\count($entries) < $limit) {
            self::readInto($entries, $file . '.1', $limit, $level);
        }

        return $entries;
    }

    /**
     * Append entries from one log file, newest first, up to $limit in total.
     *
     * @param   array    $entries  Collected entries, appended to in place.
     * @param   string   $file     Absolute path to a log file.
     * @param   int      $limit    Total number of entries wanted.
     * @param   ?string  $level    Restrict to one level, or null for all.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    private static function readInto(array &$entries, string $file, int $limit, ?string $level): void
    {
        if (!file_exists($file)) {
            return;
        }

        $raw = @file_get_contents($file);

        if ($raw === false || trim($raw) === '') {
            return;
        }

        $lines = explode("\n", trim($raw));

        // Read in reverse order (newest first)
        for ($i = \count($lines) - 1; $i >= 0 && \count($entries) < $limit; $i--) {
            if (empty($lines[$i])) {
                continue;
            }

            try {
                $entry = json_decode($lines[$i], true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                continue;
            }

            if (!\is_array($entry)) {
                continue;
            }

            if ($level !== null && ($entry['level'] ?? '') !== $level) {
                continue;
            }

            $entries[] = $entry;
        }
    }

    /**
     * Clear the log file.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function clear(): void
    {
        $file   = JPATH_CACHE . self::LOG_FILE;
        $backup = $file . '.1';

        if (file_exists($file)) {
            @unlink($file);
        }

        if (file_exists($backup)) {
            @unlink($backup);
        }
    }

    /**
     * Get log file size in bytes.
     *
     * @return  int  File size or 0 if file does not exist
     *
     * @since   10.1.0
     */
    public static function getFileSize(): int
    {
        $file = JPATH_CACHE . self::LOG_FILE;

        if (!file_exists($file)) {
            return 0;
        }

        return (int) @filesize($file);
    }

    /**
     * Rotate the log file if it exceeds the size limit.
     *
     * Renames the current file to .log.1 (overwriting any previous backup).
     *
     * @param   string  $file  Absolute path to the log file
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private static function rotate(string $file): void
    {
        if (!file_exists($file)) {
            return;
        }

        $size = @filesize($file);

        if ($size === false || $size < self::MAX_FILE_SIZE) {
            return;
        }

        $backup = $file . '.1';

        // Overwrite previous backup
        if (@rename($file, $backup)) {
            return;
        }

        // rename() can fail for reasons that have nothing to do with this code
        // -- an NFS mount, an SELinux or AppArmor rule, another process holding
        // the destination open. Suppressed and unchecked, every later log()
        // call re-checked the still-oversized file, re-failed the same rename,
        // and appended anyway, so the file grew without bound and nothing said
        // so.
        //
        // Copy-then-truncate reaches the same end state without renaming.
        if (@copy($file, $backup) && @file_put_contents($file, '') !== false) {
            error_log('Proclaim: YouTube log rotated by copy; rename() failed for ' . $file);

            return;
        }

        // Last resort. This loses the entries, which is worse than rotating
        // them -- but a log that grows until it fills the disk is worse still.
        if (@file_put_contents($file, '') !== false) {
            error_log('Proclaim: YouTube log truncated; could not rotate ' . $file);

            return;
        }

        error_log('Proclaim: YouTube log could not be rotated or truncated: ' . $file);
    }
}

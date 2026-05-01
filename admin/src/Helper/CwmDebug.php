<?php

/**
 * Centralized debug helper for Proclaim.
 *
 * Every public method short-circuits when JBSMDEBUG is off, ensuring
 * zero overhead in production.  Debug output goes to a dedicated log
 * file (com_proclaim.debug.php) and can be flushed to the Joomla
 * message queue for authorized admins via showToAdmin().
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * @since      10.3.0
 */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Centralized debug instrumentation for Proclaim.
 *
 * @since  10.3.0
 */
class CwmDebug
{
    /**
     * Buffered messages for on-screen display.
     *
     * @var string[]
     * @since 10.3.0
     */
    private static array $buffer = [];

    /**
     * Running timers keyed by label.
     *
     * @var array<string, int>  hrtime(true) nanosecond values
     * @since 10.3.0
     */
    private static array $timers = [];

    /**
     * Whether the debug mode is enabled.
     *
     * @return bool
     *
     * @since 10.3.0
     */
    public static function isEnabled(): bool
    {
        return \defined('JBSMDEBUG') && JBSMDEBUG;
    }

    /**
     * Log a debug message to the debug log file and buffer for on-screen display.
     *
     * @param   string  $message   The debug message
     * @param   string  $category  Category label (e.g. 'filter', 'scripture', 'download')
     *
     * @return  void
     *
     * @since 10.3.0
     */
    public static function log(string $message, string $category = 'general'): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $entry = '[' . $category . '] ' . $message;

        Log::add($entry, Log::DEBUG, 'com_proclaim.debug');

        self::$buffer[] = $entry;
    }

    /**
     * Log an error to the component error log file.
     *
     * Unlike log(), this method ALWAYS writes regardless of whether debug
     * mode is enabled.  Use it in catch blocks that currently swallow
     * exceptions silently, so the error is at least captured in the log.
     *
     * @param   string      $message    The error description
     * @param   \Throwable  $throwable  The caught exception/error (optional)
     * @param   string      $category   Category label
     *
     * @return  void
     *
     * @since 10.3.0
     */
    public static function error(string $message, ?\Throwable $throwable = null, string $category = 'general'): void
    {
        $entry = '[' . $category . '] ' . $message;

        if ($throwable !== null) {
            $entry .= ' — ' . $throwable::class . ': ' . $throwable->getMessage();
        }

        Log::add($entry, Log::ERROR, 'com_proclaim');

        // Also buffer for on-screen display when debug is active
        if (self::isEnabled()) {
            self::$buffer[] = 'ERROR: ' . $entry;
        }
    }

    /**
     * Start a named timer.
     *
     * @param   string  $label  A unique label for this timing span
     *
     * @return  void
     *
     * @since 10.3.0
     */
    public static function startTimer(string $label): void
    {
        if (!self::isEnabled()) {
            return;
        }

        self::$timers[$label] = hrtime(true);
    }

    /**
     * Stop a named timer and log the elapsed time.
     *
     * @param   string  $label    The timer label (must match a previous startTimer call)
     * @param   string  $context  Additional context to include in the log message
     *
     * @return  float  Elapsed milliseconds (0.0 if debug is off or timer not found)
     *
     * @since 10.3.0
     */
    public static function stopTimer(string $label, string $context = ''): float
    {
        if (!self::isEnabled() || !isset(self::$timers[$label])) {
            return 0.0;
        }

        $elapsed = (hrtime(true) - self::$timers[$label]) / 1_000_000;
        unset(self::$timers[$label]);

        $msg = $label . ' elapsed=' . round($elapsed, 1) . 'ms';

        if ($context !== '') {
            $msg .= ' ' . $context;
        }

        self::log($msg, 'timer');

        return $elapsed;
    }

    /**
     * Log a SQL query for diagnostic purposes.
     *
     * @param   string  $label  Description of the query
     * @param   mixed   $query  A query object or SQL string
     *
     * @return  void
     *
     * @since 10.3.0
     */
    public static function logQuery(string $label, mixed $query): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $sql = (string) $query;

        // Truncate very long queries for the buffer (full query goes to log file)
        $truncated = \strlen($sql) > 500 ? substr($sql, 0, 500) . '...' : $sql;

        self::log($label . ': ' . $truncated, 'query');
    }

    /**
     * Get the buffered debug messages (e.g. for appending to AJAX responses).
     *
     * @return  string[]
     *
     * @since 10.3.0
     */
    public static function getBuffer(): array
    {
        return self::$buffer;
    }

    /**
     * Flush buffered debug messages to the Joomla message queue.
     *
     * Only outputs if the current user is a super admin on an administrator page.
     * Call this at the end of admin page rendering.
     *
     * @return  void
     *
     * @since 10.3.0
     */
    public static function showToAdmin(): void
    {
        if (!self::isEnabled() || empty(self::$buffer)) {
            return;
        }

        try {
            $app = Factory::getApplication();

            if (!$app->isClient('administrator')) {
                return;
            }

            $user = $app->getIdentity();

            if (!$user || !$user->authorise('core.admin')) {
                return;
            }

            $html = '<strong>Proclaim Debug</strong> (' . \count(self::$buffer) . ' entries)<br>'
                . implode('<br>', array_map('htmlspecialchars', self::$buffer));

            $app->enqueueMessage($html, 'info');
        } catch (\Exception) {
            // Silently ignore — debug output is never critical
        }

        self::$buffer = [];
    }
}

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

        // An unwritable log directory makes FormattedtextLogger::addEntry()
        // throw. Swallow it, matching CwmlogHelper::write(): logging must never
        // be the reason a request fails. The buffer append stays outside the
        // try so a logger failure doesn't also lose the on-screen entry.
        // See #1569.
        try {
            Log::add($entry, Log::DEBUG, 'com_proclaim.debug');
        } catch (\Throwable) {
            // Diagnostics are best-effort.
        }

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

        // This is the call that actually mattered: error() always writes (that
        // is its documented contract), and it is invoked from catch blocks
        // across ~10 call sites that log and then rethrow the ORIGINAL
        // exception -- e.g. CwmaiHelper::postJson(). An unwritable log
        // directory made Log::add() throw RuntimeException('Cannot write to
        // log file.') from inside those catch blocks, replacing the real error
        // with an unrelated one and destroying the diagnostic. Swallow it, as
        // CwmlogHelper::write() already does. See #1569.
        try {
            Log::add($entry, Log::ERROR, 'com_proclaim');
        } catch (\Throwable) {
            // Reporting a failure must not become a different failure.
        }

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

        // Truncated for BOTH the buffer and the log file -- log() receives this
        // same string. A previous comment here claimed the full query still
        // reached the log file, which was never true. Left truncated rather
        // than "fixed" to write complete SQL to disk: com_proclaim.debug.php
        // is web-reachable under some Joomla layouts, and writing more raw SQL
        // there is the wrong direction for a report about SQL exposure.
        // See #1569.
        $truncated = \strlen($sql) > 500 ? substr($sql, 0, 500) . '...' : $sql;

        self::log($label . ': ' . $truncated, 'query');
    }

    /**
     * Log an outbound HTTP/API call for diagnostic purposes.
     *
     * @param   string    $label       Caller label (e.g. 'ai.claude', 'podcast.index')
     * @param   string    $method      HTTP method (GET, POST, HEAD, …)
     * @param   string    $url         Request URL
     * @param   int|null  $statusCode  Response status code, if known
     * @param   float     $elapsedMs   Elapsed milliseconds, if measured
     *
     * @return  void
     *
     * @since 10.3.3
     */
    public static function logApi(
        string $label,
        string $method,
        string $url,
        ?int $statusCode = null,
        float $elapsedMs = 0.0
    ): void {
        if (!self::isEnabled()) {
            return;
        }

        $msg = $method . ' ' . $url;

        if ($statusCode !== null) {
            $msg .= ' -> ' . $statusCode;
        }

        if ($elapsedMs > 0) {
            $msg .= ' (' . round($elapsedMs, 1) . 'ms)';
        }

        self::log($label . ': ' . $msg, 'api');
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
        // Defence in depth alongside the JBSMDEBUG gate in admin/api.php.
        // This accessor had no authorisation of any kind, unlike its sibling
        // showToAdmin(), and its one production consumer
        // (CwmsermonsController::filterAjax) ships the result to the browser
        // as `_debug` on a public, CSRF-token-only endpoint. Returning an
        // empty array rather than throwing keeps that caller's
        // `if (!empty($debugBuffer))` working, so the key is simply omitted.
        // See #1569.
        if (!self::isEnabled()) {
            return [];
        }

        try {
            $app = Factory::getApplication();

            if ($app->isClient('administrator')) {
                return self::$buffer;
            }

            $user = $app->getIdentity();

            if (!$user || !$user->authorise('core.admin', 'com_proclaim')) {
                return [];
            }
        } catch (\Throwable) {
            // No application/identity to authorise against -- disclose nothing.
            return [];
        }

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

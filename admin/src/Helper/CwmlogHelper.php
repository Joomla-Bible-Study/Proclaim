<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Proclaim's operational logging — registration and severity helpers.
 *
 * This is distinct from the action log, and the split is deliberate:
 *
 *   com_actionlogs  the audit trail — who changed what. One entry per content
 *                   change, shown in Users > Action Logs. {@see CwmactionlogHelper}
 *   this class      operational and security events with severity — refused
 *                   requests, configuration failures, diagnostics. Written to log
 *                   files for an administrator or developer to read.
 *
 * Content changes go to the action log only, never both, so an audit never shows
 * the same change twice.
 *
 * WHY REGISTRATION MATTERS
 *
 * Log::addLogEntry() dispatches an entry only to loggers whose configuration
 * matches its category. With no logger registered for a category the entry is
 * built and then silently discarded — logging that looks like it works and does
 * nothing. Proclaim had ~160 Log::add() calls and registered no logger at all, so
 * none of them reached a file. Registering the component's categories here is
 * what makes those existing calls throughout Proclaim actually record.
 *
 * Registration happens in ProclaimComponent::boot(), so anything dispatched
 * through the component is covered without each caller having to think about it.
 * Existing code may keep calling Log::add() with one of the categories below; the
 * convenience methods here additionally guarantee registration for entry points
 * that run before the component boots, such as the webservices plugin during
 * route registration.
 *
 * @since  10.3.4
 */
final class CwmlogHelper
{
    /**
     * General component activity — the category most of Proclaim already uses.
     *
     * @since  10.3.4
     */
    public const CATEGORY_GENERAL = 'com_proclaim';

    /**
     * REST API operational and security events.
     *
     * @since  10.3.4
     */
    public const CATEGORY_API = 'com_proclaim.api';

    /**
     * Bible / scripture lookups.
     *
     * @since  10.3.4
     */
    public const CATEGORY_BIBLE = 'com_proclaim.bible';

    /**
     * Verbose developer diagnostics.
     *
     * @since  10.3.4
     */
    public const CATEGORY_DEBUG = 'com_proclaim.debug';

    /**
     * Category => log file. Separate files keep an API security review from
     * having to wade through template migrations.
     *
     * The first two filenames are inherited from admin/api.php's registration:
     * existing sites already have those files and may have log rotation pointed
     * at them, so the names are kept.
     *
     * @var    array<string, string>
     * @since  10.3.4
     */
    private const FILES = [
        self::CATEGORY_GENERAL => 'com_proclaim.errors.php',
        self::CATEGORY_API     => 'com_proclaim.api.php',
        self::CATEGORY_BIBLE   => 'com_proclaim.bible.php',
    ];

    /**
     * Debug category => file, registered only when debug mode is on.
     *
     * Kept out of FILES so production sites do not accumulate an empty debug log.
     *
     * @since  10.3.4
     */
    private const DEBUG_FILE = 'com_proclaim.debug.php';

    /**
     * Severities recorded when debug mode is off: problems only.
     *
     * Deliberately excludes DEBUG, INFO and NOTICE. Those describe normal running,
     * and normal running happens on every request — one routine line per request
     * is unbounded growth for no diagnostic value. A site that needs them turns
     * debug mode on, which widens this to Log::ALL.
     *
     * This matters more than it looks: registering these categories made ~160
     * pre-existing Log::add() calls start writing for the first time, including
     * per-render INFO in the scripture helper.
     *
     * @since  10.3.4
     */
    private const PRODUCTION_PRIORITIES = Log::EMERGENCY | Log::ALERT | Log::CRITICAL | Log::ERROR | Log::WARNING;

    /**
     * Whether the loggers have been registered for this request.
     *
     * @var    bool
     * @since  10.3.4
     */
    private static bool $registered = false;

    /**
     * Register a logger for every Proclaim category, once per request.
     *
     * Idempotent and safe to call from anywhere.
     *
     * @return  void
     *
     * @since   10.3.4
     */
    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        // Set before the calls: if registration throws, retrying on every
        // subsequent entry would only repeat the failure.
        //
        // It also makes this the single owner of registration. Registering a
        // category twice gives it two loggers and writes every entry twice, so
        // admin/api.php delegates here rather than registering its own.
        self::$registered = true;

        $files    = self::FILES;
        $debugOn  = self::isDebugEnabled();
        $priority = $debugOn ? Log::ALL : self::PRODUCTION_PRIORITIES;

        // Only when debug mode is active, so production sites do not accumulate an
        // empty debug log — behaviour inherited from admin/api.php.
        if ($debugOn) {
            $files[self::CATEGORY_DEBUG] = self::DEBUG_FILE;
        }

        foreach ($files as $category => $file) {
            try {
                Log::addLogger(['text_file' => $file], $priority, [$category]);
            } catch (\Throwable) {
                // An unwritable log path must never cost the site a response.
            }
        }
    }

    /**
     * Whether verbose logging is switched on for this request.
     *
     * JBSMDEBUG is defined by admin/api.php from the component's debug setting, or
     * by the jbsmdbg query parameter.
     *
     * @return  bool
     *
     * @since   10.3.4
     */
    private static function isDebugEnabled(): bool
    {
        if (\defined('JBSMDEBUG') && JBSMDEBUG) {
            return true;
        }

        // JBSMDEBUG is defined by admin/api.php, which never runs in the API
        // application — it needs a document for the web asset manager. Without
        // this fallback the API could never be made verbose at all, which is the
        // one place a "why did that 404?" line is most wanted. Joomla's global
        // debug flag comes from configuration.php, so it costs no query.
        try {
            return (bool) Factory::getApplication()->get('debug', false);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Diagnostics — how a request was resolved. Noise in normal running,
     * invaluable when something 404s and nobody can say why.
     *
     * @param   string  $message   The message to record.
     * @param   string  $category  One of the CATEGORY_* constants.
     *
     * @return  void
     *
     * @since   10.3.4
     */
    public static function debug(string $message, string $category = self::CATEGORY_GENERAL): void
    {
        // Skipped entirely unless debug mode is on. These fire on every request,
        // so without this guard the log grows in step with traffic while saying
        // the same thing each time.
        if (!self::isDebugEnabled()) {
            return;
        }

        self::write($message, Log::DEBUG, $category);
    }

    /**
     * Notable but expected activity.
     *
     * Successful content changes deliberately do NOT come through here — they
     * belong to the action log, and recording them in both would make an audit
     * show every change twice.
     *
     * @param   string  $message   The message to record.
     * @param   string  $category  One of the CATEGORY_* constants.
     *
     * @return  void
     *
     * @since   10.3.4
     */
    public static function info(string $message, string $category = self::CATEGORY_GENERAL): void
    {
        // As with debug(): routine activity is only recorded when someone has
        // asked to see it.
        if (!self::isDebugEnabled()) {
            return;
        }

        self::write($message, Log::INFO, $category);
    }

    /**
     * Something was refused or looked wrong — security-relevant, but not a
     * malfunction. A blocked write to a read-only API resource belongs here.
     *
     * @param   string  $message   The message to record.
     * @param   string  $category  One of the CATEGORY_* constants.
     *
     * @return  void
     *
     * @since   10.3.4
     */
    public static function warning(string $message, string $category = self::CATEGORY_GENERAL): void
    {
        self::write($message, Log::WARNING, $category);
    }

    /**
     * Proclaim could not do its job — a failure an administrator must act on,
     * such as the API being unable to read its own configuration.
     *
     * @param   string  $message   The message to record.
     * @param   string  $category  One of the CATEGORY_* constants.
     *
     * @return  void
     *
     * @since   10.3.4
     */
    public static function error(string $message, string $category = self::CATEGORY_GENERAL): void
    {
        self::write($message, Log::ERROR, $category);
    }

    /**
     * Record one entry.
     *
     * @param   string   $message   The message to record.
     * @param   int  $priority  A Log::* severity constant.
     * @param   string   $category  Log category.
     *
     * @return  void
     *
     * @since   10.3.4
     */
    private static function write(string $message, int $priority, string $category): void
    {
        self::register();

        try {
            Log::add($message, $priority, $category);
        } catch (\Throwable) {
            // Logging must never be the reason a request fails.
        }
    }
}

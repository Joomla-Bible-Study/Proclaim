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
 * @since  __DEPLOY_VERSION__
 */
final class CwmlogHelper
{
    /**
     * General component activity — the category most of Proclaim already uses.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const CATEGORY_GENERAL = 'com_proclaim';

    /**
     * REST API operational and security events.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const CATEGORY_API = 'com_proclaim.api';

    /**
     * Bible / scripture lookups.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const CATEGORY_BIBLE = 'com_proclaim.bible';

    /**
     * Verbose developer diagnostics.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const CATEGORY_DEBUG = 'com_proclaim.debug';

    /**
     * Category => log file. Separate files keep an API security review from
     * having to wade through template migrations.
     *
     * The first two filenames are inherited from admin/api.php, which used to own
     * this registration — existing sites already have those files and may have log
     * rotation pointed at them, so the names are kept.
     *
     * @var    array<string, string>
     * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
     */
    private const DEBUG_FILE = 'com_proclaim.debug.php';

    /**
     * Whether the loggers have been registered for this request.
     *
     * @var    boolean
     * @since  __DEPLOY_VERSION__
     */
    private static bool $registered = false;

    /**
     * Register a logger for every Proclaim category, once per request.
     *
     * Idempotent and safe to call from anywhere.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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

        $files = self::FILES;

        // Only when debug mode is active, so production sites do not accumulate an
        // empty debug log — behaviour inherited from admin/api.php.
        if (\defined('JBSMDEBUG') && JBSMDEBUG) {
            $files[self::CATEGORY_DEBUG] = self::DEBUG_FILE;
        }

        foreach ($files as $category => $file) {
            try {
                Log::addLogger(['text_file' => $file], Log::ALL, [$category]);
            } catch (\Throwable) {
                // An unwritable log path must never cost the site a response.
            }
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
     * @since   __DEPLOY_VERSION__
     */
    public static function debug(string $message, string $category = self::CATEGORY_GENERAL): void
    {
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
     * @since   __DEPLOY_VERSION__
     */
    public static function info(string $message, string $category = self::CATEGORY_GENERAL): void
    {
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
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public static function error(string $message, string $category = self::CATEGORY_GENERAL): void
    {
        self::write($message, Log::ERROR, $category);
    }

    /**
     * Record one entry.
     *
     * @param   string   $message   The message to record.
     * @param   integer  $priority  A Log::* severity constant.
     * @param   string   $category  Log category.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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

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
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

/**
 * Analytics helper — GDPR-aware event logging and scheduled maintenance.
 *
 * This helper handles cross-cutting concerns used outside the analytics view:
 *  - logEvent()          — called from site (frontend) views to record raw events
 *  - classifyReferrer()  — pure utility; used by logEvent() and unit tests
 *  - classifyUserAgent() — pure utility; used by logEvent() and unit tests
 *  - isOptedOut()        — GDPR/DNT check; used by logEvent()
 *  - rollupAndPurge()    — background maintenance; called by the task scheduler plugin
 *
 * Dashboard data queries live in CwmanalyticsModel.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmanalyticsHelper
{
    /**
     * Social domains used for referrer classification.
     *
     * @var string[]
     * @since 10.1.0
     */
    private static array $socialDomains = [
        'facebook.com', 'fb.com', 'instagram.com', 'twitter.com', 'x.com',
        'youtube.com', 'youtu.be', 'linkedin.com', 'tiktok.com', 'pinterest.com',
        'reddit.com', 'threads.net', 'snapchat.com', 'tumblr.com', 'vimeo.com',
        'whatsapp.com', 'telegram.org', 't.me',
    ];

    /**
     * Organic search domains used for referrer classification.
     *
     * @var string[]
     * @since 10.1.0
     */
    private static array $organicDomains = [
        'google.', 'bing.com', 'yahoo.com', 'duckduckgo.com', 'yandex.',
        'baidu.com', 'ecosia.org', 'startpage.com', 'ask.com', 'aol.com',
    ];

    /**
     * Log an analytics event.
     *
     * Respects GDPR opt-out (DNT header + proclaim_analytics_optout cookie).
     * Classifies UA and referrer at log-time; raw signals never stored.
     *
     * NOTE: no GeoIP classification happens here, despite what this docblock
     * previously claimed. There is no GeoIP resolution anywhere in the
     * codebase, and country_code is absent from this method's INSERT column
     * list, so #__bsms_analytics_events.country_code is always NULL. The
     * monthly rollup and CwmanalyticsModel::getBreakdown('country_code', ...)
     * therefore have nothing to report. Deliberately left unimplemented rather
     * than wired up here: GeoIP means a new dependency and a new privacy
     * surface, against a schema that documents never storing the raw IP. No
     * admin view currently renders a country breakdown, so nothing surfaces an
     * empty widget. See #1571.
     *
     * @param   string  $type      Event type: page_view|play|download|outbound_click
     * @param   int     $studyId   Study (message) ID, 0 if media-only
     * @param   int     $mediaId   Media file ID, 0 if page view
     * @param   string  $destUrl   Destination URL for outbound_click events
     * @param   int     $seriesId  Series ID (optional; auto-resolved from study when omitted)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function logEvent(string $type, int $studyId = 0, int $mediaId = 0, string $destUrl = '', int $seriesId = 0): void
    {
        try {
            $app = Factory::getApplication();

            // Bail if tracking is disabled entirely
            try {
                $admin  = Cwmparams::getAdmin();
                $params = $admin->params;

                if (!$params->get('analytics_enabled', '1')) {
                    return;
                }
            } catch (\Throwable $e) {
                // No params available — proceed with defaults (tracking on)
                $params = new \Joomla\Registry\Registry();
            }

            $optedOut  = self::isOptedOut();
            $consentOn = !$optedOut;

            // Classify UA (raw string discarded)
            $ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $uaInfo = self::classifyUserAgent($ua);

            // Classify referrer
            $refUrl  = $_SERVER['HTTP_REFERER'] ?? '';
            $refMode = (string) $params->get('analytics_referrer_mode', 'type');
            $refInfo = self::classifyReferrer($refUrl, $app->getInput()->getString('utm_medium', ''));

            // UTM params (visitor intentionally included these)
            $utmSource   = $app->getInput()->getString('utm_source', '');
            $utmMedium   = $app->getInput()->getString('utm_medium', '');
            $utmCampaign = $app->getInput()->getString('utm_campaign', '');

            // Language
            $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
            $language   = '';

            if ($acceptLang !== '') {
                $parts    = explode(',', $acceptLang);
                $language = substr(trim(explode(';', $parts[0])[0]), 0, 10);
            }

            // Auto-resolve study_id from a media file when not provided
            if ($studyId === 0 && $mediaId > 0) {
                $studyId = self::resolveStudyId($mediaId);
            }

            // Auto-resolve series_id from study when not provided
            if ($seriesId === 0 && $studyId > 0) {
                $seriesId = self::resolveSeriesId($studyId);
            }

            // Campus: resolved from study or media record
            $locationId = self::resolveLocationId($studyId, $mediaId);

            // Session hash (personal data — consent-required)
            $sessionHash = null;

            if ($consentOn) {
                try {
                    $sessionId   = $app->getSession()->getId();
                    $sessionHash = hash('sha256', $sessionId);
                } catch (\Exception $e) {
                    $sessionHash = null;
                }
            }

            // Referrer fields (personal-data tier — consent-required)
            $referrerUrl    = null;
            $referrerDomain = null;

            if ($consentOn && $refUrl !== '') {
                if ($refMode === 'full') {
                    $referrerUrl = substr($refUrl, 0, 2048);
                }

                if ($refMode === 'full' || $refMode === 'domain') {
                    $host           = parse_url($refUrl, PHP_URL_HOST) ?: '';
                    $referrerDomain = substr(self::stripWwwPrefix($host), 0, 255);
                }
            }

            // Outbound click: repurpose destUrl as referrer_url column
            if ($type === 'outbound_click' && $destUrl !== '') {
                $referrerUrl = substr($destUrl, 0, 2048);
            }

            $db  = Factory::getContainer()->get(DatabaseInterface::class);
            $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

            $query = $db->createQuery()
                ->insert($db->quoteName('#__bsms_analytics_events'))
                ->columns([
                    $db->quoteName('series_id'),
                    $db->quoteName('study_id'),
                    $db->quoteName('media_id'),
                    $db->quoteName('location_id'),
                    $db->quoteName('event_type'),
                    $db->quoteName('referrer_type'),
                    $db->quoteName('referrer_url'),
                    $db->quoteName('referrer_domain'),
                    $db->quoteName('utm_source'),
                    $db->quoteName('utm_medium'),
                    $db->quoteName('utm_campaign'),
                    $db->quoteName('device_type'),
                    $db->quoteName('browser'),
                    $db->quoteName('os'),
                    $db->quoteName('language'),
                    $db->quoteName('is_guest'),
                    $db->quoteName('session_hash'),
                    $db->quoteName('created'),
                ])
                ->values(implode(',', [
                    $seriesId > 0 ? (int) $seriesId : 'NULL',
                    $studyId > 0 ? (int) $studyId : 'NULL',
                    $mediaId > 0 ? (int) $mediaId : 'NULL',
                    $locationId > 0 ? (int) $locationId : 'NULL',
                    $db->quote($type),
                    $refInfo['type'] !== '' ? $db->quote($refInfo['type']) : 'NULL',
                    $referrerUrl !== null ? $db->quote($referrerUrl) : 'NULL',
                    $referrerDomain !== null ? $db->quote($referrerDomain) : 'NULL',
                    $utmSource !== '' ? $db->quote(substr($utmSource, 0, 255)) : 'NULL',
                    $utmMedium !== '' ? $db->quote(substr($utmMedium, 0, 255)) : 'NULL',
                    $utmCampaign !== '' ? $db->quote(substr($utmCampaign, 0, 255)) : 'NULL',
                    $db->quote($uaInfo['device']),
                    $db->quote($uaInfo['browser']),
                    $db->quote($uaInfo['os']),
                    $language !== '' ? $db->quote($language) : 'NULL',
                    $app->getIdentity()?->guest ? 1 : 0,
                    $sessionHash !== null ? $db->quote($sessionHash) : 'NULL',
                    $db->quote($now),
                ]));

            $db->setQuery($query);
            $db->execute();
        } catch (\Exception $e) {
            // Never let analytics break the page
        }
    }

    /**
     * Classify a referrer URL into a type bucket and extract the domain.
     *
     * @param   string  $url        Full referrer URL.
     * @param   string  $utmMedium  utm_medium parameter (used to detect email campaigns).
     *
     * @return  array{type: string, domain: string}
     *
     * @since   10.1.0
     */
    /**
     * Strip a leading "www." from a hostname.
     *
     * Replaces ltrim($host, 'www.'), which was a long-standing bug: ltrim()'s
     * second argument is a *character mask*, not a prefix. It stripped any
     * leading run of 'w', '.' — so 'worship.example.org' became
     * 'orship.example.org' and 'watch.church.tv' became 'atch.church.tv'.
     * Any host beginning with w (worship, watch, webex, wesleyan, wordoflife)
     * was silently corrupted, both in the stored referrer_domain column and in
     * the internal-vs-external comparison in classifyReferrer(). See #1571.
     *
     * @param   string  $host  Hostname, possibly prefixed with "www."
     *
     * @return  string
     *
     * @since __DEPLOY_VERSION__
     */
    public static function stripWwwPrefix(string $host): string
    {
        return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
    }

    public static function classifyReferrer(string $url, string $utmMedium = ''): array
    {
        if ($url === '') {
            if (stripos($utmMedium, 'email') !== false) {
                return ['type' => 'email', 'domain' => ''];
            }

            return ['type' => 'direct', 'domain' => ''];
        }

        $host     = self::stripWwwPrefix(strtolower(parse_url($url, PHP_URL_HOST) ?: ''));
        $siteHost = '';

        try {
            $siteHost = self::stripWwwPrefix(strtolower(Uri::getInstance()->getHost()));
        } catch (\Throwable $e) {
            // In the CLI / unit-test context, there is no request URI
        }

        if ($siteHost !== '' && $host === $siteHost) {
            return ['type' => 'internal', 'domain' => $host];
        }

        foreach (self::$organicDomains as $organic) {
            if (str_contains($host, $organic)) {
                return ['type' => 'organic', 'domain' => $host];
            }
        }

        foreach (self::$socialDomains as $social) {
            if (str_contains($host, $social)) {
                return ['type' => 'social', 'domain' => $host];
            }
        }

        if (stripos($utmMedium, 'email') !== false) {
            return ['type' => 'email', 'domain' => $host];
        }

        return ['type' => 'other', 'domain' => $host];
    }

    /**
     * Classify a User-Agent string into a device/browser/OS.
     * The raw UA string is never stored; only these classified values are.
     *
     * @param   string  $ua  Raw User-Agent header value.
     *
     * @return  array{device: string, browser: string, os: string}
     *
     * @since   10.1.0
     */
    public static function classifyUserAgent(string $ua): array
    {
        if ($ua === '') {
            return ['device' => 'unknown', 'browser' => 'other', 'os' => 'other'];
        }

        $lower = strtolower($ua);

        // Device type
        $device = 'desktop';

        if (str_contains($lower, 'tablet') || str_contains($lower, 'ipad')) {
            $device = 'tablet';
        } elseif (
            str_contains($lower, 'mobile') || str_contains($lower, 'android') ||
            str_contains($lower, 'iphone') || str_contains($lower, 'ipod')
        ) {
            $device = 'mobile';
        }

        // Browser — order matters: Edge before Chrome, Chrome before Safari
        $browser = 'other';

        if (str_contains($lower, 'edg/') || str_contains($lower, 'edge/')) {
            $browser = 'Edge';
        } elseif (str_contains($lower, 'opr/') || str_contains($lower, 'opera')) {
            $browser = 'Opera';
        } elseif (str_contains($lower, 'chrome/')) {
            $browser = 'Chrome';
        } elseif (str_contains($lower, 'firefox/')) {
            $browser = 'Firefox';
        } elseif (str_contains($lower, 'safari/')) {
            $browser = 'Safari';
        }

        // OS
        $os = 'other';

        if (str_contains($lower, 'windows')) {
            $os = 'Windows';
        } elseif (str_contains($lower, 'iphone') || str_contains($lower, 'ipad') || str_contains($lower, 'ipod')) {
            $os = 'iOS';
        } elseif (str_contains($lower, 'mac os')) {
            $os = 'macOS';
        } elseif (str_contains($lower, 'android')) {
            $os = 'Android';
        } elseif (str_contains($lower, 'linux')) {
            $os = 'Linux';
        }

        return ['device' => $device, 'browser' => $browser, 'os' => $os];
    }

    /**
     * Check whether the current visitor has opted out of personal-data tracking.
     * Respects the DNT (Do Not Track) header and the proclaim_analytics_optout cookie.
     *
     * @return  bool  True if opted out (skip personal-data columns).
     *
     * @since   10.1.0
     */
    public static function isOptedOut(): bool
    {
        try {
            $admin  = Cwmparams::getAdmin();
            $params = $admin->params;

            if (!$params->get('analytics_gdpr_optout', '1')) {
                return false;
            }

            // GDPR mode — Proclaim keeps its own copy in component params
            if ($params->get('gdpr_mode', '0')) {
                return true;
            }
        } catch (\Throwable $e) {
            // No params available — default to opt-out support enabled
        }

        if (($_SERVER['HTTP_DNT'] ?? '') === '1') {
            return true;
        }

        if (!empty($_COOKIE['proclaim_analytics_optout'])) {
            return true;
        }

        return false;
    }

    /**
     * Shortest retention window the purge will act on, matching the `min`
     * on the task form's retention field.
     *
     * @since  __DEPLOY_VERSION__
     */
    public const int MIN_RETENTION_DAYS = 7;

    /**
     * Is this retention window safe to delete raw events against?
     *
     * A window of zero or less puts the cutoff at -- or, when negative,
     * *after* -- "now", which makes the purge DELETE match every row in
     * the events table rather than only aged ones.
     *
     * That is reachable from ordinary configuration, not just tampering.
     * The task plugin reads `retention_days` from task params and casts it
     * with `(int)`, and `?? 90` only defends against NULL: a blank field in
     * the Scheduler UI saves as `""`, and `(int) "" === 0`. `min="7"` on
     * the form field is a client-side rendering hint that is not enforced
     * server-side. Note that adding `filter="int"` to that field does NOT
     * help -- it converts `""` to exactly the lethal `0`.
     *
     * Kept pure and public so it can be exercised without touching the
     * database. Testing the guard by calling rollupAndPurge() with a bad
     * window means running the real DELETE if the guard is wrong.
     *
     * @param   int  $retentionDays  Proposed retention window in days.
     *
     * @return  bool  True when raw events may be deleted.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function isPurgeSafe(int $retentionDays): bool
    {
        return $retentionDays >= self::MIN_RETENTION_DAYS;
    }

    /**
     * Roll up raw events older than $retentionDays into monthly aggregates,
     * then purge the rolled-up raw events. Called by the scheduled task plugin.
     *
     * On an unsafe or disabled purge the rollup still runs -- aggregating is
     * non-destructive -- but nothing is deleted and `purged` is 0. Guessing a
     * "sensible" fallback window is deliberately avoided: an invalid setting
     * carries no signal about intent, and an admin who meant 365 should not
     * silently lose 275 days because the code assumed 90.
     *
     * The floor is enforced here, at the point of deletion, rather than only
     * at the caller. Bad values can arrive from the Scheduler UI, a restored
     * backup (Cwmbackup rewrites #__scheduler_tasks wholesale), a future
     * migration, or a direct database edit -- enumerating those paths is not
     * something the code can do, but guarding the DELETE covers all of them.
     *
     * @param   int   $retentionDays  Events older than this are rolled up, and purged when $purge is true.
     * @param   bool  $purge          Whether to delete the raw events after aggregating them.
     *
     * @return  array{rolled: int, purged: int}
     *
     * @since   10.1.0
     */
    public static function rollupAndPurge(int $retentionDays = 90, bool $purge = true): array
    {
        $result = ['rolled' => 0, 'purged' => 0];

        // An out-of-range window invalidates the whole run, not just the
        // delete: $retentionDays *is* the cutoff, so a value of 0 would have
        // the rollup summarise every event including today's while the raw
        // rows survive -- inflating the monthly aggregates the next valid run
        // cannot correct. Refuse both halves and leave the data untouched.
        if (!self::isPurgeSafe($retentionDays)) {
            CwmDebug::error(
                \sprintf(
                    'Analytics rollup aborted: retention of %d day(s) is below the %d-day minimum. A window of'
                    . ' zero or less puts the cutoff at or after "now", which would have deleted every raw'
                    . ' event. Check the Proclaim Analytics task\'s retention setting.',
                    $retentionDays,
                    self::MIN_RETENTION_DAYS
                ),
                null,
                'analytics'
            );

            return $result;
        }

        try {
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $cutoff = (new \DateTime('now', new \DateTimeZone('UTC')))
                ->modify('-' . (int) $retentionDays . ' days')
                ->format('Y-m-d H:i:s');

            // The whole rollup+purge is one transaction. Previously the INSERT
            // and the DELETE were independent statements: if the task died
            // between them (execution-time limit, scheduler timeout, OOM,
            // deploy restart) the raw events survived, the next run's cutoff
            // still covered them, and they were aggregated a second time --
            // permanently inflating the monthly totals with no self-correction.
            // See #1571.
            //
            // Savepoint-aware (the `true` argument) rather than a bare
            // transactionStart(): MysqliDriver::transactionStart() calls
            // begin_transaction() unconditionally when not asked for a
            // savepoint, and MySQL *implicitly commits* any transaction
            // already in progress. A bare call would therefore silently commit
            // a caller's open transaction. With the flag set, a standalone run
            // still opens a real transaction (depth 0) while a nested run
            // takes a savepoint and leaves the caller's transaction intact.
            $db->transactionStart(true);

            // Collect the (month, dimension-tuple) groups this run will write,
            // so their existing monthly rows can be replaced rather than
            // duplicated.
            //
            // ON DUPLICATE KEY UPDATE cannot do this here: uq_aggregate spans
            // five NULLable columns (series_id, study_id, media_id,
            // location_id, country_code -- the last of which is *always* NULL,
            // see the note on country_code below), and MySQL treats NULL as
            // distinct from NULL in a unique index. The key therefore never
            // matched, so every run inserted fresh rows instead of
            // consolidating -- verified directly against MySQL. This mirrors
            // the delete-then-reinsert workaround CwmanalyticsModel::
            // seedFromLegacy() already uses for the same reason.
            $groupCols = [
                'series_id', 'study_id', 'media_id', 'location_id',
                'event_type', 'referrer_type', 'country_code', 'device_type',
            ];

            $pending = $db->setQuery(
                'SELECT DISTINCT ' . implode(',', array_map([$db, 'quoteName'], $groupCols))
                . ', YEAR(' . $db->quoteName('created') . ') AS y'
                . ', MONTH(' . $db->quoteName('created') . ') AS m'
                . ' FROM ' . $db->quoteName('#__bsms_analytics_events')
                . ' WHERE ' . $db->quoteName('created') . ' < ' . $db->quote($cutoff)
            )->loadObjectList() ?: [];

            foreach ($pending as $group) {
                $conditions = [
                    $db->quoteName('year') . ' = ' . (int) $group->y,
                    $db->quoteName('month') . ' = ' . (int) $group->m,
                ];

                foreach ($groupCols as $col) {
                    $value = $group->$col;

                    // NULL has to be matched with IS NULL, which is the very
                    // asymmetry that broke the unique key.
                    $conditions[] = $value === null
                        ? $db->quoteName($col) . ' IS NULL'
                        : $db->quoteName($col) . ' = ' . $db->quote($value);
                }

                $db->setQuery(
                    'DELETE FROM ' . $db->quoteName('#__bsms_analytics_monthly')
                    . ' WHERE ' . implode(' AND ', $conditions)
                )->execute();
            }

            // Rollup: aggregate into the monthly table. The ON DUPLICATE KEY
            // clause is retained as a backstop for the rows whose dimensions
            // happen to be entirely non-NULL, where the unique key does work.
            $rollupSql = 'INSERT INTO ' . $db->quoteName('#__bsms_analytics_monthly') . '
                (' . implode(',', array_map([$db, 'quoteName'], [
                'series_id', 'study_id', 'media_id', 'location_id', 'event_type',
                'referrer_type', 'country_code', 'device_type', 'year', 'month', 'count',
            ])) . ')
                SELECT
                    ' . $db->quoteName('series_id') . ',
                    ' . $db->quoteName('study_id') . ',
                    ' . $db->quoteName('media_id') . ',
                    ' . $db->quoteName('location_id') . ',
                    ' . $db->quoteName('event_type') . ',
                    ' . $db->quoteName('referrer_type') . ',
                    ' . $db->quoteName('country_code') . ',
                    ' . $db->quoteName('device_type') . ',
                    YEAR(' . $db->quoteName('created') . '),
                    MONTH(' . $db->quoteName('created') . '),
                    COUNT(*)
                FROM ' . $db->quoteName('#__bsms_analytics_events') . '
                WHERE ' . $db->quoteName('created') . ' < ' . $db->quote($cutoff) . '
                GROUP BY
                    ' . $db->quoteName('series_id') . ',
                    ' . $db->quoteName('study_id') . ',
                    ' . $db->quoteName('media_id') . ',
                    ' . $db->quoteName('location_id') . ',
                    ' . $db->quoteName('event_type') . ',
                    ' . $db->quoteName('referrer_type') . ',
                    ' . $db->quoteName('country_code') . ',
                    ' . $db->quoteName('device_type') . ',
                    YEAR(' . $db->quoteName('created') . '),
                    MONTH(' . $db->quoteName('created') . ')
                ON DUPLICATE KEY UPDATE ' . $db->quoteName('count') . ' = ' . $db->quoteName('count') . ' + VALUES(' . $db->quoteName('count') . ')';

            $db->setQuery($rollupSql);
            $db->execute();

            // Report the number of monthly groups written, not
            // getAffectedRows(): after ON DUPLICATE KEY UPDATE, MySQL counts 1
            // per inserted row but 2 per updated row, so the previous figure
            // was never an accurate count of anything.
            $result['rolled'] = \count($pending);

            // Purge rolled-up raw events.
            //
            // This runs inside the same transaction as the aggregate INSERT
            // above and against the same $cutoff, so the only rows it can
            // delete are ones this run has already summarised. That ordering
            // is the safety property: there is no path that deletes a raw
            // event which was not written to #__bsms_analytics_monthly first.
            if ($purge) {
                $purgeQuery = $db->createQuery()
                    ->delete($db->quoteName('#__bsms_analytics_events'))
                    ->where($db->quoteName('created') . ' < ' . $db->quote($cutoff));
                $db->setQuery($purgeQuery);
                $db->execute();
                $result['purged'] = $db->getAffectedRows();
            }

            $db->transactionCommit(true);
        } catch (\Exception $e) {
            // Roll back so a partial rollup is never left behind for the next
            // run to double-count.
            try {
                $db->transactionRollback(true);
            } catch (\Throwable) {
                // Nothing to roll back (connection lost, or never started).
            }

            CwmDebug::error('Analytics rollup failed, rolled back', $e, 'analytics');

            return ['rolled' => 0, 'purged' => 0];
        }

        return $result;
    }

    /**
     * Resolve the series ID from a study (message) record.
     *
     * @param   int  $studyId  Study ID.
     *
     * @return  int  Series ID or 0 if unknown.
     *
     * @since   10.1.0
     */
    private static function resolveSeriesId(int $studyId): int
    {
        if ($studyId <= 0) {
            return 0;
        }

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName('series_id'))
                ->from($db->quoteName('#__bsms_studies'))
                ->where($db->quoteName('id') . ' = ' . (int) $studyId);
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Resolve the study (message) ID from a media file record.
     *
     * @param   int  $mediaId  Media file ID.
     *
     * @return  int  Study ID or 0 if unknown.
     *
     * @since   10.1.0
     */
    private static function resolveStudyId(int $mediaId): int
    {
        if ($mediaId <= 0) {
            return 0;
        }

        try {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->createQuery()
                ->select($db->quoteName('study_id'))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('id') . ' = ' . (int) $mediaId);
            $db->setQuery($query);

            return (int) $db->loadResult();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Resolve the campus (location_id) from a study or media record.
     *
     * @param   int  $studyId  Study ID.
     * @param   int  $mediaId  Media file ID.
     *
     * @return  int  Location ID or 0 if unknown.
     *
     * @since   10.1.0
     */
    private static function resolveLocationId(int $studyId, int $mediaId): int
    {
        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            if ($studyId > 0) {
                $query = $db->createQuery()
                    ->select($db->quoteName('location_id'))
                    ->from($db->quoteName('#__bsms_studies'))
                    ->where($db->quoteName('id') . ' = ' . (int) $studyId);
                $db->setQuery($query);
                $id = (int) $db->loadResult();

                if ($id > 0) {
                    return $id;
                }
            }

            if ($mediaId > 0) {
                $query = $db->createQuery()
                    ->select($db->quoteName('s.location_id'))
                    ->from($db->quoteName('#__bsms_mediafiles', 'm'))
                    ->leftJoin(
                        $db->quoteName('#__bsms_studies', 's') .
                        ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id')
                    )
                    ->where($db->quoteName('m.id') . ' = ' . (int) $mediaId);
                $db->setQuery($query);
                $id = (int) $db->loadResult();

                if ($id > 0) {
                    return $id;
                }
            }
        } catch (\Exception $e) {
            // Ignore
        }

        return 0;
    }
}

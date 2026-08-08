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
     * NOTE: no GeoIP happens here -- country_code is absent from the INSERT and
     * is always NULL. Unimplemented on purpose: it means a new dependency and
     * privacy surface, and no admin view renders a country breakdown.
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

            // Session hash (personal data — consent-required).
            // Left NULL without a secret rather than falling back to an
            // unkeyed digest, which would be permanently linkable.
            $sessionHash = null;

            if ($consentOn) {
                try {
                    $sessionId = (string) $app->getSession()->getId();
                    $secret    = (string) $app->get('secret');

                    if ($sessionId !== '' && $secret !== '') {
                        $sessionHash = self::hashSessionForDay(
                            $sessionId,
                            $secret,
                            (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d')
                        );
                    }
                } catch (\Exception $e) {
                    $sessionHash = null;
                }
            }

            // Referrer fields (personal-data tier — consent-required)
            $referrerUrl    = null;
            $referrerDomain = null;

            if ($consentOn && $refUrl !== '') {
                if ($refMode === 'full') {
                    $referrerUrl = self::stripUrlParameters($refUrl);
                }

                if ($refMode === 'full' || $refMode === 'domain') {
                    $host           = parse_url($refUrl, PHP_URL_HOST) ?: '';
                    $referrerDomain = substr(self::stripWwwPrefix($host), 0, 255);
                }
            }

            // Outbound click: repurpose destUrl as referrer_url column.
            // Gated on $consentOn like every other write to this column.
            if ($consentOn && $type === 'outbound_click' && $destUrl !== '') {
                $referrerUrl = self::stripUrlParameters($destUrl);
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
     * Deliberately not ltrim($host, 'www.'): ltrim()'s second argument is a
     * *character mask*, not a prefix, so it strips any leading run of 'w' and
     * '.' -- turning 'worship.example.org' into 'orship.example.org'. Every
     * host beginning with w is affected, in both the stored referrer_domain
     * and the internal-vs-external comparison in classifyReferrer().
     *
     * @param   string  $host  Hostname, possibly prefixed with "www."
     *
     * @return  string
     *
     * @since 10.5.6
     */
    public static function stripWwwPrefix(string $host): string
    {
        return str_starts_with($host, 'www.') ? substr($host, 4) : $host;
    }

    /**
     * Reduce a URL to scheme, host and path, discarding query and fragment.
     *
     * The analytics signal is which page a visit came from, never the
     * parameters attached to it. A referrer query string is written by the
     * *referring* site and routinely carries things that are not ours to hold:
     * search terms, session tokens, addresses in newsletter links, password
     * reset and invite tokens. Expiring the column later does not help --
     * what is never stored cannot leak. See #1612.
     *
     * UTM tags are unaffected: they are captured separately into utm_source,
     * utm_medium and utm_campaign from the request, not parsed back out here.
     *
     * @param   string  $url  Absolute URL.
     *
     * @return  string|null  scheme://host/path, or null when no host is present.
     *
     * @since 10.5.6
     */
    public static function stripUrlParameters(string $url): ?string
    {
        $parts = parse_url($url);

        // A malformed URL parses to false, and one with no host (a bare path,
        // or a mailto:) has nothing worth recording as a source.
        if (!\is_array($parts) || ($parts['host'] ?? '') === '') {
            return null;
        }

        $scheme = $parts['scheme'] ?? 'https';
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';

        return substr($scheme . '://' . $parts['host'] . $port . ($parts['path'] ?? ''), 0, 2048);
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
     * Domain-separation label, so the analytics key is not interchangeable
     * with tokens minted elsewhere from the same site secret.
     *
     * @since  10.5.6
     */
    private const string SESSION_HASH_CONTEXT = 'proclaim:analytics:session';

    /**
     * Fingerprint a visitor's session for one day.
     *
     * Stored in session_hash so COUNT(DISTINCT session_hash) can report the
     * Sessions figure. Keyed with the site secret so an outsider cannot
     * recompute it from a guessed session ID, and rotated daily so the same
     * visitor is not linkable across days. Stable within a day, which is all
     * the count needs.
     *
     * Secret and day are arguments rather than globals so the rotation is
     * testable without a request context.
     *
     * @param   string  $sessionId  Raw session identifier; never stored.
     * @param   string  $secret     Site secret used as key material.
     * @param   string  $day        Rotation bucket, 'Y-m-d' in UTC.
     *
     * @return  string  64-character hex digest.
     *
     * @since   10.5.6
     */
    public static function hashSessionForDay(string $sessionId, string $secret, string $day): string
    {
        $dailyKey = hash_hmac('sha256', self::SESSION_HASH_CONTEXT . ':' . $day, $secret);

        return hash_hmac('sha256', $sessionId, $dailyKey);
    }

    /**
     * Cookie a site's own consent manager sets to suppress the personal-data
     * columns. Any non-empty value counts.
     *
     * Proclaim ships no consent banner -- most Joomla sites already run one --
     * so this is the published integration point, not a cookie Proclaim sets.
     *
     * @since  10.5.6
     */
    public const string OPTOUT_COOKIE = 'proclaim_analytics_optout';

    /**
     * Check whether the current visitor has opted out of personal-data tracking.
     *
     * Any one of three signals suppresses the consent-required columns:
     * Sec-GPC: 1, DNT: 1, or OPTOUT_COOKIE. DNT is kept for completeness but is
     * effectively defunct, which is why GPC was added alongside it.
     *
     * Governs the personal-data tier only. Proclaim stores nothing on the
     * visitor's device, so this is not answering the ePrivacy cookie-consent
     * question. See #1613.
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

            // GDPR mode — Proclaim keeps its own copy in component params.
            // Note this parameter is primarily documented as governing outbound
            // API calls and social sharing; that it also suppresses the
            // analytics personal-data tier is called out in its own
            // description so the coupling is not a surprise.
            if ($params->get('gdpr_mode', '0')) {
                return true;
            }
        } catch (\Throwable $e) {
            // No params available — default to opt-out support enabled
        }

        // Global Privacy Control — the signal that is actually alive.
        if (($_SERVER['HTTP_SEC_GPC'] ?? '') === '1') {
            return true;
        }

        if (($_SERVER['HTTP_DNT'] ?? '') === '1') {
            return true;
        }

        if (!empty($_COOKIE[self::OPTOUT_COOKIE])) {
            return true;
        }

        return false;
    }

    /**
     * Shortest retention window the purge will act on, matching the `min`
     * on the task form's retention field.
     *
     * @since  10.5.6
     */
    public const int MIN_RETENTION_DAYS = 7;

    /**
     * Is this retention window safe to delete raw events against?
     *
     * A window of zero or less puts the cutoff at (or, when negative, after)
     * "now", so the purge DELETE would match every row rather than only aged
     * ones. A blank field in the Scheduler UI saves as "" and casts to 0, so
     * this is reachable from ordinary configuration. Adding filter="int" to
     * that field does NOT help -- it converts "" to exactly the lethal 0.
     *
     * Pure and public so the guard can be tested without running the real
     * DELETE.
     *
     * @param   int  $retentionDays  Proposed retention window in days.
     *
     * @return  bool  True when raw events may be deleted.
     *
     * @since   10.5.6
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

            // The whole rollup+purge is one transaction. Were the INSERT and
            // the DELETE independent, a task dying between them (execution-time
            // limit, scheduler timeout, OOM, deploy restart) would leave the raw
            // events in place, still inside the next run's cutoff, to be
            // aggregated a second time -- permanently inflating the monthly
            // totals with no self-correction.
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

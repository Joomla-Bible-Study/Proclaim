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

/**
 * Analytics helper — two-tier GDPR-aware event tracking.
 *
 * Tier 1: Raw events in #__bsms_analytics_events (purged after retention period).
 * Tier 2: Permanent monthly aggregates in #__bsms_analytics_monthly.
 *
 * No IP addresses are ever stored. Country lookup (if GeoLite2 configured)
 * happens at log-time; only country_code is kept.
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
     * Classifies UA, referrer, and GeoIP at log-time; raw signals never stored.
     *
     * @param   string  $type     Event type: page_view|play|download|outbound_click
     * @param   int     $studyId  Study (message) ID, 0 if media-only
     * @param   int     $mediaId  Media file ID, 0 if page view
     * @param   string  $destUrl  Destination URL for outbound_click events
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function logEvent(string $type, int $studyId = 0, int $mediaId = 0, string $destUrl = ''): void
    {
        try {
            $app = Factory::getApplication();

            // Bail if tracking is disabled entirely
            try {
                $params = Cwmparams::getInstance();

                if (!$params->get('analytics_enabled', '1')) {
                    return;
                }
            } catch (\Throwable $e) {
                // No params available — proceed with defaults (tracking on)
                $params = new \Joomla\Registry\Registry();
            }

            $optedOut   = self::isOptedOut();
            $consentOn  = !$optedOut;

            // Classify UA (raw string discarded)
            $ua          = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $uaInfo      = self::classifyUserAgent($ua);

            // Classify referrer
            $refUrl      = $_SERVER['HTTP_REFERER'] ?? '';
            $refMode     = (string) $params->get('analytics_referrer_mode', 'type');
            $refInfo     = self::classifyReferrer($refUrl, $app->getInput()->getString('utm_medium', ''));

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
                    $referrerDomain = substr(ltrim($host, 'www.'), 0, 255);
                }
            }

            // Outbound click: repurpose destUrl as referrer_url column
            if ($type === 'outbound_click' && $destUrl !== '') {
                $referrerUrl = substr($destUrl, 0, 2048);
            }

            $db  = Factory::getContainer()->get('DatabaseDriver');
            $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__bsms_analytics_events'))
                ->columns([
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
     * @param   string  $url          Full referrer URL.
     * @param   string  $utmMedium    utm_medium parameter (used to detect email campaigns).
     *
     * @return  array{type: string, domain: string}
     *
     * @since   10.1.0
     */
    public static function classifyReferrer(string $url, string $utmMedium = ''): array
    {
        if ($url === '') {
            // Empty referrer with utm_medium=email → email campaign
            if (stripos($utmMedium, 'email') !== false) {
                return ['type' => 'email', 'domain' => ''];
            }

            return ['type' => 'direct', 'domain' => ''];
        }

        $host     = strtolower(parse_url($url, PHP_URL_HOST) ?: '');
        $host     = ltrim($host, 'www.');
        $siteHost = '';

        try {
            $siteHost = strtolower(ltrim(Uri::getInstance()->getHost(), 'www.'));
        } catch (\Throwable $e) {
            // In CLI / unit-test context there is no request URI
        }

        // Same domain → internal
        if ($siteHost !== '' && $host === $siteHost) {
            return ['type' => 'internal', 'domain' => $host];
        }

        // Check organic search engines
        foreach (self::$organicDomains as $organic) {
            if (str_contains($host, $organic)) {
                return ['type' => 'organic', 'domain' => $host];
            }
        }

        // Check social networks
        foreach (self::$socialDomains as $social) {
            if (str_contains($host, $social)) {
                return ['type' => 'social', 'domain' => $host];
            }
        }

        // utm_medium=email → email
        if (stripos($utmMedium, 'email') !== false) {
            return ['type' => 'email', 'domain' => $host];
        }

        return ['type' => 'other', 'domain' => $host];
    }

    /**
     * Classify a User-Agent string into device/browser/OS.
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

        // Browser — order matters: Edge must come before Chrome, Chrome before Safari
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
        // If GDPR opt-out support is disabled, never consider opted out
        try {
            $params = Cwmparams::getInstance();

            if (!$params->get('analytics_gdpr_optout', '1')) {
                return false;
            }

            // When site-wide GDPR compliance mode is ON, treat all visitors as opted out
            // (gdpr_mode disables external calls; analytics personal data should be suppressed too)
            if ($params->get('gdpr_mode', '0')) {
                return true;
            }
        } catch (\Throwable $e) {
            // No params available (CLI / unit-test) — default to opt-out support enabled
        }

        // DNT header: value "1" means opt-out
        if (($_SERVER['HTTP_DNT'] ?? '') === '1') {
            return true;
        }

        // Proclaim analytics opt-out cookie
        if (!empty($_COOKIE['proclaim_analytics_optout'])) {
            return true;
        }

        return false;
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
            $db = Factory::getContainer()->get('DatabaseDriver');

            if ($studyId > 0) {
                $query = $db->getQuery(true)
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
                $query = $db->getQuery(true)
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

    // -------------------------------------------------------------------------
    // Dashboard query methods
    // -------------------------------------------------------------------------

    /**
     * Get all-time accumulated totals directly from the source record counters.
     *
     * Queries #__bsms_studies.hits (page views) and #__bsms_mediafiles plays/downloads.
     * These are the counters incremented in real-time by the existing hit() / hitPlay() /
     * download() methods — they are always current regardless of whether analytics event
     * tracking is active.
     *
     * Used by the admin-center quick-stats cards where "how much content has been
     * accessed total" is more useful than the new event log.
     *
     * @param   int  $locationId  Filter by campus; 0 = all.
     *
     * @return  array{views: int, plays: int, downloads: int}
     *
     * @since   10.1.0
     */
    public static function getRecordTotals(int $locationId = 0): array
    {
        $result = ['views' => 0, 'plays' => 0, 'downloads' => 0];

        try {
            $db = Factory::getContainer()->get('DatabaseDriver');

            // Page views from studies
            $q = $db->getQuery(true)
                ->select('SUM(' . $db->quoteName('hits') . ')')
                ->from($db->quoteName('#__bsms_studies'));

            if ($locationId > 0) {
                $q->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            }

            $db->setQuery($q);
            $result['views'] = (int) ($db->loadResult() ?? 0);

            // Plays and downloads from media files (join to get location)
            $q2 = $db->getQuery(true)
                ->select([
                    'SUM(' . $db->quoteName('m.plays') . ') AS plays',
                    'SUM(' . $db->quoteName('m.downloads') . ') AS downloads',
                ])
                ->from($db->quoteName('#__bsms_mediafiles', 'm'));

            if ($locationId > 0) {
                $q2->leftJoin(
                    $db->quoteName('#__bsms_studies', 's') .
                    ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id')
                )->where($db->quoteName('s.location_id') . ' = ' . (int) $locationId);
            }

            $db->setQuery($q2);
            $row                 = $db->loadAssoc() ?? [];
            $result['plays']     = (int) ($row['plays'] ?? 0);
            $result['downloads'] = (int) ($row['downloads'] ?? 0);
        } catch (\Exception $e) {
            // Return zeros
        }

        return $result;
    }

    /**
     * Get KPI totals for the dashboard.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  Filter by campus; 0 = all authorised.
     *
     * @return  array{views: int, plays: int, downloads: int, sessions: int}
     *
     * @since   10.1.0
     */
    public static function getKpiTotals(string $start, string $end, int $locationId = 0): array
    {
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select([
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('page_view') . ' THEN 1 ELSE 0 END) AS views',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) AS plays',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END) AS downloads',
                    'COUNT(DISTINCT ' . $db->quoteName('session_hash') . ') AS sessions',
                ])
                ->from($db->quoteName('#__bsms_analytics_events'))
                ->where($db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59'));

            if ($locationId > 0) {
                $query->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            } else {
                self::applyLocationFilter($query, $db);
            }

            $db->setQuery($query);
            $row = $db->loadAssoc() ?? [];

            return [
                'views'     => (int) ($row['views'] ?? 0),
                'plays'     => (int) ($row['plays'] ?? 0),
                'downloads' => (int) ($row['downloads'] ?? 0),
                'sessions'  => (int) ($row['sessions'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['views' => 0, 'plays' => 0, 'downloads' => 0, 'sessions' => 0];
        }
    }

    /**
     * Get daily or weekly time-series data for chart rendering.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  Filter by campus; 0 = all authorised.
     *
     * @return  array<int, array{period: string, views: int, plays: int, downloads: int}>
     *
     * @since   10.1.0
     */
    public static function getTimeSeries(string $start, string $end, int $locationId = 0): array
    {
        try {
            $days   = (int) ((strtotime($end) - strtotime($start)) / 86400);
            $format = $days <= 90 ? '%Y-%m-%d' : '%Y-%u';
            $db     = Factory::getContainer()->get('DatabaseDriver');

            $query = $db->getQuery(true)
                ->select([
                    'DATE_FORMAT(' . $db->quoteName('created') . ', ' . $db->quote($format) . ') AS period',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('page_view') . ' THEN 1 ELSE 0 END) AS views',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) AS plays',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END) AS downloads',
                ])
                ->from($db->quoteName('#__bsms_analytics_events'))
                ->where($db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59'))
                ->group('period')
                ->order('period ASC');

            if ($locationId > 0) {
                $query->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            } else {
                self::applyLocationFilter($query, $db);
            }

            $db->setQuery($query);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get top studies by combined engagement (views + plays + downloads).
     *
     * @param   string  $start   Start date (Y-m-d).
     * @param   string  $end     End date (Y-m-d).
     * @param   int     $limit   Maximum results.
     *
     * @return  array<int, array{study_id: int, title: string, total: int}>
     *
     * @since   10.1.0
     */
    public static function getTopStudies(string $start, string $end, int $limit = 10): array
    {
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('e.study_id'),
                    $db->quoteName('s.studytitle', 'title'),
                    'COUNT(*) AS total',
                ])
                ->from($db->quoteName('#__bsms_analytics_events', 'e'))
                ->leftJoin(
                    $db->quoteName('#__bsms_studies', 's') .
                    ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('e.study_id')
                )
                ->where($db->quoteName('e.created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('e.created') . ' <= ' . $db->quote($end . ' 23:59:59'))
                ->where($db->quoteName('e.study_id') . ' IS NOT NULL')
                ->group($db->quoteName('e.study_id'))
                ->order('total DESC');
            $db->setQuery($query, 0, (int) $limit);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get referrer type breakdown.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  array<int, array{referrer_type: string, count: int}>
     *
     * @since   10.1.0
     */
    public static function getReferrerBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return self::getBreakdown('referrer_type', $start, $end, $locationId);
    }

    /**
     * Get country breakdown.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  array<int, array{country_code: string, count: int}>
     *
     * @since   10.1.0
     */
    public static function getCountryBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return self::getBreakdown('country_code', $start, $end, $locationId);
    }

    /**
     * Get device type breakdown.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  array<int, array{device_type: string, count: int}>
     *
     * @since   10.1.0
     */
    public static function getDeviceBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return self::getBreakdown('device_type', $start, $end, $locationId);
    }

    /**
     * Get browser breakdown.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  array<int, array{browser: string, count: int}>
     *
     * @since   10.1.0
     */
    public static function getBrowserBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return self::getBreakdown('browser', $start, $end, $locationId);
    }

    /**
     * Get OS breakdown.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  array<int, array{os: string, count: int}>
     *
     * @since   10.1.0
     */
    public static function getOsBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return self::getBreakdown('os', $start, $end, $locationId);
    }

    /**
     * Get language breakdown.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  array<int, array{language: string, count: int}>
     *
     * @since   10.1.0
     */
    public static function getLanguageBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return self::getBreakdown('language', $start, $end, $locationId);
    }

    /**
     * Get UTM campaign breakdown.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  array<int, array{utm_source: string, utm_medium: string, utm_campaign: string, count: int}>
     *
     * @since   10.1.0
     */
    public static function getUtmBreakdown(string $start, string $end, int $locationId = 0): array
    {
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('utm_source'),
                    $db->quoteName('utm_medium'),
                    $db->quoteName('utm_campaign'),
                    'COUNT(*) AS count',
                ])
                ->from($db->quoteName('#__bsms_analytics_events'))
                ->where($db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59'))
                ->where($db->quoteName('utm_source') . ' IS NOT NULL')
                ->group([$db->quoteName('utm_source'), $db->quoteName('utm_medium'), $db->quoteName('utm_campaign')])
                ->order('count DESC');

            if ($locationId > 0) {
                $query->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            } else {
                self::applyLocationFilter($query, $db);
            }

            $db->setQuery($query, 0, 20);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Export analytics data as a CSV download.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  void  Outputs CSV and terminates.
     *
     * @since   10.1.0
     */
    public static function exportCsv(string $start, string $end, int $locationId = 0): void
    {
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__bsms_analytics_events'))
                ->where($db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59'))
                ->order($db->quoteName('created') . ' ASC');

            if ($locationId > 0) {
                $query->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            } else {
                self::applyLocationFilter($query, $db);
            }

            $db->setQuery($query);
            $rows = $db->loadAssocList() ?? [];

            $filename = 'proclaim-analytics-' . $start . '-to-' . $end . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');

            $out = fopen('php://output', 'w');

            if (!empty($rows)) {
                fputcsv($out, array_keys($rows[0]));

                foreach ($rows as $row) {
                    fputcsv($out, $row);
                }
            }

            fclose($out);
        } catch (\Exception $e) {
            // Fail silently; controller will handle
        }
    }

    /**
     * Roll up raw events older than $retentionDays into monthly aggregates.
     * Called by the scheduled task.
     *
     * @param   int  $retentionDays  Events older than this are rolled up and purged.
     *
     * @return  array{rolled: int, purged: int}
     *
     * @since   10.1.0
     */
    public static function rollupAndPurge(int $retentionDays = 90): array
    {
        $result = ['rolled' => 0, 'purged' => 0];

        try {
            $db       = Factory::getContainer()->get('DatabaseDriver');
            $cutoff   = (new \DateTime('now', new \DateTimeZone('UTC')))
                ->modify('-' . (int) $retentionDays . ' days')
                ->format('Y-m-d H:i:s');

            // Rollup: aggregate into monthly table using ON DUPLICATE KEY UPDATE
            $rollupSql = 'INSERT INTO ' . $db->quoteName('#__bsms_analytics_monthly') . '
                (' . implode(',', array_map([$db, 'quoteName'], [
                'study_id', 'media_id', 'location_id', 'event_type',
                'referrer_type', 'country_code', 'device_type', 'year', 'month', 'count',
            ])) . ')
                SELECT
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
            $result['rolled'] = $db->getAffectedRows();

            // Purge rolled-up raw events
            $purgeQuery = $db->getQuery(true)
                ->delete($db->quoteName('#__bsms_analytics_events'))
                ->where($db->quoteName('created') . ' < ' . $db->quote($cutoff));
            $db->setQuery($purgeQuery);
            $db->execute();
            $result['purged'] = $db->getAffectedRows();
        } catch (\Exception $e) {
            // Log to task output if needed
        }

        return $result;
    }

    /**
     * Seed the monthly aggregates table with legacy hit counts from existing study/media records.
     *
     * This is a one-time bridge from pre-10.1 aggregate counters into the new analytics tables.
     * Safe to run multiple times — uses ON DUPLICATE KEY UPDATE so counts are not double-added.
     *
     * Studies: `hits` column → page_view events (keyed to the study's studydate).
     * Media files: `downloads` column → download events, `plays` → play events (keyed to createdate).
     *
     * @return  array{studies: int, media: int}  Rows inserted/updated for studies and media.
     *
     * @since   10.1.0
     */
    public static function seedFromLegacy(): array
    {
        $result = ['studies' => 0, 'media' => 0];

        try {
            $db       = Factory::getContainer()->get('DatabaseDriver');
            $cols     = implode(',', array_map([$db, 'quoteName'], [
                'study_id', 'media_id', 'location_id', 'event_type',
                'referrer_type', 'country_code', 'device_type', 'year', 'month', 'count',
            ]));
            $dupeKey  = ' ON DUPLICATE KEY UPDATE ' . $db->quoteName('count') . ' = VALUES(' . $db->quoteName('count') . ')';

            // --- Seed study page-views from #__bsms_studies.hits ---
            $dateExpr = 'COALESCE(NULLIF(' . $db->quoteName('studydate') . ', ' . $db->quote('0000-00-00') . '),'
                      . $db->quoteName('createdate') . ', NOW())';

            $sql = 'INSERT INTO ' . $db->quoteName('#__bsms_analytics_monthly') . ' (' . $cols . ')'
                 . ' SELECT'
                 . ' ' . $db->quoteName('id') . ', NULL, ' . $db->quoteName('location_id') . ','
                 . ' ' . $db->quote('page_view') . ', NULL, NULL, NULL,'
                 . ' YEAR(' . $dateExpr . '), MONTH(' . $dateExpr . '),'
                 . ' ' . $db->quoteName('hits')
                 . ' FROM ' . $db->quoteName('#__bsms_studies')
                 . ' WHERE ' . $db->quoteName('hits') . ' > 0'
                 . $dupeKey;

            $db->setQuery($sql);
            $db->execute();
            $result['studies'] = $db->getAffectedRows();

            // --- Seed media downloads from #__bsms_mediafiles.downloads ---
            $dateMExpr = 'COALESCE(NULLIF(' . $db->quoteName('m.createdate') . ', ' . $db->quote('0000-00-00') . '), NOW())';

            $sqlDl = 'INSERT INTO ' . $db->quoteName('#__bsms_analytics_monthly') . ' (' . $cols . ')'
                   . ' SELECT'
                   . ' ' . $db->quoteName('m.study_id') . ', ' . $db->quoteName('m.id') . ', ' . $db->quoteName('s.location_id') . ','
                   . ' ' . $db->quote('download') . ', NULL, NULL, NULL,'
                   . ' YEAR(' . $dateMExpr . '), MONTH(' . $dateMExpr . '),'
                   . ' ' . $db->quoteName('m.downloads')
                   . ' FROM ' . $db->quoteName('#__bsms_mediafiles', 'm')
                   . ' LEFT JOIN ' . $db->quoteName('#__bsms_studies', 's')
                   . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id')
                   . ' WHERE ' . $db->quoteName('m.downloads') . ' > 0'
                   . $dupeKey;

            $db->setQuery($sqlDl);
            $db->execute();
            $result['media'] += $db->getAffectedRows();

            // --- Seed media plays from #__bsms_mediafiles.plays ---
            $sqlPl = 'INSERT INTO ' . $db->quoteName('#__bsms_analytics_monthly') . ' (' . $cols . ')'
                   . ' SELECT'
                   . ' ' . $db->quoteName('m.study_id') . ', ' . $db->quoteName('m.id') . ', ' . $db->quoteName('s.location_id') . ','
                   . ' ' . $db->quote('play') . ', NULL, NULL, NULL,'
                   . ' YEAR(' . $dateMExpr . '), MONTH(' . $dateMExpr . '),'
                   . ' ' . $db->quoteName('m.plays')
                   . ' FROM ' . $db->quoteName('#__bsms_mediafiles', 'm')
                   . ' LEFT JOIN ' . $db->quoteName('#__bsms_studies', 's')
                   . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id')
                   . ' WHERE ' . $db->quoteName('m.plays') . ' > 0'
                   . $dupeKey;

            $db->setQuery($sqlPl);
            $db->execute();
            $result['media'] += $db->getAffectedRows();
        } catch (\Exception $e) {
            // Never let this break the page
        }

        return $result;
    }

    /**
     * Get all-time KPI totals from the permanent monthly aggregates table.
     *
     * Returns historical totals imported via seedFromLegacy() plus any data
     * that has been rolled up from the raw events table. Used alongside
     * getKpiTotals() (which queries live raw events) to show combined numbers
     * on the dashboard when real tracked data is sparse.
     *
     * @param   int  $locationId  Filter by campus; 0 = all.
     *
     * @return  array{views: int, plays: int, downloads: int}
     *
     * @since   10.1.0
     */
    public static function getLegacyKpiTotals(int $locationId = 0): array
    {
        try {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select([
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('page_view') . ' THEN ' . $db->quoteName('count') . ' ELSE 0 END) AS views',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('play') . ' THEN ' . $db->quoteName('count') . ' ELSE 0 END) AS plays',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('download') . ' THEN ' . $db->quoteName('count') . ' ELSE 0 END) AS downloads',
                ])
                ->from($db->quoteName('#__bsms_analytics_monthly'));

            if ($locationId > 0) {
                $query->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            }

            $db->setQuery($query);
            $row = $db->loadAssoc() ?? [];

            return [
                'views'     => (int) ($row['views'] ?? 0),
                'plays'     => (int) ($row['plays'] ?? 0),
                'downloads' => (int) ($row['downloads'] ?? 0),
            ];
        } catch (\Exception $e) {
            return ['views' => 0, 'plays' => 0, 'downloads' => 0];
        }
    }

    /**
     * Check whether any real (post-10.1) tracked events exist in the raw events table.
     *
     * Used to determine whether to show the "analytics collecting data" notice
     * on the dashboard instead of empty charts.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public static function hasTrackedEvents(): bool
    {
        try {
            $db = Factory::getContainer()->get('DatabaseDriver');
            $db->setQuery('SELECT 1 FROM ' . $db->quoteName('#__bsms_analytics_events') . ' LIMIT 1');

            return $db->loadResult() !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Generic breakdown query helper.
     *
     * @param   string  $column      Column name to group by.
     * @param   string  $start       Start date.
     * @param   string  $end         End date.
     * @param   int     $locationId  Campus filter.
     *
     * @return  array<int, array<string, mixed>>
     *
     * @since   10.1.0
     */
    private static function getBreakdown(string $column, string $start, string $end, int $locationId): array
    {
        try {
            $allowed = [
                'referrer_type', 'country_code', 'device_type', 'browser', 'os', 'language',
            ];

            if (!\in_array($column, $allowed, true)) {
                return [];
            }

            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName($column),
                    'COUNT(*) AS count',
                ])
                ->from($db->quoteName('#__bsms_analytics_events'))
                ->where($db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59'))
                ->where($db->quoteName($column) . ' IS NOT NULL')
                ->group($db->quoteName($column))
                ->order('count DESC');

            if ($locationId > 0) {
                $query->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            } else {
                self::applyLocationFilter($query, $db);
            }

            $db->setQuery($query, 0, 20);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Apply campus (location) security filter for non-super-admin users.
     *
     * @param   \Joomla\Database\QueryInterface  $query  The database query.
     * @param   \Joomla\Database\DatabaseDriver  $db     Database driver.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private static function applyLocationFilter(
        \Joomla\Database\QueryInterface $query,
        \Joomla\Database\DatabaseDriver $db
    ): void {
        try {
            $user = Factory::getApplication()->getIdentity();

            if ($user && !$user->authorise('core.admin')) {
                $levels = $user->getAuthorisedViewLevels();

                if (!empty($levels)) {
                    // Restrict to studies in authorised view levels
                    $query->leftJoin(
                        $db->quoteName('#__bsms_locations', 'loc_sec') .
                        ' ON ' . $db->quoteName('loc_sec.id') . ' = ' . $db->quoteName('location_id')
                    )
                    ->whereIn($db->quoteName('loc_sec.access'), $levels);
                }
            }
        } catch (\Exception $e) {
            // Ignore — fail open (show all) if we can't determine user
        }
    }
}

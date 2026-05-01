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
     * Classifies UA, referrer, and GeoIP at log-time; raw signals never stored.
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
                    $referrerDomain = substr(ltrim($host, 'www.'), 0, 255);
                }
            }

            // Outbound click: repurpose destUrl as referrer_url column
            if ($type === 'outbound_click' && $destUrl !== '') {
                $referrerUrl = substr($destUrl, 0, 2048);
            }

            $db  = Factory::getContainer()->get(DatabaseInterface::class);
            $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

            $query = $db->getQuery(true)
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
    public static function classifyReferrer(string $url, string $utmMedium = ''): array
    {
        if ($url === '') {
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
     * Roll up raw events older than $retentionDays into monthly aggregates,
     * then purge the rolled-up raw events. Called by the scheduled task plugin.
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
            $db     = Factory::getContainer()->get(DatabaseInterface::class);
            $cutoff = (new \DateTime('now', new \DateTimeZone('UTC')))
                ->modify('-' . (int) $retentionDays . ' days')
                ->format('Y-m-d H:i:s');

            // Rollup: aggregate into monthly table using ON DUPLICATE KEY UPDATE
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
            $query = $db->getQuery(true)
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
            $query = $db->getQuery(true)
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
}

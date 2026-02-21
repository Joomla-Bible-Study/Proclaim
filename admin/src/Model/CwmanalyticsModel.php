<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\QueryInterface;

/**
 * Analytics dashboard model.
 *
 * Handles all data queries for the analytics dashboard view:
 * KPIs, time-series charts, dimension breakdowns, top-studies list,
 * legacy record totals, CSV export, and the legacy seed operation.
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmanalyticsModel extends BaseDatabaseModel
{
    /**
     * Load all published locations, optionally restricted to specific view levels.
     *
     * @param   int[]  $viewLevels  Empty array = all locations (super-admin).
     *
     * @return  object[]
     *
     * @since   10.1.0
     */
    public function getLocations(array $viewLevels = []): array
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('name')])
                ->from($db->quoteName('#__bsms_locations'))
                ->where($db->quoteName('published') . ' = 1')
                ->order($db->quoteName('name') . ' ASC');

            if (!empty($viewLevels)) {
                $query->whereIn($db->quoteName('access'), $viewLevels);
            }

            $db->setQuery($query);

            return (array) ($db->loadObjectList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all-time accumulated totals directly from the source record counters.
     *
     * Queries #__bsms_studies.hits (page views) and #__bsms_mediafiles plays/downloads.
     * These counters are always current regardless of whether event tracking is active.
     *
     * @param   int  $locationId  Filter by campus; 0 = all.
     *
     * @return  array{views: int, plays: int, downloads: int}
     *
     * @since   10.1.0
     */
    public function getRecordTotals(int $locationId = 0): array
    {
        $result = ['views' => 0, 'plays' => 0, 'downloads' => 0, 'platform_plays' => 0, 'external_plays' => 0];

        try {
            $db = $this->getDatabase();

            // Resolve accessible location IDs for non-admin users
            $accessibleIds = [];

            if ($locationId === 0) {
                try {
                    $user = Factory::getApplication()->getIdentity();

                    if ($user && !$user->authorise('core.admin') && CwmlocationHelper::isEnabled()) {
                        $accessibleIds = CwmlocationHelper::getUserLocations((int) $user->id);
                    }
                } catch (\Exception $e) {
                    // Fail open
                }
            }

            // Page views from studies
            $q = $db->getQuery(true)
                ->select('SUM(' . $db->quoteName('hits') . ')')
                ->from($db->quoteName('#__bsms_studies'));

            if ($locationId > 0) {
                $q->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            } elseif (!empty($accessibleIds)) {
                $q->where(
                    '(' . $db->quoteName('location_id') . ' IS NULL'
                    . ' OR ' . $db->quoteName('location_id') . ' IN ('
                    . implode(',', array_map('intval', $accessibleIds)) . '))'
                );
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
            } elseif (!empty($accessibleIds)) {
                $q2->leftJoin(
                    $db->quoteName('#__bsms_studies', 's') .
                    ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id')
                )->where(
                    '(' . $db->quoteName('s.location_id') . ' IS NULL'
                    . ' OR ' . $db->quoteName('s.location_id') . ' IN ('
                    . implode(',', array_map('intval', $accessibleIds)) . '))'
                );
            }

            $db->setQuery($q2);
            $row                 = $db->loadAssoc() ?? [];
            $result['plays']     = (int) ($row['plays'] ?? 0);
            $result['downloads'] = (int) ($row['downloads'] ?? 0);

            // Platform plays for ministry-created media (external plays = platform - local)
            $q3 = $db->getQuery(true)
                ->select([
                    'COALESCE(SUM(' . $db->quoteName('ps.play_count') . '), 0) AS platform_plays',
                    'GREATEST(COALESCE(SUM(' . $db->quoteName('ps.play_count') . '), 0)'
                    . ' - COALESCE(SUM(' . $db->quoteName('m2.plays') . '), 0), 0) AS external_plays',
                ])
                ->from($db->quoteName('#__bsms_platform_stats', 'ps'))
                ->leftJoin(
                    $db->quoteName('#__bsms_mediafiles', 'm2') .
                    ' ON ' . $db->quoteName('m2.id') . ' = ' . $db->quoteName('ps.media_id')
                )
                ->where($db->quoteName('m2.content_origin') . ' = 0')
                ->where($db->quoteName('m2.published') . ' = 1');

            if ($locationId > 0 || !empty($accessibleIds)) {
                $q3->leftJoin(
                    $db->quoteName('#__bsms_studies', 's2') .
                    ' ON ' . $db->quoteName('s2.id') . ' = ' . $db->quoteName('m2.study_id')
                );

                if ($locationId > 0) {
                    $q3->where($db->quoteName('s2.location_id') . ' = ' . (int) $locationId);
                } elseif (!empty($accessibleIds)) {
                    $q3->where(
                        '(' . $db->quoteName('s2.location_id') . ' IS NULL'
                        . ' OR ' . $db->quoteName('s2.location_id') . ' IN ('
                        . implode(',', array_map('intval', $accessibleIds)) . '))'
                    );
                }
            }

            $db->setQuery($q3);
            $psRow                     = $db->loadAssoc() ?? [];
            $result['platform_plays']  = (int) ($psRow['platform_plays'] ?? 0);
            $result['external_plays']  = (int) ($psRow['external_plays'] ?? 0);
        } catch (\Exception $e) {
            // Return zeros
        }

        return $result;
    }

    /**
     * Get KPI totals for the dashboard date range.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  Filter by campus; 0 = all authorised.
     *
     * @return  array{views: int, plays: int, downloads: int, sessions: int}
     *
     * @since   10.1.0
     */
    public function getKpiTotals(string $start, string $end, int $locationId = 0): array
    {
        try {
            $db    = $this->getDatabase();
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
                $this->applyLocationFilter($query, $db);
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
    public function getTimeSeries(string $start, string $end, int $locationId = 0): array
    {
        try {
            $days   = (int) ((strtotime($end) - strtotime($start)) / 86400);
            $format = $days <= 90 ? '%Y-%m-%d' : '%Y-%u';
            $db     = $this->getDatabase();

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
                $this->applyLocationFilter($query, $db);
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
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $limit       Maximum results.
     * @param   int     $locationId  Filter by campus; 0 = all authorised.
     *
     * @return  array<int, array{study_id: int, title: string, total: int}>
     *
     * @since   10.1.0
     */
    public function getTopStudies(string $start, string $end, int $limit = 10, int $locationId = 0): array
    {
        try {
            $db    = $this->getDatabase();
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
                ->where($db->quoteName('e.study_id') . ' IS NOT NULL');

            if ($locationId > 0) {
                $query->where($db->quoteName('e.location_id') . ' = ' . (int) $locationId);
            } else {
                $this->applyLocationFilter($query, $db);
            }

            $query->group($db->quoteName('e.study_id'))
                ->order('total DESC');
            $db->setQuery($query, 0, (int) $limit);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get top studies combining local analytics events with platform stats.
     *
     * Merges local event counts with platform play counts (for ministry-created
     * media only) to provide a comprehensive engagement ranking.  Each row
     * includes `local_total`, `platform_plays`, and the combined `total`.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $limit       Maximum results.
     * @param   int     $locationId  Filter by campus; 0 = all authorised.
     *
     * @return  array<int, array{study_id: int, title: string, local_total: int, platform_plays: int, total: int}>
     *
     * @since   10.1.0
     */
    public function getTopStudiesCombined(string $start, string $end, int $limit = 10, int $locationId = 0): array
    {
        try {
            $db = $this->getDatabase();

            // Sub-query 1: local analytics events per study (within date range)
            $localSub = $db->getQuery(true)
                ->select([
                    $db->quoteName('e.study_id'),
                    'COUNT(*) AS local_total',
                ])
                ->from($db->quoteName('#__bsms_analytics_events', 'e'))
                ->where($db->quoteName('e.created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('e.created') . ' <= ' . $db->quote($end . ' 23:59:59'))
                ->where($db->quoteName('e.study_id') . ' IS NOT NULL');

            if ($locationId > 0) {
                $localSub->where($db->quoteName('e.location_id') . ' = ' . (int) $locationId);
            }

            $localSub->group($db->quoteName('e.study_id'));

            // Sub-query 2: platform play counts per study (all-time, ministry-created only)
            $platSub = $db->getQuery(true)
                ->select([
                    $db->quoteName('mf.study_id'),
                    'COALESCE(SUM(' . $db->quoteName('ps.play_count') . '), 0) AS platform_plays',
                ])
                ->from($db->quoteName('#__bsms_platform_stats', 'ps'))
                ->innerJoin(
                    $db->quoteName('#__bsms_mediafiles', 'mf') .
                    ' ON ' . $db->quoteName('mf.id') . ' = ' . $db->quoteName('ps.media_id')
                )
                ->where($db->quoteName('mf.content_origin') . ' = 0')
                ->where($db->quoteName('mf.study_id') . ' IS NOT NULL')
                ->group($db->quoteName('mf.study_id'));

            // Outer query: FULL OUTER JOIN (emulated via LEFT JOIN + UNION)
            // Using a single query with LEFT JOINs from studies to both sub-queries
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('s.id', 'study_id'),
                    $db->quoteName('s.studytitle', 'title'),
                    'COALESCE(loc.local_total, 0) AS local_total',
                    'COALESCE(plat.platform_plays, 0) AS platform_plays',
                    '(COALESCE(loc.local_total, 0) + COALESCE(plat.platform_plays, 0)) AS total',
                ])
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->leftJoin('(' . $localSub . ') AS loc ON loc.study_id = ' . $db->quoteName('s.id'))
                ->leftJoin('(' . $platSub . ') AS plat ON plat.study_id = ' . $db->quoteName('s.id'))
                ->where('(COALESCE(loc.local_total, 0) + COALESCE(plat.platform_plays, 0)) > 0')
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
    public function getReferrerBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return $this->getBreakdown('referrer_type', $start, $end, $locationId);
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
    public function getCountryBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return $this->getBreakdown('country_code', $start, $end, $locationId);
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
    public function getDeviceBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return $this->getBreakdown('device_type', $start, $end, $locationId);
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
    public function getBrowserBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return $this->getBreakdown('browser', $start, $end, $locationId);
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
    public function getOsBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return $this->getBreakdown('os', $start, $end, $locationId);
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
    public function getLanguageBreakdown(string $start, string $end, int $locationId = 0): array
    {
        return $this->getBreakdown('language', $start, $end, $locationId);
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
    public function getUtmBreakdown(string $start, string $end, int $locationId = 0): array
    {
        try {
            $db    = $this->getDatabase();
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
                $this->applyLocationFilter($query, $db);
            }

            $db->setQuery($query, 0, 20);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all-time KPI totals from the permanent monthly aggregates table.
     *
     * @param   int  $locationId  Filter by campus; 0 = all.
     *
     * @return  array{views: int, plays: int, downloads: int}
     *
     * @since   10.1.0
     */
    public function getLegacyKpiTotals(int $locationId = 0): array
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('page_view') . ' THEN ' . $db->quoteName('count') . ' ELSE 0 END) AS views',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('play') . ' THEN ' . $db->quoteName('count') . ' ELSE 0 END) AS plays',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('download') . ' THEN ' . $db->quoteName('count') . ' ELSE 0 END) AS downloads',
                ])
                ->from($db->quoteName('#__bsms_analytics_monthly'));

            if ($locationId > 0) {
                $query->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
            } else {
                $this->applyLocationFilter($query, $db);
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
     * Return the date (Y-m-d) of the earliest tracked event, or '' if none exist.
     *
     * @return  string
     *
     * @since   10.1.0
     */
    public function getFirstEventDate(): string
    {
        try {
            $db = $this->getDatabase();
            $db->setQuery(
                'SELECT DATE(' . $db->quoteName('created') . ') FROM ' .
                $db->quoteName('#__bsms_analytics_events') .
                ' ORDER BY ' . $db->quoteName('created') . ' ASC LIMIT 1'
            );
            $result = $db->loadResult();

            return $result ?? '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Check whether any real (post-10.1) tracked events exist in the raw events table.
     *
     * @return  bool
     *
     * @since   10.1.0
     */
    public function hasTrackedEvents(): bool
    {
        try {
            $db = $this->getDatabase();
            $db->setQuery('SELECT 1 FROM ' . $db->quoteName('#__bsms_analytics_events') . ' LIMIT 1');

            return $db->loadResult() !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Seed the monthly aggregates table with legacy hit counts from existing study/media records.
     *
     * Safe to run multiple times. Because the unique index includes nullable columns and MySQL
     * treats NULL != NULL in unique indexes (preventing ON DUPLICATE KEY UPDATE from firing),
     * this method first removes any previously seeded rows — identified by device_type IS NULL,
     * which real event rollup rows never have — then re-inserts current counts from scratch.
     *
     * @return  array{studies: int, media: int}
     *
     * @since   10.1.0
     */
    public function seedFromLegacy(): array
    {
        $result = ['studies' => 0, 'media' => 0];

        try {
            $db   = $this->getDatabase();
            $cols = implode(',', array_map([$db, 'quoteName'], [
                'study_id', 'media_id', 'location_id', 'event_type',
                'referrer_type', 'country_code', 'device_type', 'year', 'month', 'count',
            ]));

            // Remove previously seeded rows before re-inserting fresh counts.
            // Seed rows are identified by device_type IS NULL — real rollup rows always
            // have device_type set by the UA classifier (never NULL).
            $db->setQuery(
                'DELETE FROM ' . $db->quoteName('#__bsms_analytics_monthly') .
                ' WHERE ' . $db->quoteName('device_type') . ' IS NULL' .
                ' AND ' . $db->quoteName('referrer_type') . ' IS NULL' .
                ' AND ' . $db->quoteName('country_code') . ' IS NULL'
            );
            $db->execute();

            // Seed study page-views from #__bsms_studies.hits
            $dateExpr = 'COALESCE(NULLIF(' . $db->quoteName('studydate') . ', ' . $db->quote('0000-00-00') . '),'
                . $db->quoteName('created') . ', NOW())';

            $sql = 'INSERT INTO ' . $db->quoteName('#__bsms_analytics_monthly') . ' (' . $cols . ')'
                . ' SELECT'
                . ' ' . $db->quoteName('id') . ', NULL, ' . $db->quoteName('location_id') . ','
                . ' ' . $db->quote('page_view') . ', NULL, NULL, NULL,'
                . ' YEAR(' . $dateExpr . '), MONTH(' . $dateExpr . '),'
                . ' ' . $db->quoteName('hits')
                . ' FROM ' . $db->quoteName('#__bsms_studies')
                . ' WHERE ' . $db->quoteName('hits') . ' > 0';

            $db->setQuery($sql);
            $db->execute();
            $result['studies'] = $db->getAffectedRows();

            // Seed media downloads from #__bsms_mediafiles.downloads
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
                . ' WHERE ' . $db->quoteName('m.downloads') . ' > 0';

            $db->setQuery($sqlDl);
            $db->execute();
            $result['media'] += $db->getAffectedRows();

            // Seed media plays from #__bsms_mediafiles.plays
            $sqlPl = 'INSERT INTO ' . $db->quoteName('#__bsms_analytics_monthly') . ' (' . $cols . ')'
                . ' SELECT'
                . ' ' . $db->quoteName('m.study_id') . ', ' . $db->quoteName('m.id') . ', ' . $db->quoteName('s.location_id') . ','
                . ' ' . $db->quote('play') . ', NULL, NULL, NULL,'
                . ' YEAR(' . $dateMExpr . '), MONTH(' . $dateMExpr . '),'
                . ' ' . $db->quoteName('m.plays')
                . ' FROM ' . $db->quoteName('#__bsms_mediafiles', 'm')
                . ' LEFT JOIN ' . $db->quoteName('#__bsms_studies', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id')
                . ' WHERE ' . $db->quoteName('m.plays') . ' > 0';

            $db->setQuery($sqlPl);
            $db->execute();
            $result['media'] += $db->getAffectedRows();
        } catch (\Exception $e) {
            // Never let this break the request
        }

        return $result;
    }

    /**
     * Export analytics data as a CSV download.
     *
     * Outputs CSV headers and data directly; the controller must call $app->close() after.
     *
     * @param   string  $start       Start date (Y-m-d).
     * @param   string  $end         End date (Y-m-d).
     * @param   int     $locationId  0 = all authorised.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function exportCsvString(string $start, string $end, int $locationId = 0): string
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__bsms_analytics_events'))
            ->where($db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00'))
            ->where($db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59'))
            ->order($db->quoteName('created') . ' ASC');

        if ($locationId > 0) {
            $query->where($db->quoteName('location_id') . ' = ' . (int) $locationId);
        } else {
            $this->applyLocationFilter($query, $db);
        }

        $db->setQuery($query);
        $rows = $db->loadAssocList() ?? [];

        $tmp = fopen('php://temp', 'w');

        if (!empty($rows)) {
            fputcsv($tmp, array_keys($rows[0]));

            foreach ($rows as $row) {
                fputcsv($tmp, $row);
            }
        }

        rewind($tmp);
        $csv = stream_get_contents($tmp);
        fclose($tmp);

        return (string) $csv;
    }

    /**
     * Generic breakdown query helper.
     *
     * @param   string  $column      Column name to group by (whitelist-validated).
     * @param   string  $start       Start date.
     * @param   string  $end         End date.
     * @param   int     $locationId  Campus filter.
     *
     * @return  array<int, array<string, mixed>>
     *
     * @since   10.1.0
     */
    private function getBreakdown(string $column, string $start, string $end, int $locationId): array
    {
        try {
            $allowed = [
                'referrer_type', 'country_code', 'device_type', 'browser', 'os', 'language',
            ];

            if (!\in_array($column, $allowed, true)) {
                return [];
            }

            $db    = $this->getDatabase();
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
                $this->applyLocationFilter($query, $db);
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
     * @param   QueryInterface                   $query  The database query.
     * @param   \Joomla\Database\DatabaseDriver  $db     Database driver.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private function applyLocationFilter(QueryInterface $query, \Joomla\Database\DatabaseDriver $db): void
    {
        try {
            $user = Factory::getApplication()->getIdentity();

            if ($user && !$user->authorise('core.admin') && CwmlocationHelper::isEnabled()) {
                $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                if (!empty($accessible)) {
                    $query->where(
                        '(' . $db->quoteName('location_id') . ' IS NULL'
                        . ' OR ' . $db->quoteName('location_id') . ' IN ('
                        . implode(',', array_map('intval', $accessible)) . '))'
                    );
                }
            }
        } catch (\Exception $e) {
            // Fail open — show all if we can't determine user
        }
    }

    /**
     * Get all published series with their engagement totals for the date range.
     *
     * @since 10.1.0
     */
    public function getSeriesList(string $start, string $end, int $locationId = 0): array
    {
        try {
            $db = $this->getDatabase();

            $locationFilter = $locationId > 0
                ? ' AND EXISTS (SELECT 1 FROM ' . $db->quoteName('#__bsms_studies') . ' sl2'
                  . ' WHERE sl2.' . $db->quoteName('series_id') . ' = sr.' . $db->quoteName('id')
                  . ' AND sl2.' . $db->quoteName('location_id') . ' = ' . (int) $locationId
                  . ' AND sl2.' . $db->quoteName('published') . ' = 1)'
                : '';

            // Raw SQL avoids query-builder quirks with correlated scalar subqueries.
            // One LEFT JOIN on series_id (no study JOIN) prevents Cartesian product.
            $sql = 'SELECT'
                . ' sr.' . $db->quoteName('id') . ' AS series_id,'
                . ' sr.' . $db->quoteName('series_text') . ' AS title,'
                . ' sr.' . $db->quoteName('series_thumbnail') . ' AS thumb,'
                . ' (SELECT COUNT(*) FROM ' . $db->quoteName('#__bsms_studies') . ' sc'
                . '  WHERE sc.' . $db->quoteName('series_id') . ' = sr.' . $db->quoteName('id')
                . '  AND sc.' . $db->quoteName('published') . ' IN (1, 2)) AS message_count,'
                . ' SUM(CASE WHEN e.' . $db->quoteName('event_type') . ' = ' . $db->quote('page_view') . ' THEN 1 ELSE 0 END) AS views,'
                . ' SUM(CASE WHEN e.' . $db->quoteName('event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) AS plays,'
                . ' SUM(CASE WHEN e.' . $db->quoteName('event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END) AS downloads,'
                . ' COALESCE((SELECT SUM(ps2.play_count)'
                . '  FROM ' . $db->quoteName('#__bsms_platform_stats') . ' ps2'
                . '  INNER JOIN ' . $db->quoteName('#__bsms_mediafiles') . ' mf2'
                . '    ON mf2.id = ps2.media_id AND mf2.content_origin = 0'
                . '  INNER JOIN ' . $db->quoteName('#__bsms_studies') . ' st2'
                . '    ON st2.id = mf2.study_id'
                . '  WHERE st2.series_id = sr.id), 0) AS platform_plays'
                . ' FROM ' . $db->quoteName('#__bsms_series') . ' sr'
                . ' LEFT JOIN ' . $db->quoteName('#__bsms_analytics_events') . ' e'
                . '   ON e.' . $db->quoteName('series_id') . ' = sr.' . $db->quoteName('id')
                . '   AND e.' . $db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00')
                . '   AND e.' . $db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59')
                . ' WHERE sr.' . $db->quoteName('published') . ' = 1'
                . $locationFilter
                . ' GROUP BY sr.' . $db->quoteName('id')
                . ' ORDER BY COUNT(e.' . $db->quoteName('id') . ') DESC'
                . ' LIMIT 100';

            $db->setQuery($sql);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get series info (title, thumb) for breadcrumb display.
     *
     * @since 10.1.0
     */
    public function getSeriesInfo(int $seriesId): ?object
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([$db->quoteName('id'), $db->quoteName('series_text', 'title'), $db->quoteName('series_thumbnail', 'thumb')])
                ->from($db->quoteName('#__bsms_series'))
                ->where($db->quoteName('id') . ' = ' . (int) $seriesId);
            $db->setQuery($query);

            return $db->loadObject() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get published messages in a series with per-period engagement stats.
     *
     * @since 10.1.0
     */
    public function getSeriesMessages(int $seriesId, string $start, string $end): array
    {
        try {
            $db = $this->getDatabase();

            // Correlated sub-select for platform play counts per study (ministry-created only)
            $platformSub = 'COALESCE((SELECT SUM(ps2.' . $db->quoteName('play_count') . ')'
                . ' FROM ' . $db->quoteName('#__bsms_platform_stats') . ' ps2'
                . ' INNER JOIN ' . $db->quoteName('#__bsms_mediafiles') . ' mf2'
                . '   ON mf2.' . $db->quoteName('id') . ' = ps2.' . $db->quoteName('media_id')
                . '   AND mf2.' . $db->quoteName('content_origin') . ' = 0'
                . ' WHERE mf2.' . $db->quoteName('study_id') . ' = ' . $db->quoteName('s.id')
                . '), 0)';

            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('s.id', 'study_id'),
                    $db->quoteName('s.studytitle', 'title'),
                    $db->quoteName('s.studydate', 'study_date'),
                    $db->quoteName('s.published'),
                    $db->quoteName('s.hits', 'all_time_views'),
                    'SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('page_view') . ' THEN 1 ELSE 0 END) AS views',
                    'SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) AS plays',
                    'SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END) AS downloads',
                    $platformSub . ' AS platform_plays',
                ])
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->leftJoin(
                    $db->quoteName('#__bsms_analytics_events', 'e') .
                    ' ON ' . $db->quoteName('e.study_id') . ' = ' . $db->quoteName('s.id') .
                    ' AND ' . $db->quoteName('e.created') . ' >= ' . $db->quote($start . ' 00:00:00') .
                    ' AND ' . $db->quoteName('e.created') . ' <= ' . $db->quote($end . ' 23:59:59')
                )
                ->where($db->quoteName('s.series_id') . ' = ' . (int) $seriesId)
                ->whereIn($db->quoteName('s.published'), [1, 2])
                ->group($db->quoteName('s.id'))
                ->order($db->quoteName('s.studydate') . ' DESC');
            $db->setQuery($query);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get study info (title, date, series title) for breadcrumb and message detail header.
     *
     * @since 10.1.0
     */
    public function getStudyInfo(int $studyId): ?object
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('s.id'),
                    $db->quoteName('s.studytitle', 'title'),
                    $db->quoteName('s.studydate', 'study_date'),
                    $db->quoteName('s.hits', 'all_time_views'),
                    $db->quoteName('s.series_id'),
                    $db->quoteName('sr.series_text', 'series_title'),
                ])
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->leftJoin(
                    $db->quoteName('#__bsms_series', 'sr') .
                    ' ON ' . $db->quoteName('sr.id') . ' = ' . $db->quoteName('s.series_id')
                )
                ->where($db->quoteName('s.id') . ' = ' . (int) $studyId);
            $db->setQuery($query);

            return $db->loadObject() ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get KPI totals for a single message.
     *
     * @return array{views: int, plays: int, downloads: int, sessions: int}
     * @since 10.1.0
     */
    public function getStudyKpi(int $studyId, string $start, string $end): array
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('page_view') . ' THEN 1 ELSE 0 END) AS views',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) AS plays',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END) AS downloads',
                    'COUNT(DISTINCT ' . $db->quoteName('session_hash') . ') AS sessions',
                ])
                ->from($db->quoteName('#__bsms_analytics_events'))
                ->where($db->quoteName('study_id') . ' = ' . (int) $studyId)
                ->where($db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59'));
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
     * Get daily/weekly time-series for a single message.
     *
     * @since 10.1.0
     */
    public function getStudyTimeSeries(int $studyId, string $start, string $end): array
    {
        try {
            $days   = (int) ((strtotime($end) - strtotime($start)) / 86400);
            $format = $days <= 90 ? '%Y-%m-%d' : '%Y-%u';
            $db     = $this->getDatabase();
            $query  = $db->getQuery(true)
                ->select([
                    'DATE_FORMAT(' . $db->quoteName('created') . ', ' . $db->quote($format) . ') AS period',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('page_view') . ' THEN 1 ELSE 0 END) AS views',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) AS plays',
                    'SUM(CASE WHEN ' . $db->quoteName('event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END) AS downloads',
                ])
                ->from($db->quoteName('#__bsms_analytics_events'))
                ->where($db->quoteName('study_id') . ' = ' . (int) $studyId)
                ->where($db->quoteName('created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('created') . ' <= ' . $db->quote($end . ' 23:59:59'))
                ->group('period')
                ->order('period ASC');
            $db->setQuery($query);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get media files for a study with period engagement stats.
     *
     * @since 10.1.0
     */
    public function getStudyMediaFiles(int $studyId, string $start, string $end): array
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('m.id', 'media_id'),
                    $db->quoteName('m.params', 'media_params'),
                    $db->quoteName('m.plays', 'all_time_plays'),
                    $db->quoteName('m.downloads', 'all_time_downloads'),
                    $db->quoteName('m.content_origin'),
                    $db->quoteName('m.ordering'),
                    $db->quoteName('sv.server_name'),
                    $db->quoteName('sv.type', 'server_type'),
                    'SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) AS period_plays',
                    'SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END) AS period_downloads',
                    'COALESCE(MAX(' . $db->quoteName('ps.play_count') . '), 0) AS platform_play_count',
                    'GREATEST(COALESCE(MAX(' . $db->quoteName('ps.play_count') . '), 0) - ' . $db->quoteName('m.plays') . ', 0) AS external_plays',
                    $db->quoteName('m.plays') . ' + GREATEST(COALESCE(MAX(' . $db->quoteName('ps.play_count') . '), 0) - ' . $db->quoteName('m.plays') . ', 0) AS total_reach',
                ])
                ->from($db->quoteName('#__bsms_mediafiles', 'm'))
                ->leftJoin(
                    $db->quoteName('#__bsms_servers', 'sv') .
                    ' ON ' . $db->quoteName('sv.id') . ' = ' . $db->quoteName('m.server_id')
                )
                ->leftJoin(
                    $db->quoteName('#__bsms_analytics_events', 'e') .
                    ' ON ' . $db->quoteName('e.media_id') . ' = ' . $db->quoteName('m.id') .
                    ' AND ' . $db->quoteName('e.created') . ' >= ' . $db->quote($start . ' 00:00:00') .
                    ' AND ' . $db->quoteName('e.created') . ' <= ' . $db->quote($end . ' 23:59:59')
                )
                ->leftJoin(
                    $db->quoteName('#__bsms_platform_stats', 'ps') .
                    ' ON ' . $db->quoteName('ps.media_id') . ' = ' . $db->quoteName('m.id')
                )
                ->where($db->quoteName('m.study_id') . ' = ' . (int) $studyId)
                ->where($db->quoteName('m.published') . ' = 1')
                ->group($db->quoteName('m.id'))
                ->order($db->quoteName('m.ordering') . ' ASC');
            $db->setQuery($query);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get all published servers whose addon supports stats retrieval.
     *
     * Delegates to the addon base class for auto-discovery.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    public function getStatsCapableServers(): array
    {
        try {
            return CWMAddon::getStatsCapableServers();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get platform statistics summary aggregated by platform.
     *
     * @param   int  $locationId  Filter by campus; 0 = all.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    public function getPlatformStatsSummary(int $locationId = 0): array
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('ps.platform'),
                    'COUNT(*) AS media_count',
                    'SUM(' . $db->quoteName('ps.view_count') . ') AS total_views',
                    'SUM(' . $db->quoteName('ps.play_count') . ') AS total_plays',
                    'SUM(' . $db->quoteName('ps.like_count') . ') AS total_likes',
                    'MAX(' . $db->quoteName('ps.synced_at') . ') AS last_synced',
                ])
                ->from($db->quoteName('#__bsms_platform_stats', 'ps'));

            // Resolve whether we need the study join for location filtering
            $needsStudyJoin = false;
            $accessibleIds  = [];

            if ($locationId > 0) {
                $needsStudyJoin = true;
            } else {
                try {
                    $user = Factory::getApplication()->getIdentity();

                    if ($user && !$user->authorise('core.admin') && CwmlocationHelper::isEnabled()) {
                        $accessibleIds = CwmlocationHelper::getUserLocations((int) $user->id);

                        if (!empty($accessibleIds)) {
                            $needsStudyJoin = true;
                        }
                    }
                } catch (\Exception $e) {
                    // Fail open
                }
            }

            if ($needsStudyJoin) {
                $query->leftJoin(
                    $db->quoteName('#__bsms_mediafiles', 'm') .
                    ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('ps.media_id')
                )
                ->leftJoin(
                    $db->quoteName('#__bsms_studies', 's') .
                    ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id')
                );

                if ($locationId > 0) {
                    $query->where($db->quoteName('s.location_id') . ' = ' . (int) $locationId);
                } elseif (!empty($accessibleIds)) {
                    $query->where(
                        '(' . $db->quoteName('s.location_id') . ' IS NULL'
                        . ' OR ' . $db->quoteName('s.location_id') . ' IN ('
                        . implode(',', array_map('intval', $accessibleIds)) . '))'
                    );
                }
            }

            $query->group($db->quoteName('ps.platform'))
                ->order('total_views DESC');

            $db->setQuery($query);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get per-media platform stats for a specific study.
     *
     * @param   int  $studyId  The study ID.
     *
     * @return  array
     *
     * @since   10.1.0
     */
    public function getPlatformStatsForStudy(int $studyId): array
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('ps.media_id'),
                    $db->quoteName('ps.platform'),
                    $db->quoteName('ps.platform_id'),
                    $db->quoteName('ps.view_count'),
                    $db->quoteName('ps.play_count'),
                    $db->quoteName('ps.like_count'),
                    $db->quoteName('ps.comment_count'),
                    $db->quoteName('ps.load_count'),
                    $db->quoteName('ps.hours_watched'),
                    $db->quoteName('ps.engagement'),
                    $db->quoteName('ps.synced_at'),
                    $db->quoteName('sv.server_name'),
                ])
                ->from($db->quoteName('#__bsms_platform_stats', 'ps'))
                ->leftJoin(
                    $db->quoteName('#__bsms_mediafiles', 'm') .
                    ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('ps.media_id')
                )
                ->leftJoin(
                    $db->quoteName('#__bsms_servers', 'sv') .
                    ' ON ' . $db->quoteName('sv.id') . ' = ' . $db->quoteName('ps.server_id')
                )
                ->where($db->quoteName('m.study_id') . ' = ' . (int) $studyId)
                ->order($db->quoteName('ps.platform') . ' ASC');

            $db->setQuery($query);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get engagement breakdown by media server type (YouTube, Vimeo, MP3, etc.).
     *
     * @since 10.1.0
     */
    public function getMediaTypeBreakdown(string $start, string $end, int $locationId = 0): array
    {
        try {
            $db    = $this->getDatabase();

            // Correlated sub-select for platform play counts per server (ministry-created only)
            $platformSub = 'COALESCE((SELECT SUM(ps2.' . $db->quoteName('play_count') . ')'
                . ' FROM ' . $db->quoteName('#__bsms_platform_stats') . ' ps2'
                . ' INNER JOIN ' . $db->quoteName('#__bsms_mediafiles') . ' mf2'
                . '   ON mf2.' . $db->quoteName('id') . ' = ps2.' . $db->quoteName('media_id')
                . '   AND mf2.' . $db->quoteName('content_origin') . ' = 0'
                . ' WHERE mf2.' . $db->quoteName('server_id') . ' = sv.' . $db->quoteName('id')
                . '), 0)';

            $query = $db->getQuery(true)
                ->select([
                    'COALESCE(' . $db->quoteName('sv.server_name') . ', ' . $db->quote('Unknown') . ') AS server_name',
                    'COALESCE(' . $db->quoteName('sv.type') . ', ' . $db->quote('other') . ') AS server_type',
                    'SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) AS plays',
                    'SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END) AS downloads',
                    'COUNT(DISTINCT ' . $db->quoteName('e.media_id') . ') AS media_count',
                    'COUNT(DISTINCT ' . $db->quoteName('e.study_id') . ') AS study_count',
                    $platformSub . ' AS platform_plays',
                ])
                ->from($db->quoteName('#__bsms_analytics_events', 'e'))
                ->leftJoin(
                    $db->quoteName('#__bsms_mediafiles', 'm') .
                    ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('e.media_id')
                )
                ->leftJoin(
                    $db->quoteName('#__bsms_servers', 'sv') .
                    ' ON ' . $db->quoteName('sv.id') . ' = ' . $db->quoteName('m.server_id')
                )
                ->where($db->quoteName('e.created') . ' >= ' . $db->quote($start . ' 00:00:00'))
                ->where($db->quoteName('e.created') . ' <= ' . $db->quote($end . ' 23:59:59'))
                ->where($db->quoteName('e.media_id') . ' IS NOT NULL')
                ->group(['COALESCE(' . $db->quoteName('sv.server_name') . ', ' . $db->quote('Unknown') . ')', 'COALESCE(' . $db->quoteName('sv.type') . ', ' . $db->quote('other') . ')'])
                ->order('(SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('play') . ' THEN 1 ELSE 0 END) + SUM(CASE WHEN ' . $db->quoteName('e.event_type') . ' = ' . $db->quote('download') . ' THEN 1 ELSE 0 END)) DESC');

            if ($locationId > 0) {
                $query->where($db->quoteName('e.location_id') . ' = ' . (int) $locationId);
            } else {
                $this->applyLocationFilter($query, $db);
            }

            $db->setQuery($query, 0, 30);

            return (array) ($db->loadAssocList() ?? []);
        } catch (\Exception $e) {
            return [];
        }
    }
}

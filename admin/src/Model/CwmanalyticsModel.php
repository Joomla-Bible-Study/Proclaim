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
        $result = ['views' => 0, 'plays' => 0, 'downloads' => 0];

        try {
            $db = $this->getDatabase();

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
     * @param   string  $start   Start date (Y-m-d).
     * @param   string  $end     End date (Y-m-d).
     * @param   int     $limit   Maximum results.
     *
     * @return  array<int, array{study_id: int, title: string, total: int}>
     *
     * @since   10.1.0
     */
    public function getTopStudies(string $start, string $end, int $limit = 10): array
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
                . $db->quoteName('createdate') . ', NOW())';

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
    public function exportCsv(string $start, string $end, int $locationId = 0): void
    {
        try {
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
            // Fail silently; controller will close the response
        }
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

            if ($user && !$user->authorise('core.admin')) {
                $levels = $user->getAuthorisedViewLevels();

                if (!empty($levels)) {
                    $query->leftJoin(
                        $db->quoteName('#__bsms_locations', 'loc_sec') .
                        ' ON ' . $db->quoteName('loc_sec.id') . ' = ' . $db->quoteName('location_id')
                    )
                    ->whereIn($db->quoteName('loc_sec.access'), $levels);
                }
            }
        } catch (\Exception $e) {
            // Fail open — show all if we can't determine user
        }
    }
}

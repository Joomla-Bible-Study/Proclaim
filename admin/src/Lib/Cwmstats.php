<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Lib;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/**
 * Bible Study stats support class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class Cwmstats
{
    /**
     * Static cache for query results within the same request.
     *
     * @var array<string, mixed>
     * @since 10.1.0
     */
    private static array $cache = [];

    /** @var int used to store the query of messages
     *
     * @since 9.0.0
     */
    private static int $total_messages = 0;

    /** @var string Start Date
     *
     * @since 9.0.0
     */
    private static string $total_messages_start = '';

    /** @var string End Date
     *
     * @since 9.0.0
     */
    private static string $total_messages_end = '';

    /**
     * Get a Joomla persistent cache controller for cross-request caching.
     *
     * @param   int  $lifetime  Cache lifetime in seconds.
     *
     * @return  CallbackController
     *
     * @since   10.1.0
     */
    private static function getPersistentCache(int $lifetime = 900): CallbackController
    {
        /** @var CallbackController $cache */
        $cache = Factory::getContainer()
            ->get(CacheControllerFactoryInterface::class)
            ->createCacheController('callback', [
                'defaultgroup' => 'com_proclaim',
                'caching'      => true,
            ]);
        $cache->setLifeTime($lifetime);

        return $cache;
    }

    /**
     * Total plays of media files per study
     *
     * @param   int  $id  ID number of study
     *
     * @return int Total plays from the media
     *
     * @since 9.0.0
     */
    public static function totalPlays(int $id): int
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('SUM(' . $db->quoteName('m.plays') . ')')
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('m.study_id') . ' = ' . $db->quoteName('s.id'))
            ->where($db->quoteName('m.study_id') . ' = ' . (int) $id);
        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Total messages in Bible Study
     *
     * @param   string  $start  Start Time/Date of Study
     * @param   string  $end    End Time/Date of Study
     *
     * @return int Total Messages
     *
     * @since 9.0.0
     */
    public static function getTotalMessages(string $start = '', string $end = ''): int
    {
        $user    = Factory::getApplication()->getIdentity();
        $isAdmin = $user->authorise('core.admin');

        // Delegate to CwmcountHelper for simple published count (no date filtering)
        // Note: CwmcountHelper does NOT filter by access, so only use for super admins
        if (empty($start) && empty($end) && $isAdmin) {
            return CwmcountHelper::getCountByState('#__bsms_studies', 1);
        }

        if ($start !== self::$total_messages_start || $end !== self::$total_messages_end || !self::$total_messages) {
            self::$total_messages_start = $start;
            self::$total_messages_end   = $end;

            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->where($db->quoteName('s.published') . ' = 1');

            // Apply hybrid security filter: location-based + Joomla view-level access
            CwmlocationHelper::applySecurityFilter($query, 's');

            if (!empty($start)) {
                $query->where($db->quoteName('s.time') . ' > UNIX_TIMESTAMP(' . $db->quote($start) . ')');
            }

            if (!empty($end)) {
                $query->where($db->quoteName('s.time') . ' < UNIX_TIMESTAMP(' . $db->quote($end) . ')');
            }

            $db->setQuery($query);
            self::$total_messages = (int) $db->loadResult();
        }

        return self::$total_messages;
    }

    /**
     * Total topics in Bible Study
     *
     * @param   string  $start  Start Time/Date of Study
     * @param   string  $end    End Time/Date of Study
     *
     * @return int  Total Topics
     *
     * @since 9.0.0
     */
    public static function getTotalTopics(string $start = '', string $end = ''): int
    {
        $user    = Factory::getApplication()->getIdentity();
        $isAdmin = $user->authorise('core.admin');

        // Include user ID in cache key for non-admin users
        $userId  = (int) $user->id;
        $userKey = $isAdmin ? 'admin' : 'uid:' . $userId;
        $key     = 'totalTopics:' . $start . ':' . $end . ':' . $userKey;

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        // L2: persistent cache across requests (TTL: 15 min)
        $pc     = self::getPersistentCache();
        $result = $pc->get(function () use ($start, $end) {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->leftJoin($db->quoteName('#__bsms_studytopics', 'st') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('st.study_id'))
                ->leftJoin($db->quoteName('#__bsms_topics', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.topic_id'))
                ->where($db->quoteName('t.published') . ' = 1');

            // Apply hybrid security filter: location-based + Joomla view-level access
            CwmlocationHelper::applySecurityFilter($query, 's');

            if (!empty($start)) {
                $query->where($db->quoteName('s.time') . ' > UNIX_TIMESTAMP(' . $db->quote($start) . ')');
            }

            if (!empty($end)) {
                $query->where($db->quoteName('s.time') . ' < UNIX_TIMESTAMP(' . $db->quote($end) . ')');
            }

            $db->setQuery($query);

            return (int) $db->loadResult();
        }, [], md5($key));

        self::$cache[$key] = $result;

        return $result;
    }

    /**
     * Get top studies
     *
     * @return string HTML Format
     *
     * @since 9.0.0
     */
    public static function getTopStudies(): string
    {
        $user    = Factory::getApplication()->getIdentity();
        $isAdmin = $user->authorise('core.admin');

        // Include user ID in cache key for non-admin users
        $userId   = (int) $user->id;
        $userKey  = $isAdmin ? 'admin' : 'uid:' . $userId;
        $cacheKey = 'topStudies:' . $userKey;

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // L2: persistent cache across requests (TTL: 15 min)
        $pc     = self::getPersistentCache();
        $result = $pc->get(function () {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName(['s.id', 's.studytitle', 's.studydate', 's.hits', 's.access']))
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->where($db->quoteName('s.published') . ' = 1')
                ->where($db->quoteName('s.hits') . ' > 0');

            // Apply hybrid security filter: location-based + Joomla view-level access
            CwmlocationHelper::applySecurityFilter($query, 's');

            $query->order($db->quoteName('s.hits') . ' DESC');
            $db->setQuery($query, 0, 5);
            $rows        = $db->loadObjectList();
            $top_studies = '';

            foreach ($rows as $row) {
                $top_studies .= (int) $row->hits . ' ' . Text::_('JBS_CMN_HITS') .
                    ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . (int) $row->id . '">' .
                    htmlspecialchars($row->studytitle, ENT_QUOTES, 'UTF-8') . '</a> - ' . date('Y-m-d', strtotime($row->studydate)) . '<br>';
            }

            return $top_studies;
        }, [], md5($cacheKey));

        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Total comments
     *
     * @return int
     *
     * @since 9.0.0
     */
    public static function getTotalComments(): int
    {
        return CwmcountHelper::getCountByState('#__bsms_comments', 1);
    }

    /**
     * Get top thirty days
     *
     * @return string HTML Format
     *
     * @since 9.0.0
     */
    public static function getTopThirtyDays(): string
    {
        $user    = Factory::getApplication()->getIdentity();
        $isAdmin = $user->authorise('core.admin');

        // Include user ID in cache key for non-admin users
        $userId   = (int) $user->id;
        $userKey  = $isAdmin ? 'admin' : 'uid:' . $userId;
        $cacheKey = 'topThirtyDays:' . $userKey;

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // L2: persistent cache across requests (TTL: 15 min)
        $pc     = self::getPersistentCache();
        $result = $pc->get(function () {
            $month      = mktime(0, 0, 0, (int) date("m") - 1, (int) date("d"), (int) date("Y"));
            $last_month = date("Y-m-d 00:00:01", $month);
            $db         = Factory::getContainer()->get('DatabaseDriver');
            $query      = $db->getQuery(true);
            $query
                ->select($db->quoteName(['s.id', 's.studytitle', 's.studydate', 's.hits', 's.access']))
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->where($db->quoteName('s.published') . ' = 1')
                ->where($db->quoteName('s.hits') . ' > 0')
                ->where($db->quoteName('s.studydate') . ' > ' . $db->quote($last_month));

            // Apply hybrid security filter: location-based + Joomla view-level access
            CwmlocationHelper::applySecurityFilter($query, 's');

            $query->order($db->quoteName('s.hits') . ' DESC');
            $db->setQuery($query, 0, 5);
            $rows        = $db->loadObjectList();
            $top_studies = '';

            if (!$rows) {
                $top_studies = Text::_('JBS_CPL_NO_INFORMATION');
            } else {
                foreach ($rows as $row) {
                    $top_studies .= (int) $row->hits . ' ' . Text::_('JBS_CMN_HITS') .
                        ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . (int) $row->id . '">' .
                        htmlspecialchars($row->studytitle, ENT_QUOTES, 'UTF-8') . '</a> - ' . date('Y-m-d', strtotime($row->studydate)) . '<br>';
                }
            }

            return $top_studies;
        }, [], md5($cacheKey));

        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Get Total Media Files
     *
     * @return int Number of Records under Media Files that are published.
     *
     * @since 9.0.0
     */
    public static function getTotalMediaFiles(): int
    {
        return CwmcountHelper::getCountByState('#__bsms_mediafiles', 1);
    }

    /**
     * Get Top Downloads
     *
     * @return string HTML List of links to the downloads
     *
     * @since 9.0.0
     */
    public static function getTopDownloads(): string
    {
        $user    = Factory::getApplication()->getIdentity();
        $isAdmin = $user->authorise('core.admin');

        // Include user ID in cache key for non-admin users
        $userId   = (int) $user->id;
        $userKey  = $isAdmin ? 'admin' : 'uid:' . $userId;
        $cacheKey = 'topDownloads:' . $userKey;

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // L2: persistent cache across requests (TTL: 15 min)
        $pc     = self::getPersistentCache();
        $result = $pc->get(function () {
            $db    = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query
                ->select($db->quoteName(['mf.downloads']))
                ->select($db->quoteName('s.id', 'sid'))
                ->select($db->quoteName('s.studytitle', 'stitle'))
                ->select($db->quoteName('s.studydate', 'sdate'))
                ->select($db->quoteName('s.access', 'saccess'))
                ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
                ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('mf.study_id') . ' = ' . $db->quoteName('s.id'))
                ->where($db->quoteName('mf.published') . ' = 1')
                ->where($db->quoteName('mf.downloads') . ' > 0');

            // Apply hybrid security filter: location-based + Joomla view-level access
            CwmlocationHelper::applySecurityFilter($query, 's');

            $query->order($db->quoteName('mf.downloads') . ' DESC');
            $db->setQuery($query, 0, 5);
            $rows        = $db->loadObjectList();
            $top_studies = '';

            foreach ($rows as $row) {
                $top_studies .=
                    (int) $row->downloads . ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' .
                    (int) $row->sid . '">' . htmlspecialchars($row->stitle, ENT_QUOTES, 'UTF-8') . '</a> - ' . date('Y-m-d', strtotime($row->sdate)) .
                    '<br>';
            }

            return $top_studies;
        }, [], md5($cacheKey));

        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Get Downloads Last three Months
     *
     * @return  string HTML list of download links
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public static function getDownloadsLastThreeMonths(): string
    {
        $user    = Factory::getApplication()->getIdentity();
        $isAdmin = $user->authorise('core.admin');

        // Include user ID in cache key for non-admin users
        $userId   = (int) $user->id;
        $userKey  = $isAdmin ? 'admin' : 'uid:' . $userId;
        $cacheKey = 'downloadsLast3Months:' . $userKey;

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // L2: persistent cache across requests (TTL: 15 min)
        $pc     = self::getPersistentCache();
        $result = $pc->get(function () {
            $month     = mktime(0, 0, 0, (int) date("m") - 3, (int) date("d"), (int) date("Y"));
            $lastmonth = date("Y-m-d 00:00:01", $month);
            $db        = Factory::getContainer()->get('DatabaseDriver');
            $query     = $db->getQuery(true);
            $query
                ->select($db->quoteName(['mf.downloads']))
                ->select($db->quoteName('s.id', 'sid'))
                ->select($db->quoteName('s.studytitle', 'stitle'))
                ->select($db->quoteName('s.studydate', 'sdate'))
                ->select($db->quoteName('s.access', 'saccess'))
                ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
                ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('mf.study_id') . ' = ' . $db->quoteName('s.id'))
                ->where($db->quoteName('mf.published') . ' = 1')
                ->where($db->quoteName('mf.downloads') . ' > 0')
                ->where($db->quoteName('mf.createdate') . ' > ' . $db->quote($lastmonth));

            // Apply hybrid security filter: location-based + Joomla view-level access
            CwmlocationHelper::applySecurityFilter($query, 's');

            $query->order($db->quoteName('mf.downloads') . ' DESC');
            $db->setQuery($query, 0, 5);
            $rows        = $db->loadObjectList();
            $top_studies = '';

            if (!$rows) {
                $top_studies = Text::_('JBS_CPL_NO_INFORMATION');
            } else {
                foreach ($rows as $row) {
                    $top_studies .= (int) $row->downloads . ' ' . Text::_('JBS_CMN_HITS') .
                        ' - <a href="index.php?option=com_proclaim&amp;task=message.edit&amp;id=' . (int) $row->sid . '">' .
                        htmlspecialchars($row->stitle, ENT_QUOTES, 'UTF-8') . '</a> - ' . date('Y-m-d', strtotime($row->sdate)) . '<br>';
                }
            }

            return $top_studies;
        }, [], md5($cacheKey));

        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Total Downloads
     *
     * @return  int Number of Media Files Downloaded in Published state
     *
     * @since 9.0.0
     */
    public static function getTotalDownloads(): int
    {
        if (isset(self::$cache['totalDownloads'])) {
            return self::$cache['totalDownloads'];
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('SUM(' . $db->quoteName('downloads') . ')')
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('downloads') . ' > 0');
        $db->setQuery($query);

        self::$cache['totalDownloads'] = (int) $db->loadResult();

        return self::$cache['totalDownloads'];
    }

    /**
     * Top Score Media File Plays
     *
     * @return string Number of scores
     *
     * @throws \Exception
     * @var   Registry $admin_params Admin Prams
     *
     * @since 9.0.0
     */
    public static function getTopScore(): string
    {
        $user    = Factory::getApplication()->getIdentity();
        $isAdmin = $user->authorise('core.admin');

        // Include user ID in cache key for non-admin users
        $userId   = (int) $user->id;
        $userKey  = $isAdmin ? 'admin' : 'uid:' . $userId;
        $cacheKey = 'topScore:' . $userKey;

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // L2: persistent cache across requests (TTL: 15 min)
        $pc     = self::getPersistentCache();
        $result = $pc->get(function () {
            $admin  = Cwmparams::getAdmin();
            $format = (int) $admin->params->get('format_popular', 0);
            $db     = Factory::getContainer()->get('DatabaseDriver');

            $query = $db->getQuery(true);
            $query->select($db->quoteName(['s.id', 's.studytitle', 's.studydate', 's.hits', 's.access']))
                ->select('SUM(' . $db->quoteName('mf.downloads') . ' + ' . $db->quoteName('mf.plays') . ') AS added')
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->join('INNER', $db->quoteName('#__bsms_mediafiles', 'mf') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('mf.study_id'))
                ->where($db->quoteName('mf.published') . ' = 1');

            // Apply hybrid security filter: location-based + Joomla view-level access
            CwmlocationHelper::applySecurityFilter($query, 's');

            $query->group($db->quoteName(['s.id', 's.studytitle', 's.studydate', 's.hits', 's.access']))
                ->order($db->quoteName('added') . ' DESC');

            $db->setQuery($query, 0, 10); // Get more to account for re-sorting if hits are included
            $rows = $db->loadObjectList();

            $final = [];

            foreach ($rows as $row) {
                $total   = ($format < 1) ? ((int) $row->added + (int) $row->hits) : (int) $row->added;
                $link    = ' <a href="' . Route::_('index.php?option=com_proclaim&task=message.edit&id=' . (int) $row->id) . '">' .
                    htmlspecialchars($row->studytitle, ENT_QUOTES, 'UTF-8') . '</a> ' . date('Y-m-d', strtotime($row->studydate)) . '<br>';
                $final[] = ['total' => $total, 'link' => $link];
            }

            // Re-sort by total descending
            usort($final, function ($a, $b) {
                return $b['total'] <=> $a['total'];
            });

            // Slice to top 5
            $final = \array_slice($final, 0, 5);

            $top_score_table = '';

            foreach ($final as $item) {
                $top_score_table .= (string) $item['total'] . ' ' . $item['link'];
            }

            return $top_score_table;
        }, [], md5($cacheKey));

        self::$cache[$cacheKey] = $result;

        return $result;
    }

    /**
     * Cached media stats to avoid duplicate queries
     *
     * @var array|null
     * @since 9.0.0
     */
    private static ?array $mediaStats = null;

    /**
     * Get combined media stats (players and popups) in a single query
     *
     * @return array Stats array with player and popup counts
     *
     * @since 9.0.0
     */
    private static function getMediaStats(): array
    {
        if (self::$mediaStats !== null) {
            return self::$mediaStats;
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        // Use SQL to extract and count player/popup values directly from JSON params
        // This avoids loading all records into PHP memory
        $query->select([
            'COUNT(*) as total',
            // Player counts using JSON_EXTRACT (MySQL 5.7+)
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.player")) IN ("0", "null") OR JSON_EXTRACT(' . $db->quoteName('params') . ', "$.player") IS NULL THEN 1 ELSE 0 END) as player_none',
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.player")) = "100" THEN 1 ELSE 0 END) as player_global',
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.player")) = "1" THEN 1 ELSE 0 END) as player_internal',
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.player")) = "3" THEN 1 ELSE 0 END) as player_av',
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.player")) = "7" THEN 1 ELSE 0 END) as player_legacy',
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.player")) = "8" THEN 1 ELSE 0 END) as player_embed',
            // Popup counts
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.popup")) IN ("0", "100", "null") OR JSON_EXTRACT(' . $db->quoteName('params') . ', "$.popup") IS NULL THEN 1 ELSE 0 END) as popup_none',
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.popup")) = "1" THEN 1 ELSE 0 END) as popup_popup',
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.popup")) = "2" THEN 1 ELSE 0 END) as popup_inline',
            'SUM(CASE WHEN JSON_UNQUOTE(JSON_EXTRACT(' . $db->quoteName('params') . ', "$.popup")) = "3" THEN 1 ELSE 0 END) as popup_squeezebox',
        ])
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);

        try {
            $result = $db->loadAssoc();
        } catch (\Exception $e) {
            // Fallback to PHP processing if JSON functions not available
            $result = self::getMediaStatsFallback();
        }

        self::$mediaStats = $result ?: [
            'total'            => 0,
            'player_none'      => 0,
            'player_global'    => 0,
            'player_internal'  => 0,
            'player_av'        => 0,
            'player_legacy'    => 0,
            'player_embed'     => 0,
            'popup_none'       => 0,
            'popup_popup'      => 0,
            'popup_inline'     => 0,
            'popup_squeezebox' => 0,
        ];

        return self::$mediaStats;
    }

    /**
     * Fallback method for databases without JSON support
     *
     * @return array Stats array
     *
     * @since 9.0.0
     */
    private static function getMediaStatsFallback(): array
    {
        $stats = [
            'total'            => 0,
            'player_none'      => 0,
            'player_global'    => 0,
            'player_internal'  => 0,
            'player_av'        => 0,
            'player_legacy'    => 0,
            'player_embed'     => 0,
            'popup_none'       => 0,
            'popup_popup'      => 0,
            'popup_inline'     => 0,
            'popup_squeezebox' => 0,
        ];

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);
        $rows = $db->loadColumn();

        if (!$rows) {
            return $stats;
        }

        $stats['total'] = \count($rows);
        $registry       = new Registry();

        foreach ($rows as $params) {
            $registry->loadString($params);

            // Count players
            $player = $registry->get('player', 0);
            switch ($player) {
                case 0:
                case null:
                    $stats['player_none']++;
                    break;
                case '100':
                    $stats['player_global']++;
                    break;
                case '1':
                    $stats['player_internal']++;
                    break;
                case '3':
                    $stats['player_av']++;
                    break;
                case '7':
                    $stats['player_legacy']++;
                    break;
                case '8':
                    $stats['player_embed']++;
                    break;
            }

            // Count popups
            $popup = $registry->get('popup', null);
            switch ($popup) {
                case null:
                case 100:
                case 0:
                    $stats['popup_none']++;
                    break;
                case 1:
                    $stats['popup_popup']++;
                    break;
                case 2:
                    $stats['popup_inline']++;
                    break;
                case 3:
                    $stats['popup_squeezebox']++;
                    break;
            }
        }

        return $stats;
    }

    /**
     * Returns a System of Player
     *
     * @return string HTML Format or Empty
     *
     * @since 9.0.0
     */
    public static function getPlayers(): string
    {
        $stats = self::getMediaStats();

        if ($stats['total'] === 0) {
            return '';
        }

        // Count deprecated players that may still exist (AV Plugin and Legacy only)
        $deprecatedCount = (int) $stats['player_av'] + (int) $stats['player_legacy'];

        $output = '<br /><strong>' . Text::_('JBS_CMN_TOTAL_MEDIAFILES') . ': ' . $stats['total'] . '</strong>' .
            '<br /><strong>' . Text::_('JBS_CMN_DIRECT_LINK') . ': </strong>' . (int) $stats['player_none'] .
            '<br /><strong>' . Text::_('JBS_CMN_INTERNAL_PLAYER') . ': </strong>' . (int) $stats['player_internal'] .
            '<br /><strong>' . Text::_('JBS_CMN_EMBED_CODE') . ': </strong>' . (int) $stats['player_embed'] .
            '<br /><strong>' . Text::_('JBS_CMN_USE_GLOBAL') . ': </strong>' . (int) $stats['player_global'];

        // Only show deprecated section if there are any deprecated players remaining
        if ($deprecatedCount > 0) {
            $output .= '<br /><br /><em>' . Text::_('JBS_ADM_DEPRECATED_PLAYERS') . ':</em>';

            if ((int) $stats['player_av'] > 0) {
                $output .= '<br /><strong>' . Text::_('JBS_CMN_AVPLUGIN') . ': </strong>' . (int) $stats['player_av'];
            }

            if ((int) $stats['player_legacy'] > 0) {
                $output .= '<br /><strong>' . Text::_('JBS_CMN_LEGACY_PLAYER') . ': </strong>' . (int) $stats['player_legacy'];
            }
        }

        return $output;
    }

    /**
     * Popups for media files
     *
     * @return string HTML Format
     *
     * @since 9.0.0
     */
    public static function getPopups(): string
    {
        $stats = self::getMediaStats();

        if ($stats['total'] === 0) {
            return '';
        }

        return '<br /><strong>' . Text::_('JBS_CMN_TOTAL_MEDIAFILES') . ': ' . $stats['total'] . '</strong>' .
            '<br /><strong>' . Text::_('JBS_CMN_INLINE') . ': </strong>' . (int) $stats['popup_inline'] .
            '<br /><strong>' . Text::_('JBS_CMN_POPUP') . ': </strong>' . (int) $stats['popup_popup'] .
            '<br /><strong>' . Text::_('JBS_CMN_SQUEEZEBOX') . ': </strong>' . (int) $stats['popup_squeezebox'] .
            '<br /><strong>' . Text::_('JBS_CMN_USE_GLOBAL') . ': </strong>' . (int) $stats['popup_none'];
    }

    /**
     * Get the raw podcast task state value.
     *
     * Returns the integer state of the proclaim.podcast scheduler task:
     *  1 = enabled, 0 = disabled, -2 = trashed, -3 = not created.
     *
     * @return int  The task state value
     *
     * @since 10.1.0
     */
    public static function getPodcastTaskRawState(): int
    {
        if (isset(self::$cache['podcastTaskRawState'])) {
            return self::$cache['podcastTaskRawState'];
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select($db->quoteName(['id', 'state']))
            ->from($db->quoteName('#__scheduler_tasks'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('proclaim.podcast'))
            ->order($db->quoteName('state') . ' DESC');
        $db->setQuery($query);

        $task = $db->loadObject();

        $state = $task ? (int) $task->state : -3;

        self::$cache['podcastTaskRawState'] = $state;
        self::$cache['podcastTaskId']       = $task ? (int) $task->id : 0;

        return $state;
    }

    /**
     * Get the Podcast Task State in HTML format
     *
     * @return string HTML Formatted Button with Status info
     *
     * @since 10.0.0
     */
    public static function getPodcastTaskState(): string
    {
        if (isset(self::$cache['podcastTaskState'])) {
            return self::$cache['podcastTaskState'];
        }

        $rawState = self::getPodcastTaskRawState();
        $taskId   = self::$cache['podcastTaskId'] ?? 0;

        switch ($rawState) {
            case 1:
                $stateKey    = 'JBS_CMN_TASK_ENABLED';
                $buttonClass = 'btn-success';
                break;
            case 0:
                $stateKey    = 'JBS_CMN_TASK_DISABLED';
                $buttonClass = 'btn-warning';
                break;
            case -2:
                $stateKey    = 'JBS_CMN_TASK_TRASHED';
                $buttonClass = 'btn-light';
                break;
            default:
                $stateKey    = 'JBS_CMN_TASK_NOT_CREATED';
                $buttonClass = 'btn-warning';
                break;
        }

        $return = '<div class="d-inline-block me-2 mb-2">';

        if ($rawState !== -3) {
            $return .= '<a href="' . Route::_('index.php?option=com_scheduler&task=task.edit&id=' . $taskId) . '" target="_blank">';
        } else {
            $return .= '<a href="' . Route::_('index.php?option=com_scheduler&view=tasks') . '" target="_blank">';
        }

        $return .= '<button type="button" class="btn ' . $buttonClass . '">'
            . '<i class="icon-clock" title="Clock showing time"></i>'
            . Text::_('JBS_CMN_PODCAST_TASK_STATUS') . ' <strong>' . Text::_($stateKey) . '</strong>'
            . '</button>';

        $return .= '</a>';
        $return .= '</div>';

        self::$cache['podcastTaskState'] = $return;

        return $return;
    }

    /**
     * Check if any published podcasts exist.
     *
     * @return bool  True if at least one published podcast exists
     *
     * @since 10.1.0
     */
    public static function hasPublishedPodcasts(): bool
    {
        if (isset(self::$cache['hasPublishedPodcasts'])) {
            return self::$cache['hasPublishedPodcasts'];
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_podcast'))
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query);

        $result = (int) $db->loadResult() > 0;

        self::$cache['hasPublishedPodcasts'] = $result;

        return $result;
    }

    /**
     * Top Score Site
     *
     * @return bool|string
     *
     * @throws \Exception
     * @since 9.0.0
     */
    public function getTopScoreSite(): bool|string
    {
        $input = Factory::getApplication()->getInput();
        $t     = $input->get('t', 1, 'int');

        $admin  = Cwmparams::getAdmin();
        $limit  = (int) $admin->params->get('popular_limit', 25);
        $format = (int) $admin->params->get('format_popular', 0);

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['s.id', 's.studytitle', 's.alias', 's.hits', 's.studydate', 's.access']))
            ->select('SUM(' . $db->quoteName('m.downloads') . ' + ' . $db->quoteName('m.plays') . ') as added')
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->leftJoin($db->quoteName('#__bsms_studies', 's') . ' ON ' . $db->quoteName('m.study_id') . ' = ' . $db->quoteName('s.id'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('s.published') . ' = 1')
            ->group($db->quoteName(['s.id', 's.studytitle', 's.alias', 's.hits', 's.studydate', 's.access']));

        $db->setQuery($query);
        $items = $db->loadObjectList() ?: [];

        // Check permissions for this view by running through the records and removing those the user doesn't have permission to see
        $user   = Factory::getApplication()->getIdentity();
        $groups = $user->getAuthorisedViewLevels();

        $final = [];

        foreach ($items as $item) {
            if (($item->access > 1) && !\in_array($item->access, $groups, true)) {
                continue;
            }

            $name  = $item->studytitle ?: $item->id;
            $total = ($format < 1) ? ((int) $item->added + (int) $item->hits) : (int) $item->added;
            $slug  = $item->alias ? ($item->id . ':' . $item->alias) : $item->id . ':'
                . str_replace(' ', '-', htmlspecialchars_decode($item->studytitle, ENT_QUOTES));

            $selectvalue   = Route::_('index.php?option=com_proclaim&view=cwmsermon&id=' . $slug . '&t=' . $t);
            $selectdisplay = $name . ' - ' . Text::_('JBS_CMN_SCORE') . ': ' . $total;

            $final[] = [
                'score'   => $total,
                'select'  => $selectvalue,
                'display' => $selectdisplay,
            ];
        }

        // Sort by score descending
        usort($final, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Slice to limit
        if ($limit > 0) {
            $final = \array_slice($final, 0, $limit);
        }

        $options = [
            HTMLHelper::_('select.option', '', Text::_('JBS_CMN_SELECT_POPULAR_STUDY')),
        ];

        foreach ($final as $topscore) {
            $options[] = HTMLHelper::_('select.option', $topscore['select'], $topscore['display']);
        }

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            'urlList',
            [
                'list.attr'   => 'class="form-select chzn-color-state valid form-control-success" onchange="window.location.href=this.value" size="1"',
                'list.select' => '',
                'option.key'  => 'value',
                'option.text' => 'text',
                'id'          => 'urlList',
            ]
        );
    }
}

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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;

/**
 * Helper for counting published/archived/total records across entity tables.
 *
 * Used by QuickIcon AJAX endpoints, Cwmstats, and CPanel dashboard.
 *
 * Location modes for multi-campus filtering:
 * - null      — no filtering (super-admin callers, CPanel stats)
 * - 'location' — table has location_id column; filter by user's accessible locations
 * - 'study'   — table is mediafiles; JOIN to studies for location filtering
 * - 'access'  — table has access column only; filter by user's view levels
 *
 * @since  10.1.0
 */
class CwmcountHelper
{
    /**
     * Static cache for count queries within the same request.
     *
     * @var array<string, int>
     * @since 10.1.0
     */
    private static array $cache = [];

    /**
     * Count rows by published state in the given table.
     *
     * Results are cached per request to avoid duplicate queries when
     * sendQuickIconResponse() or the cpanel stats call this multiple times.
     *
     * @param   string       $tableName     Full Joomla table name (e.g. '#__bsms_studies')
     * @param   int          $state         Published state value (1 = published, 2 = archived, etc.)
     * @param   string|null  $locationMode  Location filtering mode (null, 'location', 'study', 'access')
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getCountByState(string $tableName, int $state = 1, ?string $locationMode = null): int
    {
        $suffix = self::buildCacheKeySuffix($locationMode);
        $key    = $tableName . ':' . $state . $suffix;

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tableName, 't'))
            ->where($db->quoteName('t.published') . ' = ' . $state);

        self::applyLocationFilter($query, $db, $locationMode);

        $db->setQuery($query);

        self::$cache[$key] = (int) $db->loadResult();

        return self::$cache[$key];
    }

    /**
     * Count total rows in the given table (all states except trashed).
     *
     * Results are cached per request.
     *
     * @param   string       $tableName     Full Joomla table name
     * @param   string|null  $locationMode  Location filtering mode (null, 'location', 'study', 'access')
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getTotalCount(string $tableName, ?string $locationMode = null): int
    {
        $suffix = self::buildCacheKeySuffix($locationMode);
        $key    = $tableName . ':total' . $suffix;

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tableName, 't'))
            ->where($db->quoteName('t.published') . ' != -2');

        self::applyLocationFilter($query, $db, $locationMode);

        $db->setQuery($query);

        self::$cache[$key] = (int) $db->loadResult();

        return self::$cache[$key];
    }

    /**
     * Send a standard QuickIcon JSON response with published, archived, and total counts.
     *
     * @param   string       $tableName     Full Joomla table name
     * @param   string       $langKey       Language key base (e.g. 'COM_PROCLAIM_N_QUICKICON_MESSAGES')
     * @param   string|null  $locationMode  Location filtering mode (null, 'location', 'study', 'access')
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function sendQuickIconResponse(string $tableName, string $langKey, ?string $locationMode = null): void
    {
        $published = self::getCountByState($tableName, 1, $locationMode);
        $archived  = self::getCountByState($tableName, 2, $locationMode);
        $total     = self::getTotalCount($tableName, $locationMode);

        $result = [
            'amount'   => $published,
            'archived' => $archived,
            'total'    => $total,
            'sronly'   => Text::plural($langKey . '_SRONLY', $published),
            'name'     => Text::plural($langKey, $published),
        ];

        echo new JsonResponse($result);
    }

    /**
     * Apply location/access filtering to a count query based on the mode.
     *
     * Super admins always bypass filtering. When the location system is disabled,
     * 'location' and 'study' modes fall back to no filtering.
     *
     * @param   QueryInterface     $query         The query to modify.
     * @param   DatabaseInterface  $db            The database driver.
     * @param   string|null        $locationMode  Filtering mode.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    private static function applyLocationFilter(QueryInterface $query, DatabaseInterface $db, ?string $locationMode): void
    {
        if ($locationMode === null) {
            return;
        }

        try {
            $user = Factory::getApplication()->getIdentity();
        } catch (\Exception $e) {
            return;
        }

        if (!$user || $user->authorise('core.admin')) {
            return;
        }

        switch ($locationMode) {
            case 'location':
                // Table has location_id — filter by user's accessible locations
                if (CwmlocationHelper::isEnabled()) {
                    $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                    if (!empty($accessible)) {
                        $query->where(
                            '(' . $db->quoteName('t.location_id') . ' IS NULL'
                            . ' OR ' . $db->quoteName('t.location_id') . ' IN ('
                            . implode(',', array_map('intval', $accessible)) . '))'
                        );
                    }
                }

                // Also filter by access view levels
                $query->whereIn($db->quoteName('t.access'), $user->getAuthorisedViewLevels());
                break;

            case 'study':
                // Mediafiles — JOIN to studies for location + access filtering
                $query->leftJoin(
                    $db->quoteName('#__bsms_studies', 's')
                    . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('t.study_id')
                );

                if (CwmlocationHelper::isEnabled()) {
                    $accessible = CwmlocationHelper::getUserLocations((int) $user->id);

                    if (!empty($accessible)) {
                        $query->where(
                            '(' . $db->quoteName('s.location_id') . ' IS NULL'
                            . ' OR ' . $db->quoteName('s.location_id') . ' IN ('
                            . implode(',', array_map('intval', $accessible)) . '))'
                        );
                    }
                }

                // Mediafile's own access level
                $query->whereIn($db->quoteName('t.access'), $user->getAuthorisedViewLevels());
                break;

            case 'access':
                // Table has access column only (teachers, topics, etc.)
                $query->whereIn($db->quoteName('t.access'), $user->getAuthorisedViewLevels());
                break;
        }
    }

    /**
     * Build a cache key suffix that incorporates the user and location mode,
     * so different users get separate cached counts.
     *
     * @param   string|null  $locationMode  The filtering mode.
     *
     * @return  string
     *
     * @since   10.1.0
     */
    private static function buildCacheKeySuffix(?string $locationMode): string
    {
        if ($locationMode === null) {
            return '';
        }

        try {
            $user = Factory::getApplication()->getIdentity();
            $uid  = (int) $user?->id;
        } catch (\Exception $e) {
            $uid = 0;
        }

        return ':' . $locationMode . ':u' . $uid;
    }
}

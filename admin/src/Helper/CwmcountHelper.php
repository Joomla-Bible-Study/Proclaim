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

/**
 * Helper for counting published/archived/total records across entity tables.
 *
 * Used by QuickIcon AJAX endpoints and Cwmstats.
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
     * @param   string  $tableName  Full Joomla table name (e.g. '#__bsms_studies')
     * @param   int     $state      Published state value (1 = published, 2 = archived, etc.)
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getCountByState(string $tableName, int $state = 1): int
    {
        $key = $tableName . ':' . $state;

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tableName))
            ->where($db->quoteName('published') . ' = ' . $state);
        $db->setQuery($query);

        self::$cache[$key] = (int) $db->loadResult();

        return self::$cache[$key];
    }

    /**
     * Count total rows in the given table (all states except trashed).
     *
     * Results are cached per request.
     *
     * @param   string  $tableName  Full Joomla table name
     *
     * @return  int
     *
     * @since   10.1.0
     */
    public static function getTotalCount(string $tableName): int
    {
        $key = $tableName . ':total';

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($tableName))
            ->where($db->quoteName('published') . ' != -2');
        $db->setQuery($query);

        self::$cache[$key] = (int) $db->loadResult();

        return self::$cache[$key];
    }

    /**
     * Send a standard QuickIcon JSON response with published, archived, and total counts.
     *
     * @param   string  $tableName  Full Joomla table name
     * @param   string  $langKey    Language key base (e.g. 'COM_PROCLAIM_N_QUICKICON_MESSAGES')
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function sendQuickIconResponse(string $tableName, string $langKey): void
    {
        $published = self::getCountByState($tableName, 1);
        $archived  = self::getCountByState($tableName, 2);
        $total     = self::getTotalCount($tableName);

        $result = [
            'amount'   => $published,
            'archived' => $archived,
            'total'    => $total,
            'sronly'   => Text::plural($langKey . '_SRONLY', $published),
            'name'     => Text::plural($langKey, $published),
        ];

        echo new JsonResponse($result);
    }
}

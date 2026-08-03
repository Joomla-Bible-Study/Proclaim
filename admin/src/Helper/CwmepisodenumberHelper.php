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

use Joomla\Database\DatabaseInterface;

/**
 * Episode numbering (#__bsms_studies.studynumber) within a series.
 *
 * @package  Proclaim.Admin
 * @since    __DEPLOY_VERSION__
 */
class CwmepisodenumberHelper
{
    /**
     * Compute the next episode number within a series.
     *
     * Only considers existing studynumber values that are plain non-negative
     * integers — legacy/non-numeric values are ignored rather than breaking
     * the computation.
     *
     * @param   DatabaseInterface  $db         Database driver
     * @param   int                $seriesId   The series to number within
     * @param   int|null           $excludeId  A message id to exclude (the record being saved)
     *
     * @return  int
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function nextNumber(DatabaseInterface $db, int $seriesId, ?int $excludeId = null): int
    {
        $query = $db->createQuery()
            ->select('MAX(CAST(' . $db->quoteName('studynumber') . ' AS UNSIGNED))')
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('series_id') . ' = ' . $seriesId)
            ->where($db->quoteName('studynumber') . ' REGEXP ' . $db->quote('^[0-9]+$'));

        if ($excludeId !== null) {
            $query->where($db->quoteName('id') . ' != ' . $excludeId);
        }

        $db->setQuery($query);

        return (int) $db->loadResult() + 1;
    }

    /**
     * Find another message in the same series already using the given
     * episode number.
     *
     * @param   DatabaseInterface  $db          Database driver
     * @param   int                $seriesId    The series to check within
     * @param   string             $studynumber The episode number to check
     * @param   int|null           $excludeId   A message id to exclude (the record being saved)
     *
     * @return  \stdClass|null  The conflicting row ({id, studytitle}), or null if none
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function findDuplicate(DatabaseInterface $db, int $seriesId, string $studynumber, ?int $excludeId = null): ?\stdClass
    {
        $query = $db->createQuery()
            ->select($db->quoteName(['id', 'studytitle']))
            ->from($db->quoteName('#__bsms_studies'))
            ->where($db->quoteName('series_id') . ' = ' . $seriesId)
            ->where($db->quoteName('studynumber') . ' = ' . $db->quote($studynumber));

        if ($excludeId !== null) {
            $query->where($db->quoteName('id') . ' != ' . $excludeId);
        }

        $db->setQuery($query);

        return $db->loadObject() ?: null;
    }

    /**
     * Find every series with more than one message sharing the same episode
     * number, for the admin audit report.
     *
     * @param   DatabaseInterface  $db  Database driver
     *
     * @return  array  Rows: {series_id, series_text, studynumber, message_ids, titles}
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function findAllDuplicates(DatabaseInterface $db): array
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('st.series_id'),
                $db->quoteName('s.series_text'),
                $db->quoteName('st.studynumber'),
                'GROUP_CONCAT(' . $db->quoteName('st.id') . ' ORDER BY ' . $db->quoteName('st.id') . ') AS ' . $db->quoteName('message_ids'),
                'GROUP_CONCAT(' . $db->quoteName('st.studytitle') . ' ORDER BY ' . $db->quoteName('st.id') . " SEPARATOR ' | ') AS " . $db->quoteName('titles'),
            ])
            ->from($db->quoteName('#__bsms_studies', 'st'))
            ->join('INNER', $db->quoteName('#__bsms_series', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('st.series_id'))
            ->where($db->quoteName('st.series_id') . ' > 0')
            ->where($db->quoteName('st.studynumber') . " != ''")
            ->where($db->quoteName('st.studynumber') . ' IS NOT NULL')
            ->group([$db->quoteName('st.series_id'), $db->quoteName('st.studynumber')])
            ->having('COUNT(*) > 1')
            ->order($db->quoteName('s.series_text') . ', CAST(' . $db->quoteName('st.studynumber') . ' AS UNSIGNED)');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}

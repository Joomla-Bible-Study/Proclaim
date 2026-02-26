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
use Joomla\Database\DatabaseInterface;
use Joomla\Database\QueryInterface;

/**
 * Cross-filter helper for frontend filter dropdowns.
 *
 * When a user selects a filter (e.g. Teacher), the other dropdowns
 * (Books, Series, Years, etc.) should narrow to only show options that
 * have messages matching the active filters.  This helper applies the
 * active filter constraints to a query that already joins to
 * `#__bsms_studies` aliased as `s`.
 *
 * @since  10.1.0
 */
abstract class CwmfilterHelper
{
    /**
     * Read the current value of a frontend filter from the session/request.
     *
     * @param   string  $name  Filter name (e.g. 'teacher', 'book')
     *
     * @return  mixed  The filter value, or null/0/empty if not set
     *
     * @since   10.1.0
     */
    private static function getFilterValue(string $name): mixed
    {
        $app     = Factory::getApplication();
        $context = 'com_proclaim.sermons.list';

        return $app->getUserStateFromRequest($context . '.filter.' . $name, 'filter_' . $name, '');
    }

    /**
     * Apply all active cross-filters to a query.
     *
     * The query MUST already join `#__bsms_studies` aliased as `s`.
     * Pass `$exclude` to skip the filter for the dropdown's own entity
     * (e.g. the Teacher dropdown passes 'teacher' so it doesn't self-filter).
     *
     * @param   QueryInterface  $query    The query to constrain
     * @param   string          $exclude  Filter name to skip (the caller's own filter)
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function applyCrossFilters(QueryInterface $query, string $exclude = ''): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Teacher filter
        if ($exclude !== 'teacher') {
            $teacher = (int) self::getFilterValue('teacher');

            if ($teacher > 0) {
                // Use junction table for multi-teacher support
                $query->join(
                    'INNER',
                    $db->quoteName('#__bsms_study_teachers', 'xf_stj') . ' ON '
                    . $db->quoteName('xf_stj.study_id') . ' = ' . $db->quoteName('s.id')
                )
                    ->where($db->quoteName('xf_stj.teacher_id') . ' = ' . $teacher);
            }
        }

        // Book filter
        if ($exclude !== 'book') {
            $book = (int) self::getFilterValue('book');

            if ($book > 0) {
                $query->where(
                    '(' . $db->quoteName('s.booknumber') . ' = ' . $book
                    . ' OR ' . $db->quoteName('s.booknumber2') . ' = ' . $book . ')'
                );
            }
        }

        // Series filter
        if ($exclude !== 'series') {
            $series = (int) self::getFilterValue('series');

            if ($series > 0) {
                $query->where($db->quoteName('s.series_id') . ' = ' . $series);
            }
        }

        // Message type filter
        if ($exclude !== 'messagetype') {
            $messagetype = (int) self::getFilterValue('messageType');

            if ($messagetype > 0) {
                $query->where($db->quoteName('s.messagetype') . ' = ' . $messagetype);
            }
        }

        // Year filter
        if ($exclude !== 'year') {
            $year = (int) self::getFilterValue('year');

            if ($year > 0) {
                $query->where('YEAR(' . $db->quoteName('s.studydate') . ') = ' . $year);
            }
        }

        // Topic filter
        if ($exclude !== 'topic') {
            $topic = (int) self::getFilterValue('topic');

            if ($topic > 0) {
                $query->join(
                    'INNER',
                    $db->quoteName('#__bsms_studytopics', 'xf_st') . ' ON '
                    . $db->quoteName('xf_st.study_id') . ' = ' . $db->quoteName('s.id')
                )
                    ->where($db->quoteName('xf_st.topic_id') . ' = ' . $topic);
            }
        }

        // Location filter
        if ($exclude !== 'location') {
            $location = (int) self::getFilterValue('location');

            if ($location > 0) {
                $query->where($db->quoteName('s.location_id') . ' = ' . $location);
            }
        }
    }
}

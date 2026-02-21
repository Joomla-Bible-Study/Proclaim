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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/**
 * Helper for building formatted video descriptions for platform sync/copy.
 *
 * @since  10.1.0
 */
class CwmdescriptionHelper
{
    /**
     * Build a formatted video description snippet for a study.
     *
     * Includes title, teachers, date, scripture references, series, and a back-link.
     *
     * @param   int  $studyId  The study ID
     *
     * @return  string  Formatted description text
     *
     * @since   10.1.0
     */
    public static function buildVideoDescription(int $studyId): string
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.studytitle'),
                $db->quoteName('s.studydate'),
                $db->quoteName('s.studyintro'),
                $db->quoteName('s.alias'),
                $db->quoteName('sr.series_text', 'series_title'),
            ])
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->leftJoin(
                $db->quoteName('#__bsms_series', 'sr') .
                ' ON ' . $db->quoteName('sr.id') . ' = ' . $db->quoteName('s.series_id')
            )
            ->where($db->quoteName('s.id') . ' = ' . (int) $studyId);
        $db->setQuery($query);
        $study = $db->loadObject();

        if (!$study) {
            return '';
        }

        $lines = [];

        // Title line with series
        $titleLine = $study->studytitle ?? '';

        if (!empty($study->series_title)) {
            $titleLine .= ' — ' . $study->series_title;
        }

        $lines[] = $titleLine;

        // Teachers
        $teachers = CwmstudyteacherHelper::getTeachersForStudy($studyId);

        if (!empty($teachers)) {
            $names   = array_map(fn ($t) => $t->teachername, $teachers);
            $lines[] = implode(', ', $names);
        }

        // Date
        if (!empty($study->studydate) && $study->studydate !== '0000-00-00 00:00:00') {
            $lines[] = date('F j, Y', strtotime($study->studydate));
        }

        // Scripture references
        $scriptures = self::getScriptureReferences($studyId);

        if (!empty($scriptures)) {
            $lines[] = implode('; ', $scriptures);
        }

        // Blank line before link
        $lines[] = '';

        // Back-link to site
        $slug = $study->alias
            ? ($study->id . ':' . $study->alias)
            : (string) $study->id;
        $url  = rtrim(Uri::root(), '/') . '/' . ltrim(
            Route::_('index.php?option=com_proclaim&view=cwmsermon&id=' . $slug),
            '/'
        );
        $lines[] = 'Watch on our site: ' . $url;

        // Site name
        $siteName = Factory::getApplication()->get('sitename', '');

        if (!empty($siteName)) {
            $lines[] = $siteName;
        }

        return implode("\n", $lines);
    }

    /**
     * Get formatted scripture references for a study.
     *
     * @param   int  $studyId  The study ID
     *
     * @return  string[]  Array of formatted reference strings
     *
     * @since   10.1.0
     */
    private static function getScriptureReferences(int $studyId): array
    {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('b.bookname'),
                $db->quoteName('ss.chapter_begin'),
                $db->quoteName('ss.verse_begin'),
                $db->quoteName('ss.chapter_end'),
                $db->quoteName('ss.verse_end'),
            ])
            ->from($db->quoteName('#__bsms_study_scriptures', 'ss'))
            ->leftJoin(
                $db->quoteName('#__bsms_books', 'b') .
                ' ON ' . $db->quoteName('b.booknumber') . ' = ' . $db->quoteName('ss.book_number')
            )
            ->where($db->quoteName('ss.study_id') . ' = ' . (int) $studyId)
            ->order($db->quoteName('ss.ordering') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?? [];

        $refs = [];

        foreach ($rows as $row) {
            $ref = ($row->bookname ?? '') . ' ' . ($row->chapter_begin ?? '');

            if (!empty($row->verse_begin)) {
                $ref .= ':' . $row->verse_begin;
            }

            if (!empty($row->chapter_end) && $row->chapter_end !== $row->chapter_begin) {
                $ref .= '-' . $row->chapter_end;

                if (!empty($row->verse_end)) {
                    $ref .= ':' . $row->verse_end;
                }
            } elseif (!empty($row->verse_end) && $row->verse_end !== $row->verse_begin) {
                $ref .= '-' . $row->verse_end;
            }

            $refs[] = trim($ref);
        }

        return array_filter($refs);
    }
}

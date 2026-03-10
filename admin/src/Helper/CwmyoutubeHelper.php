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
use Joomla\Database\DatabaseInterface;

/**
 * YouTube Helper for matching video titles to Proclaim messages
 *
 * @package  Proclaim.Admin
 * @since    10.1.0
 */
class CwmyoutubeHelper
{
    /**
     * Parse a YouTube video title to extract message title and teacher name
     *
     * Supports formats:
     * - "Message Title - Teacher Name"
     * - "Teacher Name - Message Title"
     * - "Message Title | Teacher Name"
     * - "Message Title: Teacher Name"
     *
     * @param   string  $title  The YouTube video title
     *
     * @return  array  Array with 'part1', 'part2', and 'separator' keys
     *
     * @since   10.1.0
     */
    public static function parseVideoTitle(string $title): array
    {
        // Common separators in order of preference
        $separators = [' - ', ' | ', ': '];

        foreach ($separators as $sep) {
            if (str_contains($title, $sep)) {
                $parts = explode($sep, $title, 2);

                if (\count($parts) === 2) {
                    return [
                        'part1'     => trim($parts[0]),
                        'part2'     => trim($parts[1]),
                        'separator' => $sep,
                    ];
                }
            }
        }

        // No separator found - use full title as message title
        return [
            'part1'     => trim($title),
            'part2'     => null,
            'separator' => null,
        ];
    }

    /**
     * Find a teacher by name using fuzzy matching
     *
     * @param   string  $teacherName  The teacher name to search for
     *
     * @return  int|null  The teacher ID or null if not found
     *
     * @since   10.1.0
     */
    public static function findTeacherByName(string $teacherName): ?int
    {
        if (empty($teacherName)) {
            return null;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $searchName = $db->quote('%' . $db->escape($teacherName, true) . '%');
        $exactName  = $db->quote($teacherName);

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__bsms_teachers'))
            ->where('LOWER(' . $db->quoteName('teachername') . ') LIKE LOWER(' . $searchName . ')')
            ->where($db->quoteName('published') . ' = 1')
            ->order('CASE WHEN LOWER(' . $db->quoteName('teachername') . ') = LOWER(' . $exactName . ') THEN 0 ELSE 1 END')
            ->order('LENGTH(' . $db->quoteName('teachername') . ')')
            ->setLimit(1);

        $db->setQuery($query);

        $result = $db->loadResult();

        return $result ? (int) $result : null;
    }

    /**
     * Find a message by title with optional teacher filter
     *
     * @param   string    $messageTitle  The message title to search for
     * @param   int|null  $teacherId     Optional teacher ID to filter by
     *
     * @return  object|null  The message object or null if not found
     *
     * @since   10.1.0
     */
    public static function findMessageByTitle(string $messageTitle, ?int $teacherId = null): ?object
    {
        if (empty($messageTitle)) {
            return null;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $searchTitle = $db->quote('%' . $db->escape($messageTitle, true) . '%');
        $exactTitle  = $db->quote($messageTitle);

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.studytitle'),
                $db->quoteName('s.studydate'),
                $db->quoteName('s.alias'),
                $db->quoteName('t.teachername'),
                $db->quoteName('t.id', 'teacher_id'),
            ])
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->join('LEFT', $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
                . $db->quoteName('stj.study_id') . ' = ' . $db->quoteName('s.id')
                . ' AND ' . $db->quoteName('stj.ordering') . ' = 0')
            ->join('LEFT', $db->quoteName('#__bsms_teachers', 't') . ' ON '
                . $db->quoteName('t.id') . ' = COALESCE(' . $db->quoteName('stj.teacher_id') . ', ' . $db->quoteName('s.teacher_id') . ')')
            ->where('LOWER(' . $db->quoteName('s.studytitle') . ') LIKE LOWER(' . $searchTitle . ')')
            ->where($db->quoteName('s.published') . ' = 1');

        // Filter by teacher if provided
        if ($teacherId !== null) {
            $tSub = $db->getQuery(true)
                ->select('1')
                ->from($db->quoteName('#__bsms_study_teachers', 'stf'))
                ->where($db->quoteName('stf.study_id') . ' = ' . $db->quoteName('s.id'))
                ->where($db->quoteName('stf.teacher_id') . ' = ' . (int) $teacherId);
            $query->where('EXISTS (' . $tSub . ')');
        }

        // Order by exact match first, then most recent
        $query->order('CASE WHEN LOWER(' . $db->quoteName('s.studytitle') . ') = LOWER(' . $exactTitle . ') THEN 0 ELSE 1 END')
            ->order($db->quoteName('s.studydate') . ' DESC')
            ->setLimit(1);

        $db->setQuery($query);

        return $db->loadObject();
    }

    /**
     * Find a published message by matching a YouTube video ID in media file params.
     *
     * Searches #__bsms_mediafiles for a record whose params→filename contains the
     * given video ID, then returns the parent message (study) if published.
     *
     * @param   string  $videoId  YouTube video ID (11-char alphanumeric)
     *
     * @return  object|null  The message object or null if not found
     *
     * @since   10.1.0
     */
    public static function findMessageByVideoId(string $videoId): ?object
    {
        if (empty($videoId)) {
            return null;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // YouTube URLs are stored in params JSON as filename like
        // "//www.youtube.com/embed/{videoId}?enablejsapi=1"
        $searchId = $db->quote('%' . $db->escape($videoId, true) . '%');

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.studytitle'),
                $db->quoteName('s.studydate'),
                $db->quoteName('s.alias'),
                $db->quoteName('t.teachername'),
                $db->quoteName('t.id', 'teacher_id'),
            ])
            ->from($db->quoteName('#__bsms_mediafiles', 'm'))
            ->join('INNER', $db->quoteName('#__bsms_studies', 's') . ' ON '
                . $db->quoteName('s.id') . ' = ' . $db->quoteName('m.study_id'))
            ->join('LEFT', $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
                . $db->quoteName('stj.study_id') . ' = ' . $db->quoteName('s.id')
                . ' AND ' . $db->quoteName('stj.ordering') . ' = 0')
            ->join('LEFT', $db->quoteName('#__bsms_teachers', 't') . ' ON '
                . $db->quoteName('t.id') . ' = COALESCE(' . $db->quoteName('stj.teacher_id') . ', ' . $db->quoteName('s.teacher_id') . ')')
            ->where($db->quoteName('m.params') . ' LIKE ' . $searchId)
            ->where($db->quoteName('s.published') . ' = 1')
            ->whereIn($db->quoteName('m.published'), [1, 2])
            ->order($db->quoteName('s.studydate') . ' DESC')
            ->setLimit(1);

        $db->setQuery($query);

        return $db->loadObject();
    }

    /**
     * Match a YouTube video title to a Proclaim message
     *
     * Tries multiple strategies:
     * 1. Match by YouTube video ID in media file URLs (exact)
     * 2. Parse title and try part1 as title, part2 as teacher
     * 3. Try reversed: part1 as teacher, part2 as title
     * 4. Fall back to full title search without teacher filter
     *
     * @param   string  $videoTitle  The YouTube video title
     * @param   string  $videoId     Optional YouTube video ID for exact URL matching
     *
     * @return  object|null  The matched message object or null
     *
     * @since   10.1.0
     */
    public static function matchVideoToMessage(string $videoTitle, string $videoId = ''): ?object
    {
        // Strategy 0: Exact match by video ID in media file URLs
        if (!empty($videoId)) {
            $message = self::findMessageByVideoId($videoId);

            if ($message) {
                return $message;
            }
        }

        if (empty($videoTitle)) {
            return null;
        }

        $parsed = self::parseVideoTitle($videoTitle);

        // Strategy 1: part1 = message title, part2 = teacher name
        if ($parsed['part2'] !== null) {
            $teacherId = self::findTeacherByName($parsed['part2']);
            $message   = self::findMessageByTitle($parsed['part1'], $teacherId);

            if ($message) {
                return $message;
            }

            // Strategy 2: part1 = teacher name, part2 = message title (reversed order)
            $teacherId = self::findTeacherByName($parsed['part1']);
            $message   = self::findMessageByTitle($parsed['part2'], $teacherId);

            if ($message) {
                return $message;
            }
        }

        // Strategy 3: Try part1 as title without teacher filter
        if ($message = self::findMessageByTitle($parsed['part1'])) {
            return $message;
        }

        // Strategy 4: Try full title as-is (in case separators are part of the title)
        if ($parsed['separator'] !== null) {
            return self::findMessageByTitle($videoTitle, null);
        }

        return null;
    }

    /**
     * Get all potential matches for a video title (useful for admin UI)
     *
     * @param   string  $videoTitle  The YouTube video title
     * @param   int     $limit       Maximum number of matches to return
     *
     * @return  array  Array of potential message matches with confidence scores
     *
     * @since   10.1.0
     */
    public static function findPotentialMatches(string $videoTitle, int $limit = 5): array
    {
        if (empty($videoTitle)) {
            return [];
        }

        $parsed   = self::parseVideoTitle($videoTitle);
        $matches  = [];
        $seenIds  = [];

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Search using different parts of the title
        $searchTerms = array_filter([$parsed['part1'], $parsed['part2'], $videoTitle]);

        foreach ($searchTerms as $searchTerm) {
            $searchTitle = $db->quote('%' . $db->escape($searchTerm, true) . '%');

            $query = $db->getQuery(true)
                ->select([
                    $db->quoteName('s.id'),
                    $db->quoteName('s.studytitle'),
                    $db->quoteName('s.studydate'),
                    $db->quoteName('t.teachername'),
                ])
                ->from($db->quoteName('#__bsms_studies', 's'))
                ->join('LEFT', $db->quoteName('#__bsms_study_teachers', 'stj') . ' ON '
                    . $db->quoteName('stj.study_id') . ' = ' . $db->quoteName('s.id')
                    . ' AND ' . $db->quoteName('stj.ordering') . ' = 0')
                ->join('LEFT', $db->quoteName('#__bsms_teachers', 't') . ' ON '
                    . $db->quoteName('t.id') . ' = COALESCE(' . $db->quoteName('stj.teacher_id') . ', ' . $db->quoteName('s.teacher_id') . ')')
                ->where('LOWER(' . $db->quoteName('s.studytitle') . ') LIKE LOWER(' . $searchTitle . ')')
                ->where($db->quoteName('s.published') . ' = 1')
                ->order($db->quoteName('s.studydate') . ' DESC')
                ->setLimit($limit);

            $db->setQuery($query);
            $results = $db->loadObjectList();

            foreach ($results as $result) {
                if (!isset($seenIds[$result->id])) {
                    $seenIds[$result->id] = true;
                    $matches[]            = $result;

                    if (\count($matches) >= $limit) {
                        break 2;
                    }
                }
            }
        }

        return $matches;
    }
}

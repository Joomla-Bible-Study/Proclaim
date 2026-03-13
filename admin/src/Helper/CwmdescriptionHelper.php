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

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

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
     * When a media file is specified and its server has a description format
     * template (e.g. `yt_description_format`), that template is used with
     * placeholder replacement. Otherwise falls back to a sensible default.
     *
     * Supported placeholders:
     *   {title}       - Study title
     *   {series}      - Series name
     *   {teachers}    - Comma-separated teacher names
     *   {date}        - Formatted date (F j, Y)
     *   {scriptures}  - Scripture references (semicolon-separated)
     *   {studyintro}  - Study introduction (HTML stripped)
     *   {topics}      - Comma-separated topic names
     *   {chapters}    - Formatted chapter timestamps
     *   {url}         - Link back to the sermon on the site
     *
     * @param   int  $studyId  The study ID
     * @param   int  $mediaId  Optional media file ID — when provided, chapters
     *                         stored in the media params are appended as timestamps
     *
     * @return  string  Formatted description text
     *
     * @since   10.1.0
     */
    public static function buildVideoDescription(int $studyId, int $mediaId = 0): string
    {
        $data = self::gatherDescriptionData($studyId, $mediaId);

        if (empty($data)) {
            return '';
        }

        // Try to load the server's description format template
        $format = self::getServerDescriptionFormat($mediaId);

        if (!empty($format)) {
            return self::applyTemplate($format, $data);
        }

        // Default layout when no template is configured
        return self::buildDefaultDescription($data);
    }

    /**
     * Gather all replacement data for description placeholders.
     *
     * @param   int  $studyId  The study ID
     * @param   int  $mediaId  Optional media file ID
     *
     * @return  array  Associative array of placeholder values, or empty if study not found
     *
     * @since   10.2.0
     */
    private static function gatherDescriptionData(int $studyId, int $mediaId): array
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
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
            return [];
        }

        // Teachers
        $teachers    = CwmstudyteacherHelper::getTeachersForStudy($studyId);
        $teacherText = '';

        if (!empty($teachers)) {
            $teacherText = implode(', ', array_map(fn ($t) => $t->teachername, $teachers));
        }

        // Date
        $dateText = '';

        if (!empty($study->studydate) && $study->studydate !== '0000-00-00 00:00:00') {
            $dateText = date('F j, Y', strtotime($study->studydate));
        }

        // Scriptures
        $scriptures    = self::getScriptureReferences($studyId);
        $scriptureText = implode('; ', $scriptures);

        // Topics
        $topicText = self::getTopicsForStudy($studyId);

        // Chapters
        $chapters    = self::getChaptersForDescription($studyId, $mediaId);
        $chapterText = '';

        if (!empty($chapters)) {
            $chapterText = CWMAddon::formatChaptersForDescription($chapters);
        }

        // Frontend SEF URL — find the site menu item for Proclaim and build
        // the path from its route + the study alias.
        $url = self::buildFrontendUrl($study->id, $study->alias ?? '');

        // Study intro — strip HTML
        $intro = trim(strip_tags($study->studyintro ?? ''));

        return [
            'title'      => $study->studytitle ?? '',
            'series'     => $study->series_title ?? '',
            'teachers'   => $teacherText,
            'date'       => $dateText,
            'scriptures' => $scriptureText,
            'studyintro' => $intro,
            'topics'     => $topicText,
            'chapters'   => $chapterText,
            'url'        => $url,
        ];
    }

    /**
     * Apply a description format template with placeholder replacement.
     *
     * Empty placeholders are replaced with empty string and excess blank
     * lines are collapsed to at most two consecutive newlines.
     *
     * @param   string  $format  The template string with {placeholder} tokens
     * @param   array   $data    The replacement data
     *
     * @return  string  Formatted description
     *
     * @since   10.2.0
     */
    private static function applyTemplate(string $format, array $data): string
    {
        $replacements = [];

        foreach ($data as $key => $value) {
            $replacements['{' . $key . '}'] = $value;
        }

        $result = strtr($format, $replacements);

        // Clean up dangling separators left by empty placeholders (e.g. "Title — ")
        $result = preg_replace('/\s*[—–\-]+\s*$/m', '', $result);

        // Clean up lines that are only labels with empty values (e.g. "Topics: ")
        $result = preg_replace('/^\S+:\s*$/m', '', $result);

        // Collapse runs of 3+ newlines down to 2 (one blank line)
        $result = preg_replace("/\n{3,}/", "\n\n", $result);

        return trim($result);
    }

    /**
     * Build the default description layout (used when no server template exists).
     *
     * @param   array  $data  The replacement data
     *
     * @return  string  Formatted description
     *
     * @since   10.2.0
     */
    private static function buildDefaultDescription(array $data): string
    {
        $lines = [];

        // Title line with series
        $titleLine = $data['title'];

        if (!empty($data['series'])) {
            $titleLine .= ' — ' . $data['series'];
        }

        $lines[] = $titleLine;

        if (!empty($data['teachers'])) {
            $lines[] = $data['teachers'];
        }

        if (!empty($data['date'])) {
            $lines[] = $data['date'];
        }

        if (!empty($data['scriptures'])) {
            $lines[] = $data['scriptures'];
        }

        if (!empty($data['chapters'])) {
            $lines[] = '';
            $lines[] = $data['chapters'];
        }

        $lines[] = '';
        $lines[] = 'Watch on our site: ' . $data['url'];

        return implode("\n", $lines);
    }

    /**
     * Build a frontend SEF URL for a study from admin context.
     *
     * Looks up the site menu item linked to a Proclaim sermon view and
     * constructs the URL from its route path + the study alias. Falls back
     * to a non-SEF query string if no menu item is found.
     *
     * @param   int     $studyId  The study ID
     * @param   string  $alias    The study alias
     *
     * @return  string  Absolute frontend URL
     *
     * @since   10.2.0
     */
    private static function buildFrontendUrl(int $studyId, string $alias): string
    {
        $root = rtrim(Uri::root(), '/');
        $db   = Factory::getContainer()->get(DatabaseInterface::class);

        // Find a site menu item pointing to a Proclaim sermon list or detail view
        $query = $db->getQuery(true)
            ->select([$db->quoteName('path'), $db->quoteName('link')])
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('link') . ' LIKE ' . $db->quote('index.php?option=com_proclaim&view=cwmsermon%'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('client_id') . ' = 0')
            ->order($db->quoteName('level') . ' ASC')
            ->setLimit(1);
        $db->setQuery($query);
        $menuItem = $db->loadObject();

        if ($menuItem && !empty($menuItem->path)) {
            // SEF URL: menu path + study alias
            $slug = !empty($alias) ? $alias : (string) $studyId;

            return $root . '/' . $menuItem->path . '/' . $slug;
        }

        // Fallback: non-SEF URL (Joomla will redirect to SEF automatically)
        $slug = !empty($alias)
            ? ($studyId . ':' . $alias)
            : (string) $studyId;

        return $root . '/index.php?option=com_proclaim&view=cwmsermon&id=' . $slug;
    }

    /**
     * Get the description format template from a media file's server addon.
     *
     * Looks for a `description_format` or `yt_description_format` param
     * in the server's configuration.
     *
     * @param   int  $mediaId  The media file ID
     *
     * @return  string  Format template string or empty if none configured
     *
     * @since   10.2.0
     */
    private static function getServerDescriptionFormat(int $mediaId): string
    {
        if ($mediaId <= 0) {
            return '';
        }

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('srv.params'))
            ->from($db->quoteName('#__bsms_mediafiles', 'mf'))
            ->leftJoin(
                $db->quoteName('#__bsms_servers', 'srv') .
                ' ON ' . $db->quoteName('srv.id') . ' = ' . $db->quoteName('mf.server_id')
            )
            ->where($db->quoteName('mf.id') . ' = ' . (int) $mediaId);
        $db->setQuery($query);
        $serverParams = $db->loadResult();

        if (empty($serverParams)) {
            return '';
        }

        try {
            $decoded = json_decode($serverParams, true, 512, JSON_THROW_ON_ERROR) ?: [];
        } catch (\JsonException) {
            return '';
        }

        // Prefer the standardized key; fall back to legacy YouTube-specific key
        return $decoded['description_format']
            ?? $decoded['yt_description_format']
            ?? '';
    }

    /**
     * Get comma-separated topic names for a study.
     *
     * @param   int  $studyId  The study ID
     *
     * @return  string  Comma-separated topic names
     *
     * @since   10.2.0
     */
    private static function getTopicsForStudy(int $studyId): string
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('t.topic_text'))
            ->from($db->quoteName('#__bsms_studytopics', 'st'))
            ->leftJoin(
                $db->quoteName('#__bsms_topics', 't') .
                ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.topic_id')
            )
            ->where($db->quoteName('st.study_id') . ' = ' . (int) $studyId);
        $db->setQuery($query);
        $topics = $db->loadColumn() ?: [];

        return implode(', ', array_filter(array_map([Text::class, '_'], $topics)));
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
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
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
                ' ON ' . $db->quoteName('b.booknumber') . ' = ' . $db->quoteName('ss.booknumber')
            )
            ->where($db->quoteName('ss.study_id') . ' = ' . (int) $studyId)
            ->order($db->quoteName('ss.ordering') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?? [];

        $refs = [];

        foreach ($rows as $row) {
            $bookname = $row->bookname ?? '';
            $ref      = Text::_($bookname) . ' ' . ($row->chapter_begin ?? '');

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

    /**
     * Get chapters for a description, either from a specific media file or the
     * first media file on this study that has chapters.
     *
     * @param   int  $studyId  The study ID
     * @param   int  $mediaId  Optional specific media file ID
     *
     * @return  array  Chapters array or empty
     *
     * @since   10.2.0
     */
    private static function getChaptersForDescription(int $studyId, int $mediaId): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if ($mediaId > 0) {
            // Look up chapters from the specific media file
            $query = $db->getQuery(true)
                ->select($db->quoteName('params'))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('id') . ' = ' . $mediaId);
            $db->setQuery($query);
            $params = $db->loadResult();

            if ($params) {
                try {
                    $decoded  = json_decode($params, true, 512, JSON_THROW_ON_ERROR) ?: [];
                    $chapters = $decoded['chapters'] ?? [];

                    if (!empty($chapters)) {
                        return $chapters;
                    }
                } catch (\JsonException) {
                    // Invalid JSON — fall through
                }
            }
        }

        // Fall back: find the first media file on this study that has chapters
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__bsms_mediafiles'))
            ->where($db->quoteName('study_id') . ' = ' . (int) $studyId)
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('ordering') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadColumn();

        foreach ($rows as $paramsJson) {
            if (empty($paramsJson)) {
                continue;
            }

            try {
                $decoded  = json_decode($paramsJson, true, 512, JSON_THROW_ON_ERROR) ?: [];
                $chapters = $decoded['chapters'] ?? [];

                if (!empty($chapters)) {
                    return $chapters;
                }
            } catch (\JsonException) {
                continue;
            }
        }

        return [];
    }
}

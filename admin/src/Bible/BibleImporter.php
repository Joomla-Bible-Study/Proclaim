<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Bible;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bible data importer.
 *
 * Downloads translation JSON from GetBible.net GitHub repository and
 * batch-inserts verses into #__bsms_bible_verses.
 *
 * @since  10.1.0
 */
class BibleImporter
{
    /**
     * GetBible.net GitHub raw base URL for translation JSON files.
     *
     * @var  string
     * @since  10.1.0
     */
    private const GETBIBLE_RAW_URL = 'https://raw.githubusercontent.com/getbible/v2/master/translations/';

    /**
     * Batch insert size.
     *
     * @var  int
     * @since  10.1.0
     */
    private const BATCH_SIZE = 500;

    /**
     * Download and import a translation from GetBible.net GitHub.
     *
     * @param   string  $abbreviation  Translation abbreviation (e.g. "kjv", "web")
     *
     * @return  int  Number of verses imported, or -1 on failure
     *
     * @since  10.1.0
     */
    public static function downloadAndImport(string $abbreviation): int
    {
        $url  = self::GETBIBLE_RAW_URL . $abbreviation . '.json';

        try {
            $http     = HttpFactory::getHttp();
            $response = $http->get($url, [], 60);

            if ($response->code !== 200) {
                return -1;
            }

            $json = $response->body;
        } catch (\Exception $e) {
            return -1;
        }

        return self::importFromJson($json, $abbreviation);
    }

    /**
     * Import verses from a JSON string.
     *
     * The JSON format is the GetBible.net v2 format: an object keyed by book number,
     * each containing a "chapters" object keyed by chapter number, each containing
     * a "verses" array of {verse, text} objects.
     *
     * @param   string  $json          Raw JSON string
     * @param   string  $abbreviation  Translation abbreviation
     *
     * @return  int  Number of verses imported, or -1 on failure
     *
     * @since  10.1.0
     */
    public static function importFromJson(string $json, string $abbreviation): int
    {
        $data = json_decode($json, true);

        if (!is_array($data)) {
            return -1;
        }

        $db         = Factory::getContainer()->get(DatabaseInterface::class);
        $totalCount = 0;
        $batch      = [];

        // Remove existing verses for this translation
        self::removeTranslationVerses($abbreviation);

        foreach ($data as $bookKey => $bookData) {
            if (!is_array($bookData) || !isset($bookData['chapters'])) {
                continue;
            }

            // Book number in GetBible format is the key
            $bookNumber = (int) $bookKey;

            if ($bookNumber < 1 || $bookNumber > 66) {
                continue;
            }

            foreach ($bookData['chapters'] as $chapterKey => $chapterData) {
                if (!is_array($chapterData) || !isset($chapterData['verses'])) {
                    continue;
                }

                $chapterNumber = (int) $chapterKey;

                foreach ($chapterData['verses'] as $verseData) {
                    if (!is_array($verseData) || !isset($verseData['verse'], $verseData['text'])) {
                        continue;
                    }

                    $batch[] = [
                        'translation' => $abbreviation,
                        'book'        => $bookNumber,
                        'chapter'     => $chapterNumber,
                        'verse'       => (int) $verseData['verse'],
                        'text'        => $verseData['text'],
                    ];

                    if (count($batch) >= self::BATCH_SIZE) {
                        self::insertBatch($db, $batch);
                        $totalCount += count($batch);
                        $batch = [];
                    }
                }
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            self::insertBatch($db, $batch);
            $totalCount += count($batch);
        }

        // Update translation record
        self::updateTranslationRecord($abbreviation, $totalCount, $data);

        return $totalCount;
    }

    /**
     * Remove a translation and its verses.
     *
     * @param   string  $abbreviation  Translation abbreviation
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function removeTranslation(string $abbreviation): void
    {
        self::removeTranslationVerses($abbreviation);

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_bible_translations'))
            ->set($db->quoteName('installed') . ' = 0')
            ->set($db->quoteName('verse_count') . ' = 0')
            ->where($db->quoteName('abbreviation') . ' = :abbr')
            ->bind(':abbr', $abbreviation);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Check if a translation is installed (has verses).
     *
     * @param   string  $abbreviation  Translation abbreviation
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    public static function isInstalled(string $abbreviation): bool
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('installed'))
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('abbreviation') . ' = :abbr')
            ->bind(':abbr', $abbreviation);
        $db->setQuery($query);

        return (int) $db->loadResult() === 1;
    }

    /**
     * Delete all verses for a translation.
     *
     * @param   string  $abbreviation  Translation abbreviation
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private static function removeTranslationVerses(string $abbreviation): void
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__bsms_bible_verses'))
            ->where($db->quoteName('translation') . ' = :abbr')
            ->bind(':abbr', $abbreviation);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Batch-insert verse rows.
     *
     * @param   DatabaseInterface  $db     Database driver
     * @param   array              $batch  Array of verse row data
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private static function insertBatch(DatabaseInterface $db, array $batch): void
    {
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_bible_verses'))
            ->columns($db->quoteName(['translation', 'book', 'chapter', 'verse', 'text']));

        foreach ($batch as $row) {
            $query->values(
                $db->quote($row['translation']) . ', '
                . (int) $row['book'] . ', '
                . (int) $row['chapter'] . ', '
                . (int) $row['verse'] . ', '
                . $db->quote($row['text'])
            );
        }

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Update or create the translation record in #__bsms_bible_translations.
     *
     * @param   string  $abbreviation  Translation abbreviation
     * @param   int     $verseCount    Number of verses imported
     * @param   array   $data          The parsed JSON data (for extracting metadata)
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private static function updateTranslationRecord(string $abbreviation, int $verseCount, array $data): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Extract translation name from the JSON data if available
        $name      = '';
        $language  = 'en';
        $copyright = '';

        // GetBible v2 JSON has translation info at the root level or in any book
        foreach ($data as $bookData) {
            if (is_array($bookData)) {
                if (isset($bookData['translation'])) {
                    $name = $bookData['translation'];
                }

                if (isset($bookData['lang'])) {
                    $language = $bookData['lang'];
                }

                if (isset($bookData['translation_note'])) {
                    $copyright = $bookData['translation_note'];
                }

                break;
            }
        }

        if (empty($name)) {
            $name = strtoupper($abbreviation);
        }

        // Check if record exists
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('abbreviation') . ' = :abbr')
            ->bind(':abbr', $abbreviation);
        $db->setQuery($query);
        $exists = (int) $db->loadResult() > 0;

        if ($exists) {
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__bsms_bible_translations'))
                ->set($db->quoteName('installed') . ' = 1')
                ->set($db->quoteName('verse_count') . ' = :count')
                ->set($db->quoteName('name') . ' = :name')
                ->set($db->quoteName('language') . ' = :lang')
                ->set($db->quoteName('copyright') . ' = :copy')
                ->where($db->quoteName('abbreviation') . ' = :abbr')
                ->bind(':count', $verseCount, ParameterType::INTEGER)
                ->bind(':name', $name)
                ->bind(':lang', $language)
                ->bind(':copy', $copyright)
                ->bind(':abbr', $abbreviation);
        } else {
            $columns = ['abbreviation', 'name', 'language', 'source', 'installed', 'verse_count', 'copyright'];
            $query   = $db->getQuery(true)
                ->insert($db->quoteName('#__bsms_bible_translations'))
                ->columns($db->quoteName($columns))
                ->values(
                    $db->quote($abbreviation) . ', '
                    . $db->quote($name) . ', '
                    . $db->quote($language) . ', '
                    . $db->quote('getbible') . ', '
                    . '1, '
                    . (int) $verseCount . ', '
                    . $db->quote($copyright)
                );
        }

        $db->setQuery($query);
        $db->execute();
    }
}

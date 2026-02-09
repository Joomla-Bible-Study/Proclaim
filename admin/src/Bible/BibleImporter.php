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
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Bible data importer.
 *
 * Downloads translation data from the GetBible.net v2 API (book-by-book)
 * and batch-inserts verses into #__bsms_bible_verses.
 *
 * API structure:
 *   /v2/{abbr}/books.json          → list of books with URLs
 *   /v2/{abbr}/{book_nr}.json      → book with chapters[].verses[]
 *
 * @since  10.1.0
 */
class BibleImporter
{
    /**
     * GetBible.net v2 API base URL.
     *
     * @var  string
     * @since  10.1.0
     */
    private const API_BASE_URL = 'https://api.getbible.net/v2/';

    /**
     * Batch insert size.
     *
     * @var  int
     * @since  10.1.0
     */
    private const BATCH_SIZE = 500;

    /**
     * HTTP request timeout in seconds.
     *
     * @var  int
     * @since  10.1.0
     */
    private const HTTP_TIMEOUT = 120;

    /**
     * Download and import a translation from GetBible.net API.
     *
     * Fetches the book list, then downloads each book and inserts verses.
     *
     * @param   string  $abbreviation  Translation abbreviation (e.g. "kjv", "web")
     *
     * @return  int  Number of verses imported, or -1 on failure
     *
     * @since  10.1.0
     */
    public static function downloadAndImport(string $abbreviation): int
    {
        Log::addLogger(
            ['text_file' => 'com_proclaim.bible.php'],
            Log::ALL,
            ['com_proclaim.bible']
        );

        $http = HttpFactory::getHttp();

        // Fetch list of books for this translation
        $booksUrl = self::API_BASE_URL . $abbreviation . '/books.json';

        try {
            $response = $http->get($booksUrl, [], self::HTTP_TIMEOUT);

            if ($response->code !== 200) {
                Log::add('BibleImporter: HTTP ' . $response->code . ' fetching book list for "' . $abbreviation . '"', Log::ERROR, 'com_proclaim.bible');

                return -1;
            }

            $books = json_decode($response->body, true);

            if (!\is_array($books) || empty($books)) {
                Log::add('BibleImporter: Invalid or empty book list for "' . $abbreviation . '"', Log::ERROR, 'com_proclaim.bible');

                return -1;
            }
        } catch (\Exception $e) {
            Log::add('BibleImporter: Error fetching book list for "' . $abbreviation . '": ' . $e->getMessage(), Log::ERROR, 'com_proclaim.bible');

            return -1;
        }

        $db         = Factory::getContainer()->get(DatabaseInterface::class);
        $totalCount = 0;
        $batch      = [];
        $metadata   = [];

        // Remove existing verses for this translation before importing
        self::removeTranslationVerses($abbreviation);

        // Download each book
        foreach ($books as $bookInfo) {
            if (!\is_array($bookInfo) || !isset($bookInfo['nr'])) {
                continue;
            }

            $bookNr  = (int) $bookInfo['nr'];
            $bookUrl = self::API_BASE_URL . $abbreviation . '/' . $bookNr . '.json';

            try {
                $bookResponse = $http->get($bookUrl, [], self::HTTP_TIMEOUT);

                if ($bookResponse->code !== 200) {
                    continue;
                }

                $bookData = json_decode($bookResponse->body, true);

                if (!\is_array($bookData) || !isset($bookData['chapters'])) {
                    continue;
                }
            } catch (\Exception $e) {
                continue;
            }

            // Capture metadata from first book
            if (empty($metadata)) {
                $metadata = [
                    'translation' => $bookData['translation'] ?? strtoupper($abbreviation),
                    'lang'        => $bookData['lang'] ?? 'en',
                ];
            }

            // chapters is an array of {chapter, name, verses[]}
            foreach ($bookData['chapters'] as $chapterData) {
                if (!\is_array($chapterData) || !isset($chapterData['verses'])) {
                    continue;
                }

                $chapterNumber = (int) ($chapterData['chapter'] ?? 0);

                foreach ($chapterData['verses'] as $verseData) {
                    if (!\is_array($verseData) || !isset($verseData['verse'], $verseData['text'])) {
                        continue;
                    }

                    $batch[] = [
                        'translation' => $abbreviation,
                        'book'        => $bookNr,
                        'chapter'     => $chapterNumber,
                        'verse'       => (int) $verseData['verse'],
                        'text'        => $verseData['text'],
                    ];

                    if (\count($batch) >= self::BATCH_SIZE) {
                        self::insertBatch($db, $batch);
                        $totalCount += \count($batch);
                        $batch = [];
                    }
                }
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            self::insertBatch($db, $batch);
            $totalCount += \count($batch);
        }

        // Update translation record
        self::updateTranslationRecord($abbreviation, $totalCount, $metadata);

        return $totalCount;
    }

    /**
     * Import verses from a JSON string (single book format).
     *
     * @param   string  $json          Raw JSON string (book-level from GetBible v2 API)
     * @param   string  $abbreviation  Translation abbreviation
     *
     * @return  int  Number of verses imported, or -1 on failure
     *
     * @since  10.1.0
     */
    public static function importFromJson(string $json, string $abbreviation): int
    {
        $data = json_decode($json, true);

        if (!\is_array($data)) {
            return -1;
        }

        $db         = Factory::getContainer()->get(DatabaseInterface::class);
        $totalCount = 0;
        $batch      = [];

        // Remove existing verses for this translation
        self::removeTranslationVerses($abbreviation);

        // Handle single-book format: {chapters: [{chapter, verses: [{verse, text}]}]}
        if (isset($data['chapters'])) {
            $bookNr = (int) ($data['nr'] ?? $data['book_nr'] ?? 1);

            foreach ($data['chapters'] as $chapterData) {
                if (!\is_array($chapterData) || !isset($chapterData['verses'])) {
                    continue;
                }

                $chapterNumber = (int) ($chapterData['chapter'] ?? 0);

                foreach ($chapterData['verses'] as $verseData) {
                    if (!\is_array($verseData) || !isset($verseData['verse'], $verseData['text'])) {
                        continue;
                    }

                    $batch[] = [
                        'translation' => $abbreviation,
                        'book'        => $bookNr,
                        'chapter'     => $chapterNumber,
                        'verse'       => (int) $verseData['verse'],
                        'text'        => $verseData['text'],
                    ];

                    if (\count($batch) >= self::BATCH_SIZE) {
                        self::insertBatch($db, $batch);
                        $totalCount += \count($batch);
                        $batch = [];
                    }
                }
            }
        }

        // Insert remaining
        if (!empty($batch)) {
            self::insertBatch($db, $batch);
            $totalCount += \count($batch);
        }

        // Update translation record
        $metadata = [
            'translation' => $data['translation'] ?? strtoupper($abbreviation),
            'lang'        => $data['lang'] ?? 'en',
        ];
        self::updateTranslationRecord($abbreviation, $totalCount, $metadata);

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
     * Remove all installed translations and their verses.
     *
     * @return  int  Number of translations removed
     *
     * @since  10.1.0
     */
    public static function removeAllTranslations(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Delete all verses
        $query = $db->getQuery(true)->delete($db->quoteName('#__bsms_bible_verses'));
        $db->setQuery($query);
        $db->execute();

        // Count installed before resetting
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('installed') . ' = 1');
        $db->setQuery($query);
        $count = (int) $db->loadResult();

        // Reset all translation records
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_bible_translations'))
            ->set($db->quoteName('installed') . ' = 0')
            ->set($db->quoteName('verse_count') . ' = 0')
            ->where($db->quoteName('installed') . ' = 1');
        $db->setQuery($query);
        $db->execute();

        return $count;
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
     * @param   array   $metadata      Metadata array with 'translation' and 'lang' keys
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private static function updateTranslationRecord(string $abbreviation, int $verseCount, array $metadata): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $name      = $metadata['translation'] ?? strtoupper($abbreviation);
        $language  = $metadata['lang'] ?? 'en';
        $copyright = $metadata['translation_note'] ?? '';

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

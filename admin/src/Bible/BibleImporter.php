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
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Http\HttpFactory;

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
     * Cached result of whether the data_size column exists.
     *
     * @var  bool|null
     * @since  10.1.0
     */
    private static ?bool $hasDataSize = null;

    /**
     * Cached result of whether the downloaded_at column exists.
     *
     * @var  bool|null
     * @since  10.1.0
     */
    private static ?bool $hasDownloadedAt = null;

    /**
     * Download and import a translation from GetBible.net API.
     *
     * Fetches the book list, then downloads each book and inserts verses.
     *
     * @param   string  $abbreviation  Translation abbreviation (e.g. "kjv", "web")
     * @param   bool    $force         When true, skip the early-exit guard and re-download
     *                                 from the API even if verses already exist locally.
     *
     * @return  int  Number of verses imported, or -1 on failure
     *
     * @since  10.1.0
     */
    public static function downloadAndImport(string $abbreviation, bool $force = false): int
    {
        Log::addLogger(
            ['text_file' => 'com_proclaim.bible.php'],
            Log::ALL,
            ['com_proclaim.bible']
        );

        // If verses already exist for this translation and force is not set,
        // just reconcile the record and return — no need to re-download.
        if (!$force) {
            $db        = Factory::getContainer()->get(DatabaseInterface::class);
            $checkQ    = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_bible_verses'))
                ->where($db->quoteName('translation') . ' = :abbr')
                ->bind(':abbr', $abbreviation);
            $db->setQuery($checkQ);
            $existing = (int) $db->loadResult();

            if ($existing > 0) {
                self::updateTranslationRecord($abbreviation, $existing, [], 0);
                Log::add(
                    'BibleImporter: skipped download for "' . $abbreviation . '" — ' . $existing . ' verses already present',
                    Log::INFO,
                    'com_proclaim.bible'
                );

                return $existing;
            }
        }

        $factory  = new HttpFactory();
        $http     = $factory->getHttp();
        $headers  = [
            'Accept' => 'application/json',
        ];

        // Fetch list of books for this translation
        $booksUrl = self::API_BASE_URL . $abbreviation . '/books.json';

        try {
            $response = $http->get($booksUrl, $headers, self::HTTP_TIMEOUT);

            if ($response->code !== 200) {
                Log::add('BibleImporter: HTTP ' . $response->code . ' fetching book list for "' . $abbreviation . '"', Log::ERROR, 'com_proclaim.bible');

                return -1;
            }

            try {
                $books = json_decode($response->body, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                $books = null;
            }

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
        $totalSize  = 0;
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

                try {
                    $bookData = json_decode($bookResponse->body, true, 512, JSON_THROW_ON_ERROR);
                } catch (\JsonException) {
                    continue;
                }

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

                    $totalSize += \strlen($verseData['text']);
                    $batch[]    = [
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

        // Update translation record (data_size cached here, no expensive SUM query later)
        self::updateTranslationRecord($abbreviation, $totalCount, $metadata, $totalSize);

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
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return -1;
        }

        if (!\is_array($data)) {
            return -1;
        }

        $db         = Factory::getContainer()->get(DatabaseInterface::class);
        $totalCount = 0;
        $totalSize  = 0;
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

                    $totalSize += \strlen($verseData['text']);
                    $batch[]    = [
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

        // Update translation record (data_size cached here, no expensive SUM query later)
        $metadata = [
            'translation' => $data['translation'] ?? strtoupper($abbreviation),
            'lang'        => $data['lang'] ?? 'en',
        ];
        self::updateTranslationRecord($abbreviation, $totalCount, $metadata, $totalSize);

        return $totalCount;
    }

    /**
     * Check whether a translation is a protected core translation.
     *
     * Core translations (KJV, WEB) are bundled with the component and
     * cannot be removed by the user.
     *
     * @param   string  $abbreviation  Translation abbreviation
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    public static function isCoreTranslation(string $abbreviation): bool
    {
        return \in_array(strtolower($abbreviation), ['kjv', 'web'], true);
    }

    /**
     * Remove a translation and its verses.
     *
     * Core (bundled) translations cannot be removed.
     *
     * @param   string  $abbreviation  Translation abbreviation
     *
     * @return  void
     *
     * @throws  \RuntimeException  If the translation is a core translation
     *
     * @since  10.1.0
     */
    public static function removeTranslation(string $abbreviation): void
    {
        if (self::isCoreTranslation($abbreviation)) {
            throw new \RuntimeException(
                \sprintf('Cannot remove core translation %s', strtoupper($abbreviation))
            );
        }

        self::removeTranslationVerses($abbreviation);

        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_bible_translations'))
            ->set($db->quoteName('installed') . ' = 0')
            ->set($db->quoteName('verse_count') . ' = 0')
            ->where($db->quoteName('abbreviation') . ' = :abbr')
            ->bind(':abbr', $abbreviation);

        if (self::hasDataSizeColumn()) {
            $query->set($db->quoteName('data_size') . ' = 0');
        }

        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Remove all installed translations and their verses, except core translations.
     *
     * Core (bundled) translations (KJV, WEB) are preserved.
     *
     * @return  int  Number of translations removed
     *
     * @since  10.1.0
     */
    public static function removeAllTranslations(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Delete verses for non-core translations only
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__bsms_bible_verses'))
            ->where($db->quoteName('translation') . ' NOT IN (' . implode(',', array_map([$db, 'quote'], ['kjv', 'web'])) . ')');
        $db->setQuery($query);
        $db->execute();

        // Count installed non-core before resetting
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('installed') . ' = 1')
            ->where($db->quoteName('bundled') . ' = 0');
        $db->setQuery($query);
        $count = (int) $db->loadResult();

        // Reset non-core translation records
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__bsms_bible_translations'))
            ->set($db->quoteName('installed') . ' = 0')
            ->set($db->quoteName('verse_count') . ' = 0')
            ->where($db->quoteName('installed') . ' = 1')
            ->where($db->quoteName('bundled') . ' = 0');

        if (self::hasDataSizeColumn()) {
            $query->set($db->quoteName('data_size') . ' = 0');
        }
        $db->setQuery($query);
        $db->execute();

        return $count;
    }

    /**
     * Remove non-installed translation records from a specific provider source.
     *
     * Used when disabling a provider to clean up synced entries that were
     * never downloaded locally. Installed translations and bundled entries
     * are preserved.
     *
     * @param   string  $source  Provider source identifier (e.g. 'api_bible', 'getbible')
     *
     * @return  int  Number of records removed
     *
     * @since  10.1.0
     */
    public static function removeProviderEntries(string $source): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Count entries that will be removed
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('source') . ' = :source')
            ->where($db->quoteName('installed') . ' = 0')
            ->where($db->quoteName('bundled') . ' = 0')
            ->bind(':source', $source);
        $db->setQuery($query);
        $count = (int) $db->loadResult();

        if ($count > 0) {
            // Delete non-installed, non-bundled records from this provider
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__bsms_bible_translations'))
                ->where($db->quoteName('source') . ' = :source')
                ->where($db->quoteName('installed') . ' = 0')
                ->where($db->quoteName('bundled') . ' = 0')
                ->bind(':source', $source);
            $db->setQuery($query);
            $db->execute();
        }

        return $count;
    }

    /**
     * Default GetBible translation catalog entries.
     *
     * These are re-inserted when the provider is (re-)enabled and the
     * catalog has been depleted by a previous disable/cleanup.
     *
     * @var  array<array{0: string, 1: string, 2: string, 3: int, 4: int}>
     *       [abbreviation, name, language, bundled, estimated_size]
     * @since  10.1.0
     */
    private const GETBIBLE_SEED = [
        ['kjv',          'King James Version',        'en', 1, 4000000],
        ['akjv',         'American King James Version', 'en', 0, 4000000],
        ['web',          'World English Bible',       'en', 1, 4300000],
        ['asv',          'American Standard Version',  'en', 0, 4100000],
        ['ylt',          "Young's Literal Translation", 'en', 0, 4000000],
        ['basicenglish', 'Bible in Basic English',     'en', 0, 3500000],
        ['douayrheims',  'Douay-Rheims Bible',         'en', 0, 4200000],
        ['wb',           'Webster Bible',              'en', 0, 4000000],
        ['darby',        'Darby Translation',          'en', 0, 4000000],
        ['vulgate',      'Vulgata Clementina',         'la', 0, 3800000],
        ['almeida',      'Almeida Atualizada',         'pt', 0, 4000000],
        ['luther1545',   'Luther (1545)',              'de', 0, 4200000],
        ['ls1910',       'Louis Segond 1910',          'fr', 0, 4100000],
        ['synodal',      'Synodal Translation',        'ru', 0, 4500000],
        ['valera',       'Reina Valera (1909)',        'es', 0, 4100000],
        ['karoli',       'Karoli Bible',               'hu', 0, 4000000],
        ['giovanni',     'Giovanni Diodati Bible',     'it', 0, 4100000],
        ['cornilescu',   'Cornilescu Bible',           'ro', 0, 3900000],
        ['korean',       'Korean Bible',               'ko', 0, 3800000],
        ['cus',          'Chinese Union Simplified',   'zh', 0, 2500000],
    ];

    /**
     * Seed the GetBible translation catalog if depleted.
     *
     * Called when GetBible is enabled but the catalog has no uninstalled
     * entries (e.g. after a disable/re-enable cycle). Uses INSERT IGNORE
     * so existing rows (installed translations) are not overwritten.
     *
     * @return  int  Number of rows inserted
     *
     * @since  10.1.0
     */
    public static function seedGetBibleCatalog(): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Check if there are already uninstalled getbible entries — skip if so
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('source') . ' = ' . $db->quote('getbible'));
        $db->setQuery($query);
        $existing = (int) $db->loadResult();

        // If we already have a reasonable catalog, don't re-seed
        if ($existing >= \count(self::GETBIBLE_SEED)) {
            return 0;
        }

        $inserted = 0;

        foreach (self::GETBIBLE_SEED as [$abbr, $name, $lang, $bundled, $size]) {
            // Check if row already exists
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->where($db->quoteName('abbreviation') . ' = :abbr')
                ->bind(':abbr', $abbr);
            $db->setQuery($query);

            if ((int) $db->loadResult() > 0) {
                continue;
            }

            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__bsms_bible_translations'))
                ->columns($db->quoteName(['abbreviation', 'name', 'language', 'source', 'installed', 'bundled', 'estimated_size']))
                ->values(':abbr, :name, :lang, ' . $db->quote('getbible') . ', 0, :bundled, :size')
                ->bind(':abbr', $abbr)
                ->bind(':name', $name)
                ->bind(':lang', $lang)
                ->bind(':bundled', $bundled, ParameterType::INTEGER)
                ->bind(':size', $size, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();
            $inserted++;
        }

        return $inserted;
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
     * Check whether the data_size column exists on the translations table.
     *
     * The column was added in 10.1.0 and may not exist on databases that
     * haven't run the migration yet.  Result is cached for the request.
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    private static function hasDataSizeColumn(): bool
    {
        if (self::$hasDataSize !== null) {
            return self::$hasDataSize;
        }

        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $rows = $db->setQuery(
            'SHOW COLUMNS FROM ' . $db->quoteName('#__bsms_bible_translations')
            . ' LIKE ' . $db->quote('data_size')
        )->loadObjectList();

        self::$hasDataSize = \count($rows) > 0;

        return self::$hasDataSize;
    }

    /**
     * Check whether the downloaded_at column exists on the translations table.
     *
     * The column was added in 10.1.0 and may not exist on databases that
     * haven't run the migration yet.  Result is cached for the request.
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    private static function hasDownloadedAtColumn(): bool
    {
        if (self::$hasDownloadedAt !== null) {
            return self::$hasDownloadedAt;
        }

        $db   = Factory::getContainer()->get(DatabaseInterface::class);
        $rows = $db->setQuery(
            'SHOW COLUMNS FROM ' . $db->quoteName('#__bsms_bible_translations')
            . ' LIKE ' . $db->quote('downloaded_at')
        )->loadObjectList();

        self::$hasDownloadedAt = \count($rows) > 0;

        return self::$hasDownloadedAt;
    }

    /**
     * Update or create the translation record in #__bsms_bible_translations.
     *
     * @param   string  $abbreviation  Translation abbreviation
     * @param   int     $verseCount    Number of verses imported
     * @param   array   $metadata      Metadata array with 'translation' and 'lang' keys
     * @param   int     $dataSize      Total stored text size in bytes
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private static function updateTranslationRecord(string $abbreviation, int $verseCount, array $metadata, int $dataSize = 0): void
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

        $withSize       = self::hasDataSizeColumn();
        $withDownloaded = self::hasDownloadedAtColumn();

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

            if ($withSize) {
                $query->set($db->quoteName('data_size') . ' = :size')
                    ->bind(':size', $dataSize, ParameterType::INTEGER);
            }

            if ($withDownloaded) {
                $now = Factory::getDate()->toSql();
                $query->set($db->quoteName('downloaded_at') . ' = :dlat')
                    ->bind(':dlat', $now);
            }
        } else {
            $columns = ['abbreviation', 'name', 'language', 'source', 'installed', 'verse_count', 'copyright'];
            $values  = $db->quote($abbreviation) . ', '
                . $db->quote($name) . ', '
                . $db->quote($language) . ', '
                . $db->quote('getbible') . ', '
                . '1, '
                . (int) $verseCount . ', '
                . $db->quote($copyright);

            if ($withSize) {
                $columns[] = 'data_size';
                $values .= ', ' . (int) $dataSize;
            }

            if ($withDownloaded) {
                $columns[] = 'downloaded_at';
                $values .= ', ' . $db->quote(Factory::getDate()->toSql());
            }

            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__bsms_bible_translations'))
                ->columns($db->quoteName($columns))
                ->values($values);
        }

        $db->setQuery($query);
        $db->execute();
    }
}

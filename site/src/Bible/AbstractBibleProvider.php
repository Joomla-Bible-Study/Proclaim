<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Bible;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Http\HttpFactory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract base class for Bible providers.
 *
 * Provides shared utilities: book name mapping, cache read/write, HTTP fetching.
 *
 * @since  10.1.0
 */
abstract class AbstractBibleProvider implements BibleProviderInterface
{
    /**
     * Mapping from Proclaim book numbers (101-166) to standard book numbers (1-66).
     *
     * @var  array<int, int>
     * @since  10.1.0
     */
    protected const PROCLAIM_TO_STANDARD = [
        101 => 1, 102 => 2, 103 => 3, 104 => 4, 105 => 5,
        106 => 6, 107 => 7, 108 => 8, 109 => 9, 110 => 10,
        111 => 11, 112 => 12, 113 => 13, 114 => 14, 115 => 15,
        116 => 16, 117 => 17, 118 => 18, 119 => 19, 120 => 20,
        121 => 21, 122 => 22, 123 => 23, 124 => 24, 125 => 25,
        126 => 26, 127 => 27, 128 => 28, 129 => 29, 130 => 30,
        131 => 31, 132 => 32, 133 => 33, 134 => 34, 135 => 35,
        136 => 36, 137 => 37, 138 => 38, 139 => 39, 140 => 40,
        141 => 41, 142 => 42, 143 => 43, 144 => 44, 145 => 45,
        146 => 46, 147 => 47, 148 => 48, 149 => 49, 150 => 50,
        151 => 51, 152 => 52, 153 => 53, 154 => 54, 155 => 55,
        156 => 56, 157 => 57, 158 => 58, 159 => 59, 160 => 60,
        161 => 61, 162 => 62, 163 => 63, 164 => 64, 165 => 65,
        166 => 66,
    ];

    /**
     * Standard book names indexed by standard book number (1-66).
     *
     * @var  array<int, string>
     * @since  10.1.0
     */
    protected const BOOK_NAMES = [
        1  => 'Genesis', 2 => 'Exodus', 3 => 'Leviticus', 4 => 'Numbers',
        5  => 'Deuteronomy', 6 => 'Joshua', 7 => 'Judges', 8 => 'Ruth',
        9  => '1 Samuel', 10 => '2 Samuel', 11 => '1 Kings', 12 => '2 Kings',
        13 => '1 Chronicles', 14 => '2 Chronicles', 15 => 'Ezra',
        16 => 'Nehemiah', 17 => 'Esther', 18 => 'Job', 19 => 'Psalms',
        20 => 'Proverbs', 21 => 'Ecclesiastes', 22 => 'Song of Solomon',
        23 => 'Isaiah', 24 => 'Jeremiah', 25 => 'Lamentations',
        26 => 'Ezekiel', 27 => 'Daniel', 28 => 'Hosea', 29 => 'Joel',
        30 => 'Amos', 31 => 'Obadiah', 32 => 'Jonah', 33 => 'Micah',
        34 => 'Nahum', 35 => 'Habakkuk', 36 => 'Zephaniah', 37 => 'Haggai',
        38 => 'Zechariah', 39 => 'Malachi',
        40 => 'Matthew', 41 => 'Mark', 42 => 'Luke', 43 => 'John',
        44 => 'Acts', 45 => 'Romans', 46 => '1 Corinthians',
        47 => '2 Corinthians', 48 => 'Galatians', 49 => 'Ephesians',
        50 => 'Philippians', 51 => 'Colossians', 52 => '1 Thessalonians',
        53 => '2 Thessalonians', 54 => '1 Timothy', 55 => '2 Timothy',
        56 => 'Titus', 57 => 'Philemon', 58 => 'Hebrews', 59 => 'James',
        60 => '1 Peter', 61 => '2 Peter', 62 => '1 John', 63 => '2 John',
        64 => '3 John', 65 => 'Jude', 66 => 'Revelation',
    ];

    /**
     * Whether the Joomla logger has been registered for our category.
     *
     * @var  bool
     * @since  10.1.0
     */
    private static bool $loggerRegistered = false;

    /**
     * Cache TTL in seconds. Default 24 hours; configurable via admin params.
     *
     * @var  int
     * @since  10.1.0
     */
    protected int $cacheTtl = 86400;

    /**
     * Register the Joomla logger for the com_proclaim.bible category.
     *
     * Call once before any Log::add() calls. Safe to call multiple times.
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public static function registerLogger(): void
    {
        if (!self::$loggerRegistered) {
            Log::addLogger(
                ['text_file' => 'com_proclaim.bible.php'],
                Log::ALL,
                ['com_proclaim.bible']
            );
            self::$loggerRegistered = true;
        }
    }

    /**
     * Set the cache TTL in seconds.
     *
     * @param   int  $seconds  TTL in seconds
     *
     * @return  void
     *
     * @since  10.1.0
     */
    public function setCacheTtl(int $seconds): void
    {
        $this->cacheTtl = max(3600, $seconds);
    }

    /**
     * Get the database driver.
     *
     * @return  DatabaseInterface
     *
     * @since  10.1.0
     */
    protected function getDatabase(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * Convert Proclaim book number (101-166) to standard (1-66).
     *
     * @param   int  $proclaimBook  Proclaim book number
     *
     * @return  int  Standard book number, or 0 if invalid
     *
     * @since  10.1.0
     */
    public static function proclaimToStandard(int $proclaimBook): int
    {
        return self::PROCLAIM_TO_STANDARD[$proclaimBook] ?? 0;
    }

    /**
     * Convert standard book number (1-66) to Proclaim (101-166).
     *
     * @param   int  $standardBook  Standard book number
     *
     * @return  int  Proclaim book number
     *
     * @since  10.1.0
     */
    public static function standardToProclaim(int $standardBook): int
    {
        return $standardBook + 100;
    }

    /**
     * Get a book name by standard book number.
     *
     * @param   int  $bookNumber  Standard book number (1-66)
     *
     * @return  string  Book name or empty string
     *
     * @since  10.1.0
     */
    public static function getBookName(int $bookNumber): string
    {
        return self::BOOK_NAMES[$bookNumber] ?? '';
    }

    /**
     * Read a cached passage from the scripture_cache table.
     *
     * @param   string  $provider     Provider name
     * @param   string  $translation  Translation abbreviation
     * @param   string  $reference    Reference string
     *
     * @return  BiblePassageResult|null  Cached result or null if not found/expired
     *
     * @since  10.1.0
     */
    protected function readCache(string $provider, string $translation, string $reference): ?BiblePassageResult
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['text', 'copyright']))
            ->from($db->quoteName('#__bsms_scripture_cache'))
            ->where($db->quoteName('provider') . ' = :provider')
            ->where($db->quoteName('translation') . ' = :translation')
            ->where($db->quoteName('reference') . ' = :reference')
            ->where($db->quoteName('expires_at') . ' > NOW()')
            ->bind(':provider', $provider)
            ->bind(':translation', $translation)
            ->bind(':reference', $reference);

        $db->setQuery($query);
        $row = $db->loadObject();

        if (!$row) {
            return null;
        }

        return new BiblePassageResult(
            text: $row->text,
            reference: $reference,
            translation: $translation,
            copyright: $row->copyright ?? '',
            isHtml: false
        );
    }

    /**
     * Write a passage to the scripture_cache table.
     *
     * @param   string  $provider     Provider name
     * @param   string  $translation  Translation abbreviation
     * @param   string  $reference    Reference string
     * @param   string  $text         The passage text
     * @param   string  $copyright    Copyright notice
     *
     * @return  void
     *
     * @since  10.1.0
     */
    protected function writeCache(
        string $provider,
        string $translation,
        string $reference,
        string $text,
        string $copyright = ''
    ): void {
        $db        = $this->getDatabase();
        $expiresAt = date('Y-m-d H:i:s', time() + $this->cacheTtl);

        // Upsert: delete old entry then insert new
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__bsms_scripture_cache'))
            ->where($db->quoteName('provider') . ' = :provider')
            ->where($db->quoteName('translation') . ' = :translation')
            ->where($db->quoteName('reference') . ' = :reference')
            ->bind(':provider', $provider)
            ->bind(':translation', $translation)
            ->bind(':reference', $reference);
        $db->setQuery($query);
        $db->execute();

        $columns = ['provider', 'translation', 'reference', 'text', 'copyright', 'expires_at'];
        $values  = ':provider2, :translation2, :reference2, :text, :copyright, :expires_at';

        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__bsms_scripture_cache'))
            ->columns($db->quoteName($columns))
            ->values($values)
            ->bind(':provider2', $provider)
            ->bind(':translation2', $translation)
            ->bind(':reference2', $reference)
            ->bind(':text', $text)
            ->bind(':copyright', $copyright)
            ->bind(':expires_at', $expiresAt);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Maximum number of HTTP retry attempts.
     *
     * @var  int
     * @since  10.1.0
     */
    private const HTTP_MAX_RETRIES = 3;

    /**
     * Whether the last httpGet() failure was a transient (retryable) error.
     *
     * Set after each httpGet() call. Callers can inspect this to decide
     * whether to attempt a fallback or report a permanent failure.
     *
     * @var  bool
     * @since  10.1.0
     */
    protected bool $lastErrorTransient = false;

    /**
     * Whether the last error was transient (retryable).
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    public function isLastErrorTransient(): bool
    {
        return $this->lastErrorTransient;
    }

    /**
     * Detect HTML responses (DDoS gatekeeper pages).
     *
     * Some Bible API providers return HTML instead of JSON when under
     * DDoS protection. This detects that condition.
     *
     * @param   string  $body  Response body to check
     *
     * @return  bool  True if the body appears to be HTML
     *
     * @since  10.1.0
     */
    protected static function isHtmlResponse(string $body): bool
    {
        $trimmed = ltrim($body);

        return str_starts_with($trimmed, '<!') || str_starts_with(strtolower($trimmed), '<html');
    }

    /**
     * Perform an HTTP GET request with retry and backoff.
     *
     * Retries on transient errors (429, 503, timeouts, HTML gatekeeper pages).
     * Non-retryable errors (400, 404) fail immediately.
     *
     * @param   string  $url      The URL to fetch
     * @param   int     $timeout  Timeout in seconds
     *
     * @return  string|null  Response body or null on failure
     *
     * @since  10.1.0
     */
    protected function httpGet(string $url, int $timeout = 10): ?string
    {
        $this->lastErrorTransient = false;
        $factory                  = new HttpFactory();
        $http                     = $factory->getHttp();
        $host                     = strtok($url, '?');

        for ($attempt = 1; $attempt <= self::HTTP_MAX_RETRIES; $attempt++) {
            // Exponential backoff: 0s, 2s, 4s
            if ($attempt > 1) {
                $delay = (int) pow(2, $attempt - 1);
                Log::add(
                    'Retry ' . ($attempt - 1) . '/' . (self::HTTP_MAX_RETRIES - 1) . ' for ' . $host . ' (waiting ' . $delay . 's)',
                    Log::WARNING,
                    'com_proclaim.bible'
                );
                sleep($delay);
            }

            try {
                $response = $http->get($url, [], $timeout);
            } catch (\Exception $e) {
                // Network error / timeout — transient, retry
                Log::add('HTTP request failed (attempt ' . $attempt . '): ' . $e->getMessage(), Log::WARNING, 'com_proclaim.bible');
                $this->lastErrorTransient = true;

                continue;
            }

            $code = $response->getStatusCode();
            $body = (string) $response->getBody();

            // Non-retryable client errors — fail immediately
            if (\in_array($code, [400, 404], true)) {
                Log::add('HTTP ' . $code . ' from ' . $host . ' (not retryable)', Log::WARNING, 'com_proclaim.bible');
                $this->lastErrorTransient = false;

                return null;
            }

            // Retryable server errors
            if (\in_array($code, [429, 503], true)) {
                Log::add('HTTP ' . $code . ' from ' . $host . ' (attempt ' . $attempt . ')', Log::WARNING, 'com_proclaim.bible');
                $this->lastErrorTransient = true;

                continue;
            }

            if ($code === 200) {
                // Detect DDoS gatekeeper HTML pages returned with 200
                if (self::isHtmlResponse($body)) {
                    Log::add('HTML gatekeeper detected from ' . $host . ' (attempt ' . $attempt . ')', Log::WARNING, 'com_proclaim.bible');
                    $this->lastErrorTransient = true;

                    continue;
                }

                return $body;
            }

            // Other non-200 codes — log and retry
            Log::add('HTTP ' . $code . ' from ' . $host . ' (attempt ' . $attempt . ')', Log::WARNING, 'com_proclaim.bible');
            $this->lastErrorTransient = true;
        }

        Log::add('All ' . self::HTTP_MAX_RETRIES . ' attempts exhausted for ' . $host, Log::ERROR, 'com_proclaim.bible');

        return null;
    }
}

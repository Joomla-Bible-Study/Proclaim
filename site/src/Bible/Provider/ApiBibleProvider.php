<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Site
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Site\Bible\Provider;

use CWM\Component\Proclaim\Site\Bible\AbstractBibleProvider;
use CWM\Component\Proclaim\Site\Bible\BiblePassageResult;
use Joomla\CMS\Log\Log;
use Joomla\Http\HttpFactory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * API.Bible provider (American Bible Society).
 *
 * Retrieves scripture passages from the API.Bible REST API using server-side
 * HTTP requests. Provides access to copyrighted translations (NIV, ESV, NLT, etc.)
 * when the user has an approved API key.
 *
 * Privacy: All requests are server-side. Visitor IPs are never exposed.
 * FUMS tracking is done via server-side ping (no client-side JS).
 *
 * API docs: https://docs.api.bible/
 *
 * @since  10.1.0
 */
class ApiBibleProvider extends AbstractBibleProvider
{
    /**
     * API base URL.
     *
     * @var  string
     * @since  10.1.0
     */
    private const API_BASE = 'https://rest.api.bible/v1';

    /**
     * FUMS tracking base URL for server-side pings.
     *
     * @var  string
     * @since  10.1.0
     */
    private const FUMS_BASE = 'https://fums.api.bible/f3';

    /**
     * OSIS book codes indexed by standard book number (1-66).
     *
     * @var  array<int, string>
     * @since  10.1.0
     */
    private const OSIS_CODES = [
        1  => 'GEN', 2 => 'EXO', 3 => 'LEV', 4 => 'NUM', 5 => 'DEU',
        6  => 'JOS', 7 => 'JDG', 8 => 'RUT', 9 => '1SA', 10 => '2SA',
        11 => '1KI', 12 => '2KI', 13 => '1CH', 14 => '2CH', 15 => 'EZR',
        16 => 'NEH', 17 => 'EST', 18 => 'JOB', 19 => 'PSA', 20 => 'PRO',
        21 => 'ECC', 22 => 'SNG', 23 => 'ISA', 24 => 'JER', 25 => 'LAM',
        26 => 'EZK', 27 => 'DAN', 28 => 'HOS', 29 => 'JOL', 30 => 'AMO',
        31 => 'OBA', 32 => 'JON', 33 => 'MIC', 34 => 'NAM', 35 => 'HAB',
        36 => 'ZEP', 37 => 'HAG', 38 => 'ZEC', 39 => 'MAL',
        40 => 'MAT', 41 => 'MRK', 42 => 'LUK', 43 => 'JHN',
        44 => 'ACT', 45 => 'ROM', 46 => '1CO', 47 => '2CO',
        48 => 'GAL', 49 => 'EPH', 50 => 'PHP', 51 => 'COL',
        52 => '1TH', 53 => '2TH', 54 => '1TI', 55 => '2TI',
        56 => 'TIT', 57 => 'PHM', 58 => 'HEB', 59 => 'JAS',
        60 => '1PE', 61 => '2PE', 62 => '1JN', 63 => '2JN',
        64 => '3JN', 65 => 'JUD', 66 => 'REV',
    ];

    /**
     * The API key for authentication.
     *
     * @var  string
     * @since  10.1.0
     */
    private string $apiKey;

    /**
     * Constructor.
     *
     * @param   string  $apiKey  API.Bible API key
     *
     * @since  10.1.0
     */
    public function __construct(string $apiKey = '')
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @inheritDoc
     */
    public function getPassage(string $reference, string $translation): BiblePassageResult
    {
        if (empty($this->apiKey)) {
            Log::add('ApiBible: No API key configured — cannot fetch "' . $reference . '"', Log::WARNING, 'com_proclaim.bible');

            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        // Check cache first
        $cached = $this->readCache('api_bible', $translation, $reference);

        if ($cached) {
            return $cached;
        }

        // Look up the provider_id (api.bible Bible ID) from translations table
        $bibleId = $this->getBibleId($translation);

        if (empty($bibleId)) {
            Log::add('ApiBible: No Bible ID (provider_id) for translation "' . $translation . '"', Log::WARNING, 'com_proclaim.bible');

            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        // Parse reference (e.g. "John+3:16-18" or "Luke+1:20-2:5")
        $passageId = $this->buildPassageId($reference);

        if (empty($passageId)) {
            Log::add('ApiBible: Failed to parse reference "' . $reference . '" into OSIS passage ID', Log::WARNING, 'com_proclaim.bible');

            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        // Call the API
        $url  = self::API_BASE . '/bibles/' . urlencode($bibleId)
            . '/passages/' . urlencode($passageId)
            . '?content-type=text&include-verse-numbers=true';
        $body = $this->httpGetWithApiKey($url);

        if ($body === null) {
            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $data = null;
        }

        if (!\is_array($data) || !isset($data['data'])) {
            Log::add('ApiBible: Invalid JSON response for "' . $reference . '" (' . $translation . ')', Log::ERROR, 'com_proclaim.bible');

            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        $passage   = $data['data'];
        $text      = trim($passage['content'] ?? '');
        $copyright = $passage['copyright'] ?? '';
        $humanRef  = $passage['reference'] ?? $reference;

        // Fire FUMS server-side tracking (mandatory per ToS)
        $fumsId = $data['meta']['fumsId'] ?? '';

        if (!empty($fumsId)) {
            $this->fireFums($fumsId);
        }

        if (empty($text)) {
            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        // Cache the result
        $this->writeCache('api_bible', $translation, $reference, $text, $copyright);

        return new BiblePassageResult(
            text: $text,
            reference: $humanRef,
            translation: $translation,
            copyright: $copyright,
            isHtml: false
        );
    }

    /**
     * @inheritDoc
     */
    public function getAvailableTranslations(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['abbreviation', 'name', 'language']))
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('source') . ' = ' . $db->quote('api_bible'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadAssocList() ?: [];
    }

    /**
     * @inheritDoc
     */
    public function returnsText(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isOfflineCapable(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'api_bible';
    }

    /**
     * Get the OSIS book codes mapping.
     *
     * @return  array<int, string>
     *
     * @since  10.1.0
     */
    public static function getOsisCodes(): array
    {
        return self::OSIS_CODES;
    }

    /**
     * Look up the api.bible Bible ID for a translation abbreviation.
     *
     * @param   string  $abbreviation  Translation abbreviation (e.g. "niv")
     *
     * @return  string|null  The provider_id or null if not found
     *
     * @since  10.1.0
     */
    private function getBibleId(string $abbreviation): ?string
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('provider_id'))
                ->from($db->quoteName('#__bsms_bible_translations'))
                ->where($db->quoteName('abbreviation') . ' = :abbr')
                ->where($db->quoteName('source') . ' = ' . $db->quote('api_bible'))
                ->bind(':abbr', $abbreviation);
            $db->setQuery($query);

            return $db->loadResult() ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Build an OSIS-format passage ID from a Proclaim-style reference.
     *
     * Converts "John+3:16-18" to "JHN.3.16-JHN.3.18"
     * Converts "Genesis+1:1" to "GEN.1.1"
     * Converts "Luke+7:36-7:38" to "LUK.7.36-LUK.7.38"
     * Converts "Isaiah+52:13-53:12" to "ISA.52.13-ISA.53.12"
     *
     * @param   string  $reference  Reference string like "John+3:16-18" or "Luke+7:36-7:38"
     *
     * @return  string  OSIS passage ID or empty string on failure
     *
     * @since  10.1.0
     */
    public function buildPassageId(string $reference): string
    {
        // Normalize: replace + with space
        $ref = str_replace('+', ' ', trim($reference));

        // Match "Book Chapter:Verse[-[EndChapter:]EndVerse]"
        if (!preg_match('/^(.+?)\s+(\d+):(\d+)(?:\s*-\s*(?:(\d+):)?(\d+))?$/i', $ref, $m)) {
            return '';
        }

        $bookName    = trim($m[1]);
        $chapter     = (int) $m[2];
        $verseStart  = (int) $m[3];
        $chapterEnd  = !empty($m[4]) ? (int) $m[4] : $chapter;
        $verseEnd    = !empty($m[5]) ? (int) $m[5] : null;

        // Find the OSIS code by matching book name
        $osisCode = $this->resolveBookToOsis($bookName);

        if (empty($osisCode)) {
            return '';
        }

        $passageId = $osisCode . '.' . $chapter . '.' . $verseStart;

        if ($verseEnd !== null && ($chapterEnd > $chapter || $verseEnd > $verseStart)) {
            $passageId .= '-' . $osisCode . '.' . $chapterEnd . '.' . $verseEnd;
        }

        return $passageId;
    }

    /**
     * Resolve a book name to its OSIS code.
     *
     * @param   string  $bookName  Book name (e.g. "John", "1 Corinthians", "Genesis")
     *
     * @return  string  OSIS code or empty string
     *
     * @since  10.1.0
     */
    private function resolveBookToOsis(string $bookName): string
    {
        $normalized = strtolower(trim($bookName));

        foreach (self::BOOK_NAMES as $num => $name) {
            if (strtolower($name) === $normalized) {
                return self::OSIS_CODES[$num] ?? '';
            }
        }

        return '';
    }

    /**
     * Perform an HTTP GET with the api-key header.
     *
     * @param   string  $url      URL to fetch
     * @param   int     $timeout  Timeout in seconds
     *
     * @return  string|null  Response body or null on failure
     *
     * @since  10.1.0
     */
    private function httpGetWithApiKey(string $url, int $timeout = 15): ?string
    {
        try {
            $factory  = new HttpFactory();
            $http     = $factory->getHttp();
            $response = $http->get($url, ['api-key' => $this->apiKey], $timeout);

            if ($response->code === 200) {
                return $response->body;
            }

            Log::add('ApiBible: HTTP ' . $response->code . ' from ' . strtok($url, '?'), Log::ERROR, 'com_proclaim.bible');
        } catch (\Exception $e) {
            Log::add('ApiBible: HTTP error: ' . $e->getMessage(), Log::ERROR, 'com_proclaim.bible');
        }

        return null;
    }

    /**
     * Fire a FUMS tracking ping (mandatory per API.Bible ToS).
     *
     * Server-side only — no visitor data is exposed.
     *
     * @param   string  $fumsId  The FUMS token from the API response
     *
     * @return  void
     *
     * @since  10.1.0
     */
    private function fireFums(string $fumsId): void
    {
        try {
            $url = self::FUMS_BASE . '?t=' . urlencode($fumsId) . '&dId=proclaim&sId=server';
            $this->httpGet($url, 5);
        } catch (\Throwable $e) {
            // Non-fatal — best effort
        }
    }
}

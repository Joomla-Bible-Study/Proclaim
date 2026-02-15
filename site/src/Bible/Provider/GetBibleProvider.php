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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * GetBible.net API provider.
 *
 * Calls the GetBible.net v2 API to retrieve scripture passages.
 * Results are cached in #__bsms_scripture_cache.
 *
 * API endpoint: https://query.getbible.net/v2/{translation}/{reference}
 *
 * @since  10.1.0
 */
class GetBibleProvider extends AbstractBibleProvider
{
    /**
     * API base URL.
     *
     * @var  string
     * @since  10.1.0
     */
    private const API_BASE = 'https://query.getbible.net/v2/';

    /**
     * @inheritDoc
     */
    public function getPassage(string $reference, string $translation): BiblePassageResult
    {
        // Check cache first
        $cached = $this->readCache('getbible', $translation, $reference);

        if ($cached) {
            return $cached;
        }

        // Normalize reference: replace + with spaces for the API
        $apiRef  = str_replace('+', ' ', $reference);
        $url     = self::API_BASE . urlencode($translation) . '/' . urlencode($apiRef);
        $body    = $this->httpGet($url, 15);

        if ($body === null) {
            Log::add('GetBible: API returned no data for "' . $apiRef . '" (' . $translation . ')', Log::WARNING, 'com_proclaim.bible');

            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        // Guard: gatekeeper may slip through with 200 + HTML body
        if (self::isHtmlResponse($body)) {
            Log::add('GetBible: HTML gatekeeper response for "' . $apiRef . '" (' . $translation . ')', Log::WARNING, 'com_proclaim.bible');

            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        $data = json_decode($body, true);

        if (!\is_array($data) || empty($data)) {
            Log::add('GetBible: Invalid JSON response for "' . $apiRef . '" (' . $translation . ')', Log::ERROR, 'com_proclaim.bible');

            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        // The API response has a dynamic root key (the reference).
        // It can be a single passage or multiple. Each has a "verses" array.
        $text      = '';
        $copyright = '';

        foreach ($data as $passage) {
            if (!\is_array($passage) || !isset($passage['verses'])) {
                continue;
            }

            if (!empty($passage['name'])) {
                if ($text !== '') {
                    $text .= ' ';
                }
            }

            foreach ($passage['verses'] as $verse) {
                $verseNum  = $verse['verse'] ?? '';
                $verseText = $verse['text'] ?? '';
                $text .= '<sup>' . htmlspecialchars((string) $verseNum) . '</sup>'
                    . htmlspecialchars(trim($verseText)) . ' ';
            }

            if (empty($copyright) && !empty($passage['translation_note'])) {
                $copyright = $passage['translation_note'];
            }
        }

        $text = trim($text);

        if ($text === '') {
            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        // Cache the result
        $this->writeCache('getbible', $translation, $reference, $text, $copyright);

        return new BiblePassageResult(
            text: $text,
            reference: $reference,
            translation: $translation,
            copyright: $copyright,
            isHtml: true
        );
    }

    /**
     * @inheritDoc
     */
    public function getAvailableTranslations(): array
    {
        // Return translations from our database that have getbible as source
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['abbreviation', 'name', 'language']))
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('source') . ' = ' . $db->quote('getbible'))
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
        return 'getbible';
    }
}

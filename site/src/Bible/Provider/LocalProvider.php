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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Local Bible provider.
 *
 * Reads verse text from the #__bsms_bible_verses table.
 * Fastest provider, fully offline-capable.
 *
 * @since  10.1.0
 */
class LocalProvider extends AbstractBibleProvider
{
    /**
     * @inheritDoc
     */
    public function getPassage(string $reference, string $translation): BiblePassageResult
    {
        $parsed = $this->parseReference($reference);

        if (!$parsed) {
            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['book', 'chapter', 'verse', 'text']))
            ->from($db->quoteName('#__bsms_bible_verses'))
            ->where($db->quoteName('translation') . ' = :translation')
            ->where($db->quoteName('book') . ' = :book')
            ->bind(':translation', $translation)
            ->bind(':book', $parsed['book'], \Joomla\Database\ParameterType::INTEGER);

        // Single chapter range
        if ($parsed['chapter_end'] === $parsed['chapter_begin']) {
            $query->where($db->quoteName('chapter') . ' = :chapter')
                ->bind(':chapter', $parsed['chapter_begin'], \Joomla\Database\ParameterType::INTEGER);

            if ($parsed['verse_begin'] > 0) {
                $query->where($db->quoteName('verse') . ' >= :vbegin')
                    ->bind(':vbegin', $parsed['verse_begin'], \Joomla\Database\ParameterType::INTEGER);
            }

            if ($parsed['verse_end'] > 0) {
                $query->where($db->quoteName('verse') . ' <= :vend')
                    ->bind(':vend', $parsed['verse_end'], \Joomla\Database\ParameterType::INTEGER);
            }
        } else {
            // Multi-chapter range
            $query->where('(' .
                '(' . $db->quoteName('chapter') . ' = :cbegin AND ' . $db->quoteName('verse') . ' >= :vbegin2)' .
                ' OR ' .
                '(' . $db->quoteName('chapter') . ' > :cbegin2 AND ' . $db->quoteName('chapter') . ' < :cend)' .
                ' OR ' .
                '(' . $db->quoteName('chapter') . ' = :cend2 AND ' . $db->quoteName('verse') . ' <= :vend2)' .
                ')')
                ->bind(':cbegin', $parsed['chapter_begin'], \Joomla\Database\ParameterType::INTEGER)
                ->bind(':vbegin2', $parsed['verse_begin'] ?: 1, \Joomla\Database\ParameterType::INTEGER)
                ->bind(':cbegin2', $parsed['chapter_begin'], \Joomla\Database\ParameterType::INTEGER)
                ->bind(':cend', $parsed['chapter_end'], \Joomla\Database\ParameterType::INTEGER)
                ->bind(':cend2', $parsed['chapter_end'], \Joomla\Database\ParameterType::INTEGER)
                ->bind(':vend2', $parsed['verse_end'] ?: 999, \Joomla\Database\ParameterType::INTEGER);
        }

        $query->order($db->quoteName('chapter') . ' ASC, ' . $db->quoteName('verse') . ' ASC');

        $db->setQuery($query);
        $verses = $db->loadObjectList();

        if (empty($verses)) {
            return new BiblePassageResult(
                reference: $reference,
                translation: $translation
            );
        }

        // Build verse text with superscript verse numbers
        $text          = '';
        $currentChapter = 0;

        foreach ($verses as $verse) {
            if ((int) $verse->chapter !== $currentChapter) {
                $currentChapter = (int) $verse->chapter;

                if ($text !== '') {
                    $text .= ' ';
                }
            }

            $text .= '<sup>' . $verse->verse . '</sup>' . $verse->text . ' ';
        }

        // Get copyright from translation record
        $copyright = $this->getTranslationCopyright($translation);

        return new BiblePassageResult(
            text: trim($text),
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
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['abbreviation', 'name', 'language']))
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('installed') . ' = 1')
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
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'local';
    }

    /**
     * Parse a BibleGateway-style reference into components.
     *
     * Handles formats like: "John+3:16", "Genesis+1:1-2:5", "Psalms+23"
     *
     * @param   string  $reference  The reference string with + for spaces
     *
     * @return  array|null  Parsed components or null on failure
     *
     * @since  10.1.0
     */
    protected function parseReference(string $reference): ?array
    {
        // Replace + with spaces for parsing
        $ref = str_replace('+', ' ', $reference);

        // Match: "Book Chapter:Verse-Chapter:Verse" or "Book Chapter:Verse-Verse" or "Book Chapter:Verse" or "Book Chapter"
        if (!preg_match('/^(.+?)\s+(\d+)(?::(\d+))?(?:-(\d+)(?::(\d+))?)?$/', $ref, $matches)) {
            return null;
        }

        $bookName     = trim($matches[1]);
        $chapterBegin = (int) $matches[2];
        $verseBegin   = isset($matches[3]) && $matches[3] !== '' ? (int) $matches[3] : 0;

        // Determine end chapter/verse
        if (isset($matches[5]) && $matches[5] !== '') {
            // "Book Ch:V-Ch:V" format
            $chapterEnd = (int) $matches[4];
            $verseEnd   = (int) $matches[5];
        } elseif (isset($matches[4]) && $matches[4] !== '') {
            // "Book Ch:V-V" format (same chapter)
            $chapterEnd = $chapterBegin;
            $verseEnd   = (int) $matches[4];
        } else {
            $chapterEnd = $chapterBegin;
            $verseEnd   = $verseBegin;
        }

        // Resolve book name to standard number
        $bookNumber = $this->resolveBookNumber($bookName);

        if ($bookNumber === 0) {
            return null;
        }

        return [
            'book'          => $bookNumber,
            'chapter_begin' => $chapterBegin,
            'verse_begin'   => $verseBegin,
            'chapter_end'   => $chapterEnd,
            'verse_end'     => $verseEnd,
        ];
    }

    /**
     * Resolve a book name to its standard number (1-66).
     *
     * @param   string  $name  Book name (case-insensitive)
     *
     * @return  int  Standard book number or 0 if not found
     *
     * @since  10.1.0
     */
    protected function resolveBookNumber(string $name): int
    {
        $normalized = strtolower(trim($name));

        foreach (self::BOOK_NAMES as $number => $bookName) {
            if (strtolower($bookName) === $normalized) {
                return $number;
            }
        }

        // Try common abbreviations
        $abbreviations = [
            'gen' => 1, 'ex' => 2, 'lev' => 3, 'num' => 4, 'deut' => 5,
            'josh' => 6, 'judg' => 7, '1 sam' => 9, '2 sam' => 10,
            '1 kgs' => 11, '2 kgs' => 12, '1 chr' => 13, '2 chr' => 14,
            'neh' => 16, 'est' => 17, 'ps' => 19, 'prov' => 20,
            'eccl' => 21, 'song' => 22, 'isa' => 23, 'jer' => 24,
            'lam' => 25, 'ezek' => 26, 'dan' => 27, 'hos' => 28,
            'hab' => 35, 'zeph' => 36, 'hag' => 37, 'zech' => 38,
            'mal' => 39, 'matt' => 40, 'mk' => 41, 'lk' => 42,
            'jn' => 43, 'rom' => 45, '1 cor' => 46, '2 cor' => 47,
            'gal' => 48, 'eph' => 49, 'phil' => 50, 'col' => 51,
            '1 thess' => 52, '2 thess' => 53, '1 tim' => 54, '2 tim' => 55,
            'tit' => 56, 'phlm' => 57, 'heb' => 58, 'jas' => 59,
            '1 pet' => 60, '2 pet' => 61, '1 jn' => 62, '2 jn' => 63,
            '3 jn' => 64, 'rev' => 66,
        ];

        return $abbreviations[$normalized] ?? 0;
    }

    /**
     * Get copyright text for a translation.
     *
     * @param   string  $abbreviation  Translation abbreviation
     *
     * @return  string
     *
     * @since  10.1.0
     */
    private function getTranslationCopyright(string $abbreviation): string
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('copyright'))
            ->from($db->quoteName('#__bsms_bible_translations'))
            ->where($db->quoteName('abbreviation') . ' = :abbr')
            ->bind(':abbr', $abbreviation);

        $db->setQuery($query);

        return (string) $db->loadResult();
    }
}

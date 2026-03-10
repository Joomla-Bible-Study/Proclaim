<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Value object representing a single Bible scripture reference.
 *
 * @since  10.1.0
 */
class ScriptureReference
{
    /**
     * @param   int     $booknumber    Book number (101-173)
     * @param   int     $chapterBegin  Starting chapter
     * @param   int     $verseBegin    Starting verse (0 = chapter-only)
     * @param   int     $chapterEnd    Ending chapter
     * @param   int     $verseEnd      Ending verse (0 = chapter-only)
     * @param   string  $bibleVersion  Bible translation abbreviation
     * @param   string  $referenceText Human-readable reference (e.g. "Luke 7:36-38")
     * @param   int     $ordering      Sort order within a study
     *
     * @since  10.1.0
     */
    public function __construct(
        public int $booknumber = 0,
        public int $chapterBegin = 0,
        public int $verseBegin = 0,
        public int $chapterEnd = 0,
        public int $verseEnd = 0,
        public string $bibleVersion = '',
        public string $referenceText = '',
        public int $ordering = 0,
    ) {
    }

    /**
     * Convert to an associative array suitable for database insertion.
     *
     * @return  array
     *
     * @since  10.1.0
     */
    public function toArray(): array
    {
        return [
            'booknumber'     => $this->booknumber,
            'chapter_begin'  => $this->chapterBegin,
            'verse_begin'    => $this->verseBegin,
            'chapter_end'    => $this->chapterEnd,
            'verse_end'      => $this->verseEnd,
            'bible_version'  => $this->bibleVersion,
            'reference_text' => $this->referenceText,
            'ordering'       => $this->ordering,
        ];
    }

    /**
     * Create from a database row object.
     *
     * @param   object  $row  Database row
     *
     * @return  self
     *
     * @since  10.1.0
     */
    public static function fromRow(object $row): self
    {
        return new self(
            booknumber:    (int) ($row->booknumber ?? 0),
            chapterBegin:  (int) ($row->chapter_begin ?? 0),
            verseBegin:    (int) ($row->verse_begin ?? 0),
            chapterEnd:    (int) ($row->chapter_end ?? 0),
            verseEnd:      (int) ($row->verse_end ?? 0),
            bibleVersion:  (string) ($row->bible_version ?? ''),
            referenceText: (string) ($row->reference_text ?? ''),
            ordering:      (int) ($row->ordering ?? 0),
        );
    }
}

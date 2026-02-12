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
 * Bible book structure data — chapter and verse counts for all 73 books.
 *
 * Verse counts follow KJV numbering. Minor variations exist between translations
 * (e.g., some Psalms, 3 John) but KJV is the standard reference numbering.
 *
 * Booknumbers use the Proclaim offset system: 101-166 canonical, 167-173 deuterocanonical.
 *
 * @since  10.1.0
 */
class BibleStructure
{
    /**
     * Verse counts per chapter, keyed by booknumber.
     * Each value is an array where index 0 = chapter 1's verse count, etc.
     *
     * @var array<int, int[]>
     * @since 10.1.0
     */
    private const VERSE_COUNTS = [
        // ── Old Testament ──────────────────────────────────────────────
        // Genesis
        101 => [31,25,24,26,32,22,24,22,29,32,32,20,18,24,21,16,27,25,6,8,
                35,36,28,13,31,44,23,55,46,34,31,55,32,20,31,29,43,36,30,23,
                23,57,38,34,34,28,34,31,22,33,26],
        // Exodus
        102 => [22,25,22,31,23,30,25,32,35,29,10,51,22,31,27,36,16,27,25,26,
                36,31,33,18,40,37,21,43,46,38,18,35,23,35,35,38,29,31,43,38],
        // Leviticus
        103 => [17,16,17,35,19,30,38,36,24,20,47,8,59,57,33,34,16,30,37,27,
                24,33,44,23,55,46,34],
        // Numbers
        104 => [54,34,51,49,31,27,89,26,23,36,35,16,33,45,41,50,13,32,22,29,
                35,41,30,25,18,65,23,31,40,16,54,42,56,29,34,13],
        // Deuteronomy
        105 => [46,37,29,49,33,25,26,20,29,22,32,32,18,29,23,22,20,22,21,20,
                23,30,25,22,19,19,26,68,29,20,30,52,29,12],
        // Joshua
        106 => [18,24,17,24,15,27,26,35,27,43,23,24,33,15,63,10,18,28,51,9,
                45,34,16,33],
        // Judges
        107 => [36,23,31,24,31,40,25,35,57,18,40,15,25,20,20,31,13,31,30,48,
                25],
        // Ruth
        108 => [22,23,18,22],
        // 1 Samuel
        109 => [28,36,21,22,12,21,17,22,27,27,15,25,23,52,35,23,58,30,24,42,
                15,23,29,22,44,25,12,25,11,31,13],
        // 2 Samuel
        110 => [27,32,39,12,25,23,29,18,13,19,27,31,39,33,37,23,29,33,43,26,
                22,51,39,25],
        // 1 Kings
        111 => [53,46,28,34,18,38,51,66,28,29,43,33,34,31,34,34,24,46,21,43,
                29,53],
        // 2 Kings
        112 => [18,25,27,44,27,33,20,29,37,36,21,21,25,29,38,20,41,37,37,21,
                26,20,37,20,30],
        // 1 Chronicles
        113 => [54,55,24,43,26,81,40,40,44,14,47,40,14,17,29,43,27,17,19,8,
                30,19,32,31,31,32,34,21,30],
        // 2 Chronicles
        114 => [17,18,17,22,14,42,22,18,31,19,23,16,22,15,19,14,19,34,11,37,
                20,12,21,27,28,23,9,27,36,27,21,33,25,33,27,23],
        // Ezra
        115 => [11,70,13,24,17,22,28,36,15,44],
        // Nehemiah
        116 => [11,20,32,23,19,19,73,18,38,39,36,47,31],
        // Esther
        117 => [22,23,15,17,14,14,10,17,32,3],
        // Job
        118 => [22,13,26,21,27,30,21,22,35,22,20,25,28,22,35,22,16,21,29,29,
                34,30,17,25,6,14,23,28,25,31,40,22,33,37,16,33,24,41,30,24,
                34,17],
        // Psalms
        119 => [6,12,8,8,12,10,17,9,20,18,7,8,6,7,5,11,15,50,14,9,
                13,31,6,10,22,12,14,9,11,12,24,11,22,22,28,12,40,22,13,17,
                13,11,5,26,17,11,9,14,20,23,19,9,6,7,23,13,11,11,17,12,
                8,12,11,10,13,20,7,35,36,5,24,20,28,23,10,12,20,72,13,19,
                16,8,18,12,13,17,7,18,52,17,16,15,5,23,11,13,12,9,9,5,
                8,28,22,35,45,48,43,13,31,7,10,10,9,8,18,19,2,29,176,7,
                8,9,4,8,5,6,5,6,8,8,3,18,3,3,21,26,9,8,24,13,
                10,7,12,15,21,10,20,14,9,6],
        // Proverbs
        120 => [33,22,35,27,23,35,27,36,18,32,31,28,25,35,33,33,28,24,29,30,
                31,29,35,34,28,28,27,28,27,33,31],
        // Ecclesiastes
        121 => [18,26,22,16,20,12,29,17,18,20,10,14],
        // Song of Solomon
        122 => [17,17,11,16,16,13,13,14],
        // Isaiah
        123 => [31,22,26,6,30,13,25,22,21,34,16,6,22,32,9,14,14,7,25,6,
                17,25,18,23,12,21,13,29,24,33,9,20,24,17,10,22,38,22,8,31,
                29,25,28,28,25,13,15,22,26,11,23,15,12,17,13,12,21,14,21,22,
                11,12,19,12,25,24],
        // Jeremiah
        124 => [19,37,25,31,31,30,34,22,26,25,23,17,27,22,21,21,27,23,15,18,
                14,30,40,10,38,24,22,17,32,24,40,44,26,22,19,32,21,28,18,16,
                18,22,13,30,5,28,7,47,39,46,64,34],
        // Lamentations
        125 => [22,22,66,22,22],
        // Ezekiel
        126 => [28,10,27,17,17,14,27,18,11,22,25,28,23,23,8,63,24,32,14,49,
                32,31,49,27,17,21,36,26,21,26,18,32,33,31,15,38,28,23,29,49,
                26,20,27,31,25,24,23,35],
        // Daniel
        127 => [21,49,30,37,31,28,28,27,27,21,45,13],
        // Hosea
        128 => [11,23,5,19,15,11,16,14,17,15,12,14,16,9],
        // Joel
        129 => [20,32,21],
        // Amos
        130 => [15,16,15,13,27,14,17,14,15],
        // Obadiah
        131 => [21],
        // Jonah
        132 => [17,10,10,11],
        // Micah
        133 => [16,13,12,13,15,16,20],
        // Nahum
        134 => [15,13,19],
        // Habakkuk
        135 => [17,20,19],
        // Zephaniah
        136 => [18,15,20],
        // Haggai
        137 => [15,23],
        // Zechariah
        138 => [21,13,10,14,11,15,14,23,17,12,17,14,9,21],
        // Malachi
        139 => [14,17,18,6],

        // ── New Testament ──────────────────────────────────────────────
        // Matthew
        140 => [25,23,17,25,48,34,29,34,38,42,30,50,58,36,39,28,27,35,30,34,
                46,46,39,51,46,75,66,20],
        // Mark
        141 => [45,28,35,41,43,56,37,38,50,52,33,44,37,72,47,20],
        // Luke
        142 => [80,52,38,44,39,49,50,56,62,42,54,59,35,35,32,31,37,43,48,47,
                38,71,56,53],
        // John
        143 => [51,25,36,54,47,71,53,59,41,42,57,50,38,31,27,33,26,40,42,31,
                25],
        // Acts
        144 => [26,47,26,37,42,15,60,40,43,48,30,25,52,28,41,40,34,28,41,38,
                40,30,35,27,27,32,44,31],
        // Romans
        145 => [32,29,31,25,21,23,25,39,33,21,36,21,14,23,33,27],
        // 1 Corinthians
        146 => [31,16,23,21,13,20,40,13,27,33,34,31,13,40,58,24],
        // 2 Corinthians
        147 => [24,17,18,18,21,18,16,24,15,18,33,21,14],
        // Galatians
        148 => [24,21,29,31,26,18],
        // Ephesians
        149 => [23,22,21,32,33,24],
        // Philippians
        150 => [30,30,21,23],
        // Colossians
        151 => [29,23,25,18],
        // 1 Thessalonians
        152 => [10,20,13,18,28],
        // 2 Thessalonians
        153 => [12,17,18],
        // 1 Timothy
        154 => [20,15,16,16,25,21],
        // 2 Timothy
        155 => [18,26,17,22],
        // Titus
        156 => [16,15,15],
        // Philemon
        157 => [25],
        // Hebrews
        158 => [14,18,19,16,14,20,28,13,28,39,40,29,25],
        // James
        159 => [27,26,18,17,20],
        // 1 Peter
        160 => [25,25,22,19,14],
        // 2 Peter
        161 => [21,22,18],
        // 1 John
        162 => [10,29,24,21,21],
        // 2 John
        163 => [13],
        // 3 John
        164 => [14],
        // Jude
        165 => [25],
        // Revelation
        166 => [20,29,22,11,14,17,17,13,21,11,19,17,18,20,8,21,18,24,21,15,
                27,21],

        // ── Deuterocanonical ───────────────────────────────────────────
        // Tobit
        167 => [22,14,17,21,22,18,16,21,6,14,19,22,18,15],
        // Judith
        168 => [16,28,10,15,24,21,32,36,14,23,23,20,20,19,14,25],
        // 1 Maccabees
        169 => [64,70,60,61,68,63,50,32,73,89,74,53,53,49,41,24],
        // 2 Maccabees
        170 => [36,32,40,50,27,31,42,36,29,38,38,45,26,46,39],
        // Wisdom
        171 => [16,24,19,20,23,25,30,21,18,21,26,27,19,31,19,29,21,25,22],
        // Sirach
        172 => [30,18,31,31,15,37,36,19,18,31,34,18,26,27,20,30,32,33,30,31,
                28,27,27,34,26,29,30,26,28,25,31,24,33,33,23,26,20,25,25,16,
                29,30,13,27,27,28,39,19,16,29,30],
        // Baruch
        173 => [22,35,38,37,9,73],
    ];

    /**
     * Get the number of chapters in a book.
     *
     * @param   int  $booknumber  Book number (101-173)
     *
     * @return  int  Chapter count, or 0 if book not found
     *
     * @since  10.1.0
     */
    public static function getChapterCount(int $booknumber): int
    {
        if (!isset(self::VERSE_COUNTS[$booknumber])) {
            return 0;
        }

        return \count(self::VERSE_COUNTS[$booknumber]);
    }

    /**
     * Get the number of verses in a specific chapter.
     *
     * @param   int  $booknumber  Book number (101-173)
     * @param   int  $chapter     Chapter number (1-based)
     *
     * @return  int  Verse count, or 0 if not found
     *
     * @since  10.1.0
     */
    public static function getVerseCount(int $booknumber, int $chapter): int
    {
        if (!isset(self::VERSE_COUNTS[$booknumber])) {
            return 0;
        }

        $chapters = self::VERSE_COUNTS[$booknumber];
        $index    = $chapter - 1;

        if ($index < 0 || $index >= \count($chapters)) {
            return 0;
        }

        return $chapters[$index];
    }

    /**
     * Validate that a chapter number is valid for a book.
     *
     * @param   int  $booknumber  Book number (101-173)
     * @param   int  $chapter     Chapter number (1-based)
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    public static function isValidChapter(int $booknumber, int $chapter): bool
    {
        $max = self::getChapterCount($booknumber);

        return $max > 0 && $chapter >= 1 && $chapter <= $max;
    }

    /**
     * Validate that a verse number is valid for a book chapter.
     *
     * @param   int  $booknumber  Book number (101-173)
     * @param   int  $chapter     Chapter number (1-based)
     * @param   int  $verse       Verse number (1-based)
     *
     * @return  bool
     *
     * @since  10.1.0
     */
    public static function isValidVerse(int $booknumber, int $chapter, int $verse): bool
    {
        $max = self::getVerseCount($booknumber, $chapter);

        return $max > 0 && $verse >= 1 && $verse <= $max;
    }

    /**
     * Get the Bible structure data formatted for JavaScript consumption.
     *
     * Returns a compact object: { booknumber: [verseCount1, verseCount2, ...], ... }
     * where the array index represents (chapter - 1).
     *
     * @return  array<int, int[]>
     *
     * @since  10.1.0
     */
    public static function getStructureForJs(): array
    {
        return self::VERSE_COUNTS;
    }
}

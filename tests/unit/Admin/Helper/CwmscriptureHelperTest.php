<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmscriptureHelper;
use CWM\Library\Scripture\Helper\ScriptureReference;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Tests for the CwmscriptureHelper class.
 *
 * @since  10.1.0
 */
class CwmscriptureHelperTest extends ProclaimTestCase
{
    /**
     * Test parsing a simple single-verse reference.
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseSimpleReference(): void
    {
        $ref = CwmscriptureHelper::parseReference('Genesis 1:1');

        $this->assertNotNull($ref);
        $this->assertSame(101, $ref->booknumber);
        $this->assertSame(1, $ref->chapterBegin);
        $this->assertSame(1, $ref->verseBegin);
        $this->assertSame(1, $ref->chapterEnd);
        $this->assertSame(1, $ref->verseEnd);
    }

    /**
     * Test parsing a verse range within the same chapter.
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseVerseRange(): void
    {
        $ref = CwmscriptureHelper::parseReference('Luke 7:36-38');

        $this->assertNotNull($ref);
        $this->assertSame(142, $ref->booknumber);
        $this->assertSame(7, $ref->chapterBegin);
        $this->assertSame(36, $ref->verseBegin);
        $this->assertSame(7, $ref->chapterEnd);
        $this->assertSame(38, $ref->verseEnd);
    }

    /**
     * Test parsing a cross-chapter range.
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseCrossChapterRange(): void
    {
        $ref = CwmscriptureHelper::parseReference('Luke 1:20-2:5');

        $this->assertNotNull($ref);
        $this->assertSame(142, $ref->booknumber);
        $this->assertSame(1, $ref->chapterBegin);
        $this->assertSame(20, $ref->verseBegin);
        $this->assertSame(2, $ref->chapterEnd);
        $this->assertSame(5, $ref->verseEnd);
    }

    /**
     * Test parsing a chapter-only reference (e.g., Psalm 23).
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseChapterOnly(): void
    {
        $ref = CwmscriptureHelper::parseReference('Psalm 23');

        $this->assertNotNull($ref);
        $this->assertSame(119, $ref->booknumber);
        $this->assertSame(23, $ref->chapterBegin);
        $this->assertSame(0, $ref->verseBegin);
        $this->assertSame(23, $ref->chapterEnd);
        $this->assertSame(0, $ref->verseEnd);
    }

    /**
     * Test parsing with abbreviations.
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseAbbreviation(): void
    {
        $ref = CwmscriptureHelper::parseReference('Gen 1:1');

        $this->assertNotNull($ref);
        $this->assertSame(101, $ref->booknumber);
    }

    /**
     * Test parsing a numbered book.
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseNumberedBook(): void
    {
        $ref = CwmscriptureHelper::parseReference('1 Cor 13:4-7');

        $this->assertNotNull($ref);
        $this->assertSame(146, $ref->booknumber);
        $this->assertSame(13, $ref->chapterBegin);
        $this->assertSame(4, $ref->verseBegin);
        $this->assertSame(13, $ref->chapterEnd);
        $this->assertSame(7, $ref->verseEnd);
    }

    /**
     * Test parsing Revelation with cross-chapter range.
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseLongCrossChapter(): void
    {
        $ref = CwmscriptureHelper::parseReference('Rev 21:1-22:5');

        $this->assertNotNull($ref);
        $this->assertSame(166, $ref->booknumber);
        $this->assertSame(21, $ref->chapterBegin);
        $this->assertSame(1, $ref->verseBegin);
        $this->assertSame(22, $ref->chapterEnd);
        $this->assertSame(5, $ref->verseEnd);
    }

    /**
     * Test that invalid input returns null.
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseInvalidInput(): void
    {
        $this->assertNull(CwmscriptureHelper::parseReference('NotABook 1:1'));
        $this->assertNull(CwmscriptureHelper::parseReference(''));
        $this->assertNull(CwmscriptureHelper::parseReference('just some text'));
    }

    /**
     * Test formatting a simple single-verse reference.
     *
     * @return void
     * @since  10.1.0
     */
    public function testFormatSingleVerse(): void
    {
        // In test context, Text::_() returns the language key as-is
        $this->assertSame('JBS_BBK_GENESIS 1:1', CwmscriptureHelper::formatReference(101, 1, 1, 1, 1));
    }

    /**
     * Test formatting a verse range.
     *
     * @return void
     * @since  10.1.0
     */
    public function testFormatVerseRange(): void
    {
        // In test context, Text::_() returns the language key as-is
        $this->assertSame('JBS_BBK_LUKE 7:36-38', CwmscriptureHelper::formatReference(142, 7, 36, 7, 38));
    }

    /**
     * Test formatting a cross-chapter range.
     *
     * @return void
     * @since  10.1.0
     */
    public function testFormatCrossChapterRange(): void
    {
        // In test context, Text::_() returns the language key as-is
        $this->assertSame('JBS_BBK_LUKE 1:20-2:5', CwmscriptureHelper::formatReference(142, 1, 20, 2, 5));
    }

    /**
     * Test formatting chapter-only.
     *
     * @return void
     * @since  10.1.0
     */
    public function testFormatChapterOnly(): void
    {
        // In test context, Text::_() returns the language key as-is
        $this->assertSame('JBS_BBK_PSALM 23', CwmscriptureHelper::formatReference(119, 23, 0, 23, 0));
    }

    /**
     * Test round-trip: parse then format produces equivalent string.
     *
     * @return void
     * @since  10.1.0
     */
    public function testRoundTrip(): void
    {
        // In test context Text::_() returns language keys, so format produces
        // "JBS_BBK_LUKE 7:36-38" instead of "Luke 7:36-38". We verify structural
        // round-trip: parse → format → parse again → same booknumber/chapter/verse.
        $inputs = [
            'Genesis 1:1',
            'Luke 7:36-38',
            'Luke 1:20-2:5',
            'Psalm 23',
            'Revelation 21:1-22:5',
        ];

        foreach ($inputs as $input) {
            $ref = CwmscriptureHelper::parseReference($input);
            $this->assertNotNull($ref, 'Failed to parse: ' . $input);

            $formatted = CwmscriptureHelper::formatReference(
                $ref->booknumber,
                $ref->chapterBegin,
                $ref->verseBegin,
                $ref->chapterEnd,
                $ref->verseEnd
            );

            // Parse the formatted string back (uses translated book map)
            $ref2 = CwmscriptureHelper::parseReference($formatted);
            $this->assertNotNull($ref2, 'Failed to re-parse formatted: ' . $formatted);
            $this->assertSame($ref->booknumber, $ref2->booknumber, 'Booknumber mismatch for: ' . $input);
            $this->assertSame($ref->chapterBegin, $ref2->chapterBegin, 'ChapterBegin mismatch for: ' . $input);
            $this->assertSame($ref->verseBegin, $ref2->verseBegin, 'VerseBegin mismatch for: ' . $input);
            $this->assertSame($ref->chapterEnd, $ref2->chapterEnd, 'ChapterEnd mismatch for: ' . $input);
            $this->assertSame($ref->verseEnd, $ref2->verseEnd, 'VerseEnd mismatch for: ' . $input);
        }
    }

    /**
     * Test getBookNumber with various inputs.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetBookNumber(): void
    {
        $this->assertSame(101, CwmscriptureHelper::getBookNumber('Gen'));
        $this->assertSame(101, CwmscriptureHelper::getBookNumber('genesis'));
        $this->assertSame(142, CwmscriptureHelper::getBookNumber('Luke'));
        $this->assertSame(142, CwmscriptureHelper::getBookNumber('lk'));
        $this->assertSame(146, CwmscriptureHelper::getBookNumber('1 Cor'));
        $this->assertSame(166, CwmscriptureHelper::getBookNumber('Rev'));
        $this->assertSame(0, CwmscriptureHelper::getBookNumber('NotABook'));
    }

    /**
     * Test getBookName returns translated name.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetBookName(): void
    {
        // In test context, Text::_() returns the key as-is
        $name = CwmscriptureHelper::getBookName(101);
        $this->assertNotEmpty($name);

        $this->assertSame('', CwmscriptureHelper::getBookName(0));
        $this->assertSame('', CwmscriptureHelper::getBookName(999));
    }

    /**
     * Test getAllBooks returns full list.
     *
     * @return void
     * @since  10.1.0
     */
    public function testGetAllBooks(): void
    {
        $books = CwmscriptureHelper::getAllBooks();
        $this->assertCount(73, $books);

        $first = $books[0];
        $this->assertArrayHasKey('booknumber', $first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('key', $first);
        $this->assertSame(101, $first['booknumber']);
    }

    /**
     * Test ScriptureReference value object.
     *
     * @return void
     * @since  10.1.0
     */
    public function testScriptureReferenceToArray(): void
    {
        $ref = new ScriptureReference(
            booknumber: 142,
            chapterBegin: 7,
            verseBegin: 36,
            chapterEnd: 7,
            verseEnd: 38,
            bibleVersion: 'kjv',
            referenceText: 'Luke 7:36-38',
            ordering: 0,
        );

        $array = $ref->toArray();
        $this->assertSame(142, $array['booknumber']);
        $this->assertSame(7, $array['chapter_begin']);
        $this->assertSame(36, $array['verse_begin']);
        $this->assertSame('kjv', $array['bible_version']);
        $this->assertSame('Luke 7:36-38', $array['reference_text']);
    }

    /**
     * Test ScriptureReference fromRow factory method.
     *
     * @return void
     * @since  10.1.0
     */
    public function testScriptureReferenceFromRow(): void
    {
        $row = (object) [
            'booknumber'     => 101,
            'chapter_begin'  => 1,
            'verse_begin'    => 1,
            'chapter_end'    => 1,
            'verse_end'      => 1,
            'bible_version'  => 'esv',
            'reference_text' => 'Genesis 1:1',
            'ordering'       => 0,
        ];

        $ref = ScriptureReference::fromRow($row);
        $this->assertSame(101, $ref->booknumber);
        $this->assertSame('esv', $ref->bibleVersion);
        $this->assertSame('Genesis 1:1', $ref->referenceText);
    }

    /**
     * Test parsing various abbreviation formats.
     *
     * @return void
     * @since  10.1.0
     */
    public function testParseVariousAbbreviations(): void
    {
        // Matthew
        $ref = CwmscriptureHelper::parseReference('Matt 5:3-12');
        $this->assertNotNull($ref);
        $this->assertSame(140, $ref->booknumber);

        // Mark
        $ref = CwmscriptureHelper::parseReference('Mk 1:1');
        $this->assertNotNull($ref);
        $this->assertSame(141, $ref->booknumber);

        // 2 Timothy
        $ref = CwmscriptureHelper::parseReference('2 Tim 3:16-17');
        $this->assertNotNull($ref);
        $this->assertSame(155, $ref->booknumber);

        // Philippians
        $ref = CwmscriptureHelper::parseReference('Phil 4:13');
        $this->assertNotNull($ref);
        $this->assertSame(150, $ref->booknumber);
    }
}

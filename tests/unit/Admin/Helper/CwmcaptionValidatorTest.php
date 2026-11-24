<?php

/**
 * Unit tests for CwmcaptionValidator helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmcaptionValidator;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for CwmcaptionValidator
 *
 * Pure-logic tests: every behavior the controller delegates is exercised
 * here so we have real coverage of the rejection rules without needing
 * an HTTP / SAPI context for the upload itself.
 *
 * @since  10.3.0
 */
#[CoversClass(CwmcaptionValidator::class)]
class CwmcaptionValidatorTest extends ProclaimTestCase
{
    /**
     * The validator under test.
     *
     * @var    CwmcaptionValidator
     * @since  10.3.0
     */
    private CwmcaptionValidator $validator;

    /**
     * Create the validator.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CwmcaptionValidator();
    }

    /**
     * The allowed caption extensions are exactly vtt, srt and sbv, in that order.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testAllowedExtensionsConstantContainsVttSrtAndSbv(): void
    {
        $this->assertSame(['vtt', 'srt', 'sbv'], CwmcaptionValidator::ALLOWED_EXTENSIONS);
    }

    /**
     * The size limit is exactly 2 MB.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testMaxSizeIsTwoMegabytes(): void
    {
        $this->assertSame(2 * 1024 * 1024, CwmcaptionValidator::MAX_SIZE_BYTES);
    }

    /**
     * Extensions, each with whether a caption may have it.
     *
     * @return  array<string, array{0: string, 1: bool}>  Each case
     *
     * @since   10.3.0
     */
    public static function extensionProvider(): array
    {
        return [
            'lowercase vtt'     => ['vtt', true],
            'lowercase srt'     => ['srt', true],
            'lowercase sbv'     => ['sbv', true],
            'uppercase VTT'     => ['VTT', true],
            'uppercase SBV'     => ['SBV', true],
            'mixed-case Srt'    => ['Srt', true],
            'mp4 rejected'      => ['mp4', false],
            'php rejected'      => ['php', false],
            'empty rejected'    => ['', false],
            'with-dot rejected' => ['.vtt', false],
        ];
    }

    /**
     * isAllowedExtension() accepts vtt, srt and sbv in any case, and rejects anything
     * else, an empty string or a leading dot included.
     *
     * @param   string  $ext       Extension
     * @param   bool    $expected  Whether it is allowed
     *
     * @return  void
     *
     * @since   10.3.0
     */
    #[DataProvider('extensionProvider')]
    public function testIsAllowedExtension(string $ext, bool $expected): void
    {
        $this->assertSame($expected, $this->validator->isAllowedExtension($ext));
    }

    /**
     * Sizes in bytes, each with whether a caption may be that size.
     *
     * @return  array<string, array{0: int, 1: bool}>  Each case
     *
     * @since   10.3.0
     */
    public static function sizeProvider(): array
    {
        return [
            'one byte'            => [1, true],
            'one MB'              => [1024 * 1024, true],
            'exactly max (2 MB)'  => [2 * 1024 * 1024, true],
            'one byte over max'   => [2 * 1024 * 1024 + 1, false],
            'zero bytes rejected' => [0, false],
            'negative rejected'   => [-1, false],
        ];
    }

    /**
     * isAllowedSize() accepts 1 byte up to exactly 2 MB, and rejects zero, negative and
     * larger sizes.
     *
     * @param   int   $bytes     Size in bytes
     * @param   bool  $expected  Whether it is allowed
     *
     * @return  void
     *
     * @since   10.3.0
     */
    #[DataProvider('sizeProvider')]
    public function testIsAllowedSize(int $bytes, bool $expected): void
    {
        $this->assertSame($expected, $this->validator->isAllowedSize($bytes));
    }

    /**
     * detectFormat() reads a WEBVTT header as vtt.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatReturnsVttForWebvttHeader(): void
    {
        $this->assertSame('vtt', $this->validator->detectFormat("WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello"));
    }

    /**
     * detectFormat() reads a WEBVTT header behind a UTF-8 byte order mark as vtt.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatStripsUtf8Bom(): void
    {
        $this->assertSame('vtt', $this->validator->detectFormat("\xEF\xBB\xBFWEBVTT\n"));
    }

    /**
     * detectFormat() reads a WEBVTT header after leading blank lines and spaces as vtt.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatTrimsLeadingWhitespace(): void
    {
        $this->assertSame('vtt', $this->validator->detectFormat("\n\n  WEBVTT\n"));
    }

    /**
     * detectFormat() reads a numbered cue with comma timestamps and LF line endings as srt.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatReturnsSrtForNumericCueWithUnixNewline(): void
    {
        $this->assertSame('srt', $this->validator->detectFormat("1\n00:00:01,000 --> 00:00:02,000\nHello"));
    }

    /**
     * detectFormat() reads a numbered cue with comma timestamps and CRLF line endings as srt.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatReturnsSrtForNumericCueWithCrlfNewline(): void
    {
        $this->assertSame('srt', $this->validator->detectFormat("1\r\n00:00:01,000 --> 00:00:02,000\r\nHello"));
    }

    /**
     * detectFormat() reads a numbered cue behind a byte order mark as srt.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatReturnsSrtForBomThenSrt(): void
    {
        $this->assertSame('srt', $this->validator->detectFormat("\xEF\xBB\xBF1\n00:00:01,000 --> 00:00:02,000"));
    }

    /**
     * detectFormat() returns null for plain text.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatRejectsPlainText(): void
    {
        $this->assertNull($this->validator->detectFormat("This is just a regular text file."));
    }

    /**
     * detectFormat() returns null for an empty string.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatRejectsEmptyString(): void
    {
        $this->assertNull($this->validator->detectFormat(''));
    }

    /**
     * detectFormat() returns null for binary data.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatRejectsBinaryData(): void
    {
        // Random binary that doesn't match either header
        $this->assertNull($this->validator->detectFormat("\x00\x01\x02\x03\xFF\xFE binary garbage"));
    }

    /**
     * detectFormat() returns null for HTML that contains WEBVTT but doesn't start with it.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testDetectFormatRejectsHtmlPretendingToBeCaption(): void
    {
        $this->assertNull($this->validator->detectFormat("<html>WEBVTT</html>"));
    }

    /**
     * detectFormat() reads a cue with single-digit hours and a comma between its
     * timestamps as sbv.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testDetectFormatReturnsSbvForSingleDigitHourCue(): void
    {
        // Canonical YouTube SBV download — single-digit hour, comma between timestamps.
        $this->assertSame(
            'sbv',
            $this->validator->detectFormat("0:00:01.000,0:00:02.000\nHello")
        );
    }

    /**
     * detectFormat() reads an sbv cue with zero-padded hours as sbv too.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testDetectFormatReturnsSbvForDoubleDigitHourCue(): void
    {
        // Defensive: SBV parsers commonly accept zero-padded hours too.
        $this->assertSame(
            'sbv',
            $this->validator->detectFormat("01:23:45.000,01:23:46.500\nHello")
        );
    }

    /**
     * detectFormat() reads an sbv cue behind a byte order mark as sbv.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testDetectFormatReturnsSbvWithBom(): void
    {
        $this->assertSame(
            'sbv',
            $this->validator->detectFormat("\xEF\xBB\xBF0:00:01.000,0:00:02.000\nHello")
        );
    }

    /**
     * A file that opens with an sbv timestamp line, and no cue number, is detected as
     * sbv rather than srt.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testDetectFormatPrefersSbvOverSrtWhenBothCouldMatch(): void
    {
        // SBV's first line is also the timestamp line — no leading numeric counter.
        // The SBV branch must win to keep ' --> ' out of the SRT regex's path.
        $this->assertSame(
            'sbv',
            $this->validator->detectFormat("0:00:01.000,0:00:02.000\nText")
        );
    }

    /**
     * SBV inputs, each with the VTT convertSbvToVtt() should produce.
     *
     * @return  array<string, array{0: string, 1: string}>  Each case
     *
     * @since   10.3.3
     */
    public static function convertSbvToVttProvider(): array
    {
        return [
            'single cue, single-digit hour' => [
                "0:00:01.000,0:00:02.000\nHello world",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello world\n",
            ],
            'single cue, double-digit hour kept' => [
                "01:23:45.000,01:23:46.500\nLater",
                "WEBVTT\n\n01:23:45.000 --> 01:23:46.500\nLater\n",
            ],
            'multi-cue with blank-line separator' => [
                "0:00:01.000,0:00:02.000\nFirst\n\n0:00:03.000,0:00:04.000\nSecond",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nFirst\n\n00:00:03.000 --> 00:00:04.000\nSecond\n",
            ],
            'multi-line text in cue' => [
                "0:00:01.000,0:00:05.000\nLine one\nLine two",
                "WEBVTT\n\n00:00:01.000 --> 00:00:05.000\nLine one\nLine two\n",
            ],
            'CRLF line endings normalized' => [
                "0:00:01.000,0:00:02.000\r\nHello\r\n\r\n0:00:03.000,0:00:04.000\r\nWorld",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello\n\n00:00:03.000 --> 00:00:04.000\nWorld\n",
            ],
            'BOM stripped' => [
                "\xEF\xBB\xBF0:00:01.000,0:00:02.000\nHello",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello\n",
            ],
        ];
    }

    /**
     * convertSbvToVtt() produces exactly the expected VTT for each case.
     *
     * @param   string  $sbv          SBV input
     * @param   string  $expectedVtt  The VTT it should become
     *
     * @return  void
     *
     * @since   10.3.3
     */
    #[DataProvider('convertSbvToVttProvider')]
    public function testConvertSbvToVtt(string $sbv, string $expectedVtt): void
    {
        $this->assertSame($expectedVtt, $this->validator->convertSbvToVtt($sbv));
    }

    /**
     * convertSbvToVtt() output starts with the WEBVTT header and a blank line.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testConvertSbvToVttAlwaysStartsWithWebvttHeader(): void
    {
        $vtt = $this->validator->convertSbvToVtt("0:00:01.000,0:00:02.000\nHello");
        $this->assertStringStartsWith("WEBVTT\n\n", $vtt);
    }

    /**
     * convertSbvToVtt() drops a block with no timestamp line and keeps the cues around it.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testConvertSbvToVttSkipsBlocksWithoutTimestampLine(): void
    {
        // A garbage block in the middle should not crash conversion.
        $sbv = "0:00:01.000,0:00:02.000\nGood\n\nrandom garbage\n\n0:00:03.000,0:00:04.000\nAlso good";
        $vtt = $this->validator->convertSbvToVtt($sbv);

        $this->assertStringContainsString("00:00:01.000 --> 00:00:02.000\nGood", $vtt);
        $this->assertStringContainsString("00:00:03.000 --> 00:00:04.000\nAlso good", $vtt);
        $this->assertStringNotContainsString('garbage', $vtt);
    }

    /**
     * SRT inputs, each with the VTT convertSrtToVtt() should produce.
     *
     * @return  array<string, array{0: string, 1: string}>  Each case
     *
     * @since   10.3.3
     */
    public static function convertSrtToVttProvider(): array
    {
        return [
            'single cue with counter, comma → period' => [
                "1\n00:00:01,000 --> 00:00:02,000\nHello",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello\n",
            ],
            'multi-cue with non-sequential counters' => [
                "1\n00:00:01,000 --> 00:00:02,000\nFirst\n\n7\n00:00:10,500 --> 00:00:12,500\nSeventh",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nFirst\n\n00:00:10.500 --> 00:00:12.500\nSeventh\n",
            ],
            'multi-line cue text preserved' => [
                "1\n00:00:01,000 --> 00:00:05,000\nLine one\nLine two",
                "WEBVTT\n\n00:00:01.000 --> 00:00:05.000\nLine one\nLine two\n",
            ],
            'CRLF line endings normalized' => [
                "1\r\n00:00:01,000 --> 00:00:02,000\r\nHello\r\n\r\n2\r\n00:00:03,000 --> 00:00:04,000\r\nWorld",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello\n\n00:00:03.000 --> 00:00:04.000\nWorld\n",
            ],
            'BOM stripped' => [
                "\xEF\xBB\xBF1\n00:00:01,000 --> 00:00:02,000\nHello",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello\n",
            ],
            'commas in cue text untouched' => [
                "1\n00:00:01,000 --> 00:00:02,000\nWell, hello, there.",
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nWell, hello, there.\n",
            ],
        ];
    }

    /**
     * convertSrtToVtt() produces exactly the expected VTT for each case.
     *
     * @param   string  $srt          SRT input
     * @param   string  $expectedVtt  The VTT it should become
     *
     * @return  void
     *
     * @since   10.3.3
     */
    #[DataProvider('convertSrtToVttProvider')]
    public function testConvertSrtToVtt(string $srt, string $expectedVtt): void
    {
        $this->assertSame($expectedVtt, $this->validator->convertSrtToVtt($srt));
    }

    /**
     * convertSrtToVtt() output starts with the WEBVTT header and a blank line.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testConvertSrtToVttAlwaysStartsWithWebvttHeader(): void
    {
        $vtt = $this->validator->convertSrtToVtt("1\n00:00:01,000 --> 00:00:02,000\nHi");
        $this->assertStringStartsWith("WEBVTT\n\n", $vtt);
    }

    /**
     * convertSrtToVtt() drops a block with no valid timestamp line and keeps the cues
     * around it.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testConvertSrtToVttSkipsBlocksWithoutValidTimestampLine(): void
    {
        // Garbage block in the middle should not crash conversion.
        $srt = "1\n00:00:01,000 --> 00:00:02,000\nGood\n\nrandom garbage\n\n2\n00:00:03,000 --> 00:00:04,000\nAlso good";
        $vtt = $this->validator->convertSrtToVtt($srt);

        $this->assertStringContainsString("00:00:01.000 --> 00:00:02.000\nGood", $vtt);
        $this->assertStringContainsString("00:00:03.000 --> 00:00:04.000\nAlso good", $vtt);
        $this->assertStringNotContainsString('garbage', $vtt);
    }

    /**
     * convertSrtToVtt() turns the commas in timestamps into periods but leaves the commas
     * in cue text alone.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testConvertSrtToVttDoesNotTouchCommasInsideText(): void
    {
        // Property test: commas inside body text must round-trip verbatim.
        $srt = "1\n00:00:01,000 --> 00:00:02,000\nA, B, C, D, E";
        $vtt = $this->validator->convertSrtToVtt($srt);

        $this->assertStringContainsString('A, B, C, D, E', $vtt);
        $this->assertStringContainsString('00:00:01.000 --> 00:00:02.000', $vtt);
    }

    /**
     * VTT inputs, each with the SRT convertVttToSrt() should produce.
     *
     * @return  array<string, array{0: string, 1: string}>  Each case
     *
     * @since   10.3.3
     */
    public static function convertVttToSrtProvider(): array
    {
        return [
            'single cue, period → comma' => [
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello",
                "1\n00:00:01,000 --> 00:00:02,000\nHello\n",
            ],
            'multi-cue numbered sequentially' => [
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nFirst\n\n00:00:03.000 --> 00:00:04.000\nSecond",
                "1\n00:00:01,000 --> 00:00:02,000\nFirst\n\n2\n00:00:03,000 --> 00:00:04,000\nSecond\n",
            ],
            'NOTE block stripped' => [
                "WEBVTT\n\nNOTE this is a comment\n\n00:00:01.000 --> 00:00:02.000\nHello",
                "1\n00:00:01,000 --> 00:00:02,000\nHello\n",
            ],
            'STYLE block stripped' => [
                "WEBVTT\n\nSTYLE\n::cue { color: red }\n\n00:00:01.000 --> 00:00:02.000\nHello",
                "1\n00:00:01,000 --> 00:00:02,000\nHello\n",
            ],
            'cue identifier line dropped' => [
                "WEBVTT\n\nintro-cue\n00:00:01.000 --> 00:00:02.000\nHello",
                "1\n00:00:01,000 --> 00:00:02,000\nHello\n",
            ],
            'cue settings dropped' => [
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000 align:start position:50%\nHello",
                "1\n00:00:01,000 --> 00:00:02,000\nHello\n",
            ],
            'inline tags stripped' => [
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\n<v Speaker>Hello <c.red>world</c></v>",
                "1\n00:00:01,000 --> 00:00:02,000\nHello world\n",
            ],
            'MM:SS.mmm hours-absent prepends 00' => [
                "WEBVTT\n\n00:01.500 --> 00:02.500\nShort",
                "1\n00:00:01,500 --> 00:00:02,500\nShort\n",
            ],
        ];
    }

    /**
     * convertVttToSrt() produces exactly the expected SRT for each case.
     *
     * @param   string  $vtt          VTT input
     * @param   string  $expectedSrt  The SRT it should become
     *
     * @return  void
     *
     * @since   10.3.3
     */
    #[DataProvider('convertVttToSrtProvider')]
    public function testConvertVttToSrt(string $vtt, string $expectedSrt): void
    {
        $this->assertSame($expectedSrt, $this->validator->convertVttToSrt($vtt));
    }

    /**
     * VTT inputs, each with the SBV convertVttToSbv() should produce.
     *
     * @return  array<string, array{0: string, 1: string}>  Each case
     *
     * @since   10.3.3
     */
    public static function convertVttToSbvProvider(): array
    {
        return [
            'single cue, hours reduced to single digit' => [
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello",
                "0:00:01.000,0:00:02.000\nHello\n",
            ],
            'multi-digit hours kept' => [
                "WEBVTT\n\n12:34:56.000 --> 12:34:57.000\nLater",
                "12:34:56.000,12:34:57.000\nLater\n",
            ],
            'multi-cue blank-line separated' => [
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nFirst\n\n00:00:03.000 --> 00:00:04.000\nSecond",
                "0:00:01.000,0:00:02.000\nFirst\n\n0:00:03.000,0:00:04.000\nSecond\n",
            ],
            'NOTE/STYLE/REGION blocks stripped' => [
                "WEBVTT\n\nNOTE foo\n\nSTYLE\n::cue {}\n\nREGION\nid:r1\n\n00:00:01.000 --> 00:00:02.000\nHello",
                "0:00:01.000,0:00:02.000\nHello\n",
            ],
            'cue identifier + settings dropped' => [
                "WEBVTT\n\nintro\n00:00:01.000 --> 00:00:02.000 align:center\nHello",
                "0:00:01.000,0:00:02.000\nHello\n",
            ],
            'inline tags stripped' => [
                "WEBVTT\n\n00:00:01.000 --> 00:00:02.000\n<v Speaker>Hi</v>",
                "0:00:01.000,0:00:02.000\nHi\n",
            ],
            'MM:SS.mmm hours-absent prepends 0' => [
                "WEBVTT\n\n00:01.500 --> 00:02.500\nShort",
                "0:00:01.500,0:00:02.500\nShort\n",
            ],
        ];
    }

    /**
     * convertVttToSbv() produces exactly the expected SBV for each case.
     *
     * @param   string  $vtt          VTT input
     * @param   string  $expectedSbv  The SBV it should become
     *
     * @return  void
     *
     * @since   10.3.3
     */
    #[DataProvider('convertVttToSbvProvider')]
    public function testConvertVttToSbv(string $vtt, string $expectedSbv): void
    {
        $this->assertSame($expectedSbv, $this->validator->convertVttToSbv($vtt));
    }

    /**
     * convertVttToSrt() returns an empty string for a VTT file with no cues.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testConvertVttToSrtReturnsEmptyForHeaderOnly(): void
    {
        $this->assertSame('', $this->validator->convertVttToSrt("WEBVTT\n\n"));
    }

    /**
     * convertVttToSbv() returns an empty string for a VTT file with no cues.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testConvertVttToSbvReturnsEmptyForHeaderOnly(): void
    {
        $this->assertSame('', $this->validator->convertVttToSbv("WEBVTT\n\n"));
    }

    /**
     * Converting SRT to VTT and back keeps each cue's timestamps and text, commas in
     * the text included.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testRoundTripSrtToVttToSrtPreservesCues(): void
    {
        // Property test: SRT → VTT → SRT round-trip yields equivalent
        // cue content (numbering may renormalize, whitespace may differ
        // by a trailing newline).
        $original = "1\n00:00:01,500 --> 00:00:02,750\nFirst, with comma\n\n2\n00:01:00,000 --> 00:01:05,000\nLater";
        $vtt      = $this->validator->convertSrtToVtt($original);
        $rebuilt  = $this->validator->convertVttToSrt($vtt);

        $this->assertStringContainsString("00:00:01,500 --> 00:00:02,750\nFirst, with comma", $rebuilt);
        $this->assertStringContainsString("00:01:00,000 --> 00:01:05,000\nLater", $rebuilt);
    }

    /**
     * Converting SBV to VTT and back keeps each cue's timestamps and text.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testRoundTripSbvToVttToSbvPreservesCues(): void
    {
        $original = "0:00:01.500,0:00:02.750\nFirst\n\n1:23:45.000,1:23:46.500\nLater";
        $vtt      = $this->validator->convertSbvToVtt($original);
        $rebuilt  = $this->validator->convertVttToSbv($vtt);

        $this->assertStringContainsString("0:00:01.500,0:00:02.750\nFirst", $rebuilt);
        $this->assertStringContainsString("1:23:45.000,1:23:46.500\nLater", $rebuilt);
    }

    /**
     * The only transcript extension is txt.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testTranscriptExtensionsConstantContainsTxt(): void
    {
        $this->assertSame(['txt'], CwmcaptionValidator::TRANSCRIPT_EXTENSIONS);
    }

    /**
     * Extensions, each with whether a transcript may have it.
     *
     * @return  array<string, array{0: string, 1: bool}>  Each case
     *
     * @since   10.3.3
     */
    public static function transcriptExtensionProvider(): array
    {
        return [
            'lowercase txt' => ['txt', true],
            'uppercase TXT' => ['TXT', true],
            'mixed Txt'     => ['Txt', true],
            'vtt rejected'  => ['vtt', false],
            'srt rejected'  => ['srt', false],
            'sbv rejected'  => ['sbv', false],
            'empty'         => ['', false],
            'with-dot'      => ['.txt', false],
        ];
    }

    /**
     * isTranscriptExtension() accepts txt in any case, and rejects the caption
     * extensions, an empty string and a leading dot.
     *
     * @param   string  $ext       Extension
     * @param   bool    $expected  Whether it is a transcript extension
     *
     * @return  void
     *
     * @since   10.3.3
     */
    #[DataProvider('transcriptExtensionProvider')]
    public function testIsTranscriptExtension(string $ext, bool $expected): void
    {
        $this->assertSame($expected, $this->validator->isTranscriptExtension($ext));
    }

    /**
     * No caption extension counts as a transcript extension, and no transcript
     * extension as a caption one.
     *
     * @return  void
     *
     * @since   10.3.3
     */
    public function testTranscriptAndCaptionExtensionsAreDisjoint(): void
    {
        // Property: a single extension should never satisfy both checks —
        // otherwise the controller's branch order would matter for correctness.
        foreach (CwmcaptionValidator::ALLOWED_EXTENSIONS as $captionExt) {
            $this->assertFalse(
                $this->validator->isTranscriptExtension($captionExt),
                "Caption extension '$captionExt' must not also be a transcript extension"
            );
        }

        foreach (CwmcaptionValidator::TRANSCRIPT_EXTENSIONS as $transcriptExt) {
            $this->assertFalse(
                $this->validator->isAllowedExtension($transcriptExt),
                "Transcript extension '$transcriptExt' must not also be an allowed caption extension"
            );
        }
    }

    /**
     * Filenames, each with what sanitizeFilename() should make of it.
     *
     * @return  array<string, array{0: string, 1: string}>  Each case
     *
     * @since   10.3.0
     */
    public static function sanitizeFilenameProvider(): array
    {
        return [
            'plain ASCII kept'         => ['my-caption_01', 'my-caption_01'],
            'space stripped'           => ['hello world', 'helloworld'],
            'unicode stripped'         => ['привет', 'caption'],
            'emoji stripped'           => ['caption😀file', 'captionfile'],
            'path traversal stripped'  => ['../../etc/passwd', 'etcpasswd'],
            'backslashes stripped'     => ['..\\..\\windows', 'windows'],
            'NUL byte stripped'        => ["safe\0name", 'safename'],
            'shell metachars stripped' => ['name;rm -rf /', 'namerm-rf'],
            'dots stripped'            => ['a.b.c', 'abc'],
            'empty falls back'         => ['', 'caption'],
            'only-stripped falls back' => ['😀😀😀', 'caption'],
            'whitespace falls back'    => ['   ', 'caption'],
        ];
    }

    /**
     * sanitizeFilename() keeps only ASCII letters, digits, hyphens and underscores, and
     * falls back to caption when nothing is left.
     *
     * @param   string  $input     Filename
     * @param   string  $expected  What it should become
     *
     * @return  void
     *
     * @since   10.3.0
     */
    #[DataProvider('sanitizeFilenameProvider')]
    public function testSanitizeFilename(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->validator->sanitizeFilename($input));
    }

    /**
     * buildFilename() returns caption_TIMESTAMP_BASE.EXT for an ordinary filename.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testBuildFilenameProducesExpectedShape(): void
    {
        $name = $this->validator->buildFilename('lecture-01.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_lecture-01.vtt', $name);
    }

    /**
     * buildFilename() uses the extension it is given, not the original filename's.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testBuildFilenameUsesProvidedExtensionNotOriginal(): void
    {
        // Caller may pass a normalized lowercase ext separately
        $name = $this->validator->buildFilename('Subtitles.SRT', 'srt', 1735689600);

        $this->assertSame('caption_1735689600_Subtitles.srt', $name);
    }

    /**
     * buildFilename() drops the directories from a traversal path and keeps only the
     * file's own name.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testBuildFilenameStripsPathComponentsThenSanitizes(): void
    {
        // pathinfo() strips the directory portion before sanitization runs —
        // belt-and-braces: even if a malicious uploader supplies a traversal
        // path, only the basename's filename ever reaches the regex filter.
        $name = $this->validator->buildFilename('../../../etc/passwd.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_passwd.vtt', $name);
    }

    /**
     * buildFilename() strips unsafe characters from the base name.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testBuildFilenameSanitizesUnsafeCharsInBaseName(): void
    {
        $name = $this->validator->buildFilename('hello world;rm.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_helloworldrm.vtt', $name);
    }

    /**
     * buildFilename() cuts a long base name to 50 characters.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testBuildFilenameTruncatesLongBaseNameToFiftyChars(): void
    {
        $longBase = str_repeat('a', 200);
        $name     = $this->validator->buildFilename($longBase . '.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_' . str_repeat('a', 50) . '.vtt', $name);
    }

    /**
     * buildFilename() uses caption as the base name when nothing survives sanitizing.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testBuildFilenameFallsBackWhenNothingSurvivesSanitization(): void
    {
        $name = $this->validator->buildFilename('😀😀😀.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_caption.vtt', $name);
    }

    /**
     * Without a timestamp, buildFilename() uses the current time.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function testBuildFilenameDefaultsTimestampToCurrentTime(): void
    {
        $before = time();
        $name   = $this->validator->buildFilename('test.vtt', 'vtt');
        $after  = time();

        $this->assertMatchesRegularExpression('/^caption_(\d+)_test\.vtt$/', $name);

        preg_match('/^caption_(\d+)_/', $name, $m);
        $stamp = (int) $m[1];

        $this->assertGreaterThanOrEqual($before, $stamp);
        $this->assertLessThanOrEqual($after, $stamp);
    }
}

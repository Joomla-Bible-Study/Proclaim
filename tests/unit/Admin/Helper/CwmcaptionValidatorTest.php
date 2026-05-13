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
    private CwmcaptionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new CwmcaptionValidator();
    }

    public function testAllowedExtensionsConstantContainsVttSrtAndSbv(): void
    {
        $this->assertSame(['vtt', 'srt', 'sbv'], CwmcaptionValidator::ALLOWED_EXTENSIONS);
    }

    public function testMaxSizeIsTwoMegabytes(): void
    {
        $this->assertSame(2 * 1024 * 1024, CwmcaptionValidator::MAX_SIZE_BYTES);
    }

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

    #[DataProvider('extensionProvider')]
    public function testIsAllowedExtension(string $ext, bool $expected): void
    {
        $this->assertSame($expected, $this->validator->isAllowedExtension($ext));
    }

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

    #[DataProvider('sizeProvider')]
    public function testIsAllowedSize(int $bytes, bool $expected): void
    {
        $this->assertSame($expected, $this->validator->isAllowedSize($bytes));
    }

    public function testDetectFormatReturnsVttForWebvttHeader(): void
    {
        $this->assertSame('vtt', $this->validator->detectFormat("WEBVTT\n\n00:00:01.000 --> 00:00:02.000\nHello"));
    }

    public function testDetectFormatStripsUtf8Bom(): void
    {
        $this->assertSame('vtt', $this->validator->detectFormat("\xEF\xBB\xBFWEBVTT\n"));
    }

    public function testDetectFormatTrimsLeadingWhitespace(): void
    {
        $this->assertSame('vtt', $this->validator->detectFormat("\n\n  WEBVTT\n"));
    }

    public function testDetectFormatReturnsSrtForNumericCueWithUnixNewline(): void
    {
        $this->assertSame('srt', $this->validator->detectFormat("1\n00:00:01,000 --> 00:00:02,000\nHello"));
    }

    public function testDetectFormatReturnsSrtForNumericCueWithCrlfNewline(): void
    {
        $this->assertSame('srt', $this->validator->detectFormat("1\r\n00:00:01,000 --> 00:00:02,000\r\nHello"));
    }

    public function testDetectFormatReturnsSrtForBomThenSrt(): void
    {
        $this->assertSame('srt', $this->validator->detectFormat("\xEF\xBB\xBF1\n00:00:01,000 --> 00:00:02,000"));
    }

    public function testDetectFormatRejectsPlainText(): void
    {
        $this->assertNull($this->validator->detectFormat("This is just a regular text file."));
    }

    public function testDetectFormatRejectsEmptyString(): void
    {
        $this->assertNull($this->validator->detectFormat(''));
    }

    public function testDetectFormatRejectsBinaryData(): void
    {
        // Random binary that doesn't match either header
        $this->assertNull($this->validator->detectFormat("\x00\x01\x02\x03\xFF\xFE binary garbage"));
    }

    public function testDetectFormatRejectsHtmlPretendingToBeCaption(): void
    {
        $this->assertNull($this->validator->detectFormat("<html>WEBVTT</html>"));
    }

    public function testDetectFormatReturnsSbvForSingleDigitHourCue(): void
    {
        // Canonical YouTube SBV download — single-digit hour, comma between timestamps.
        $this->assertSame(
            'sbv',
            $this->validator->detectFormat("0:00:01.000,0:00:02.000\nHello")
        );
    }

    public function testDetectFormatReturnsSbvForDoubleDigitHourCue(): void
    {
        // Defensive: SBV parsers commonly accept zero-padded hours too.
        $this->assertSame(
            'sbv',
            $this->validator->detectFormat("01:23:45.000,01:23:46.500\nHello")
        );
    }

    public function testDetectFormatReturnsSbvWithBom(): void
    {
        $this->assertSame(
            'sbv',
            $this->validator->detectFormat("\xEF\xBB\xBF0:00:01.000,0:00:02.000\nHello")
        );
    }

    public function testDetectFormatPrefersSbvOverSrtWhenBothCouldMatch(): void
    {
        // SBV's first line is also the timestamp line — no leading numeric counter.
        // The SBV branch must win to keep ' --> ' out of the SRT regex's path.
        $this->assertSame(
            'sbv',
            $this->validator->detectFormat("0:00:01.000,0:00:02.000\nText")
        );
    }

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

    #[DataProvider('convertSbvToVttProvider')]
    public function testConvertSbvToVtt(string $sbv, string $expectedVtt): void
    {
        $this->assertSame($expectedVtt, $this->validator->convertSbvToVtt($sbv));
    }

    public function testConvertSbvToVttAlwaysStartsWithWebvttHeader(): void
    {
        $vtt = $this->validator->convertSbvToVtt("0:00:01.000,0:00:02.000\nHello");
        $this->assertStringStartsWith("WEBVTT\n\n", $vtt);
    }

    public function testConvertSbvToVttSkipsBlocksWithoutTimestampLine(): void
    {
        // A garbage block in the middle should not crash conversion.
        $sbv = "0:00:01.000,0:00:02.000\nGood\n\nrandom garbage\n\n0:00:03.000,0:00:04.000\nAlso good";
        $vtt = $this->validator->convertSbvToVtt($sbv);

        $this->assertStringContainsString("00:00:01.000 --> 00:00:02.000\nGood", $vtt);
        $this->assertStringContainsString("00:00:03.000 --> 00:00:04.000\nAlso good", $vtt);
        $this->assertStringNotContainsString('garbage', $vtt);
    }

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

    #[DataProvider('sanitizeFilenameProvider')]
    public function testSanitizeFilename(string $input, string $expected): void
    {
        $this->assertSame($expected, $this->validator->sanitizeFilename($input));
    }

    public function testBuildFilenameProducesExpectedShape(): void
    {
        $name = $this->validator->buildFilename('lecture-01.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_lecture-01.vtt', $name);
    }

    public function testBuildFilenameUsesProvidedExtensionNotOriginal(): void
    {
        // Caller may pass a normalized lowercase ext separately
        $name = $this->validator->buildFilename('Subtitles.SRT', 'srt', 1735689600);

        $this->assertSame('caption_1735689600_Subtitles.srt', $name);
    }

    public function testBuildFilenameStripsPathComponentsThenSanitizes(): void
    {
        // pathinfo() strips the directory portion before sanitization runs —
        // belt-and-braces: even if a malicious uploader supplies a traversal
        // path, only the basename's filename ever reaches the regex filter.
        $name = $this->validator->buildFilename('../../../etc/passwd.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_passwd.vtt', $name);
    }

    public function testBuildFilenameSanitizesUnsafeCharsInBaseName(): void
    {
        $name = $this->validator->buildFilename('hello world;rm.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_helloworldrm.vtt', $name);
    }

    public function testBuildFilenameTruncatesLongBaseNameToFiftyChars(): void
    {
        $longBase = str_repeat('a', 200);
        $name     = $this->validator->buildFilename($longBase . '.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_' . str_repeat('a', 50) . '.vtt', $name);
    }

    public function testBuildFilenameFallsBackWhenNothingSurvivesSanitization(): void
    {
        $name = $this->validator->buildFilename('😀😀😀.vtt', 'vtt', 1735689600);

        $this->assertSame('caption_1735689600_caption.vtt', $name);
    }

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

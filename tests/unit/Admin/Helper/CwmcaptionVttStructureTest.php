<?php

/**
 * Unit tests for whole-body WebVTT structure validation.
 *
 * @package    Proclaim.Test
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmcaptionValidator;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1581.
 *
 * detectFormat() reads 64 bytes, so its VTT test is a six-character prefix
 * check. Uploads detected as VTT were then moved into place byte-for-byte and
 * served from a public URL, so "WEBVTT" followed by up to two megabytes of
 * anything was stored verbatim. SRT and SBV never had that gap: both are parsed
 * and re-serialised on the way in, so only valid cues survive.
 *
 * The body is now validated in full. Validated rather than normalised: the
 * existing parseVttCues() drops NOTE, STYLE and REGION blocks, cue identifiers,
 * cue settings and inline tags, which is correct when converting to a format
 * that cannot express them and destructive when applied to a file the user
 * authored.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmcaptionValidator::class)]
class CwmcaptionVttStructureTest extends ProclaimTestCase
{
    private CwmcaptionValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new CwmcaptionValidator();
    }

    /**
     * Files that must be accepted, including every WebVTT feature the
     * lossy converter would have discarded.
     *
     * @return array<string, array{0: string}>
     */
    public static function validProvider(): array
    {
        return [
            'minimal header only' => ["WEBVTT"],
            'single cue'          => ["WEBVTT\n\n00:00:01.000 --> 00:00:04.000\nHello"],
            'short timestamps'    => ["WEBVTT\n\n00:01.000 --> 00:04.000\nHello"],
            'cue identifier'      => ["WEBVTT\n\nintro\n00:00:01.000 --> 00:00:04.000\nHello"],
            'cue settings'        => ["WEBVTT\n\n00:00:01.000 --> 00:00:04.000 line:0 align:middle\nHello"],
            'speaker tag'         => ["WEBVTT\n\n00:00:01.000 --> 00:00:04.000\n<v Pastor>Welcome</v>"],
            'NOTE block'          => ["WEBVTT\n\nNOTE this is a comment\n\n00:00:01.000 --> 00:00:04.000\nHi"],
            'STYLE block'         => ["WEBVTT\n\nSTYLE\n::cue { color: yellow }\n\n00:00:01.000 --> 00:00:04.000\nHi"],
            'REGION block'        => ["WEBVTT\n\nREGION\nid:fred width:40%\n\n00:00:01.000 --> 00:00:04.000\nHi"],
            'header metadata'     => ["WEBVTT - Some Title\n\n00:00:01.000 --> 00:00:04.000\nHi"],
            'crlf line endings'   => ["WEBVTT\r\n\r\n00:00:01.000 --> 00:00:04.000\r\nHello"],
            'BOM prefix'          => ["\xEF\xBB\xBFWEBVTT\n\n00:00:01.000 --> 00:00:04.000\nHello"],
            'multiple cues'       => ["WEBVTT\n\n00:00:01.000 --> 00:00:04.000\nOne\n\n00:00:05.000 --> 00:00:09.000\nTwo"],
            'multi-line cue text' => ["WEBVTT\n\n00:00:01.000 --> 00:00:04.000\nLine one\nLine two"],
            'hours over 99'       => ["WEBVTT\n\n100:00:01.000 --> 100:00:04.000\nHi"],
        ];
    }

    /**
     * A valid caption file must still be accepted and stored unchanged.
     *
     * @param   string  $body  Caption file contents
     */
    #[DataProvider('validProvider')]
    #[TestDox('A structurally valid WebVTT file is accepted')]
    public function testValidBodiesAreAccepted(string $body): void
    {
        $this->assertTrue(
            $this->validator->isStructurallyVtt($body),
            'Rejecting a legitimate caption feature would be worse than the hole being closed — see #1581'
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function invalidProvider(): array
    {
        return [
            // The reported hole: the prefix passes, the rest is anything.
            'header then arbitrary text'  => ["WEBVTT\n\nthis is not a cue at all"],
            'header then html'            => ["WEBVTT\n\n<html><body>phishing</body></html>"],
            'header then php'             => ["WEBVTT\n\n<?php echo 'x'; ?>"],
            'header then binary-ish'      => ["WEBVTT\n\n\x00\x01\x02\x03 payload"],
            'header then a second header' => ["WEBVTT\n\nWEBVTT junk"],
            'valid cue then junk'         => ["WEBVTT\n\n00:00:01.000 --> 00:00:04.000\nHi\n\nnot a cue"],
            // Not VTT at all.
            'no header'            => ["00:00:01.000 --> 00:00:04.000\nHello"],
            'empty'                => [''],
            'plain text'           => ['just some text'],
            'header glued to text' => ['WEBVTTjunk'],
            // Malformed timestamps.
            'missing milliseconds' => ["WEBVTT\n\n00:00:01 --> 00:00:04\nHi"],
            'arrow only'           => ["WEBVTT\n\n--> \nHi"],
        ];
    }

    /**
     * @param   string  $body  Caption file contents
     */
    #[DataProvider('invalidProvider')]
    #[TestDox('Content past the header that is not WebVTT is rejected')]
    public function testInvalidBodiesAreRejected(string $body): void
    {
        $this->assertFalse(
            $this->validator->isStructurallyVtt($body),
            'A six-byte prefix check let arbitrary content reach a public URL — see #1581'
        );
    }

    /**
     * The specific upload described in the issue: a valid-looking header
     * followed by a payload well past the 64 bytes detectFormat() reads.
     */
    #[TestDox('A large payload hidden past the sniff window is rejected')]
    public function testPayloadBeyondTheSniffWindowIsRejected(): void
    {
        $body = "WEBVTT\n\n" . str_repeat('A', 200000);

        $this->assertSame(
            'vtt',
            $this->validator->detectFormat(substr($body, 0, 64)),
            'Precondition: the sniff still routes this as VTT — that is all it can do'
        );
        $this->assertFalse(
            $this->validator->isStructurallyVtt($body),
            'The whole body must be checked, not just the head — see #1581'
        );
    }

    /**
     * The upload path must actually call the validator; detecting the format
     * is not the same as trusting the file.
     */
    #[TestDox('The upload endpoint validates the whole body before storing')]
    public function testUploadPathValidatesBeforeStoring(): void
    {
        $source = (string) file_get_contents(
            \dirname(__DIR__, 4) . '/admin/src/Controller/CwmmediafileController.php'
        );

        $this->assertStringContainsString(
            'isStructurallyVtt($body)',
            $source,
            'move_uploaded_file() stores the file unread — the body must be checked first — see #1581'
        );

        $validateAt = strpos($source, 'isStructurallyVtt($body)');
        $moveAt     = strpos($source, "'vtt' => move_uploaded_file");

        $this->assertNotFalse($moveAt, 'Expected the VTT store branch to still be present');
        $this->assertLessThan($moveAt, $validateAt, 'Validation must happen before the file is moved into place');
    }
}

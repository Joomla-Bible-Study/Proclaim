<?php

/**
 * Integration tests for extension-based MIME detection.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmmime;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1578.
 *
 * fromExtension() ran every value through parse_url(), which splits on '?' and
 * '#' whether or not the value is a URL. A stored filename like
 * "Sermon #12.mp4" became "Sermon ", the extension was lost, and detection
 * returned null.
 *
 * Downstream that meant a video declared as audio/mpeg in the podcast feed's
 * enclosure, application/octet-stream on download, and the MIME-contradiction
 * check silently skipping exactly the files most likely to be wrong. An
 * enclosure whose type or URL carries no usable extension is the same class of
 * defect that caused the Apple Podcasts delisting in v10.5.4/10.5.5.
 *
 * @since  10.5.6
 */
#[CoversClass(Cwmmime::class)]
class CwmmimeExtensionTest extends IntegrationTestCase
{
    /**
     * Filenames, paths and URLs with the extension extensionOf() should find: names holding #
     * or ?, URLs with a query or fragment, and plain names, one with no extension.
     *
     * @return  array<string, array{0: string, 1: string}>  Each input and its extension
     *
     * @since   10.5.6
     */
    public static function extensionProvider(): array
    {
        return [
            // The bug: '#' and '?' in an ordinary filename.
            'hash in filename'       => ['Sermon #12.mp4', 'mp4'],
            'hash mid-filename'      => ['Track #3 - Sermon.mp4', 'mp4'],
            'question in filename'   => ['episode?12.mp3', 'mp3'],
            'hash in a path'         => ['media/sermons/talk #2.m4v', 'm4v'],
            'windows path with hash' => ['C:\\media\\file #1.mp4', 'mp4'],

            // URLs still have their query and fragment stripped.
            'url with query'        => ['https://cdn.example.org/a/b.mp4?v=1', 'mp4'],
            'url with fragment'     => ['https://cdn.example.org/a/b.mp3#t=30', 'mp3'],
            'protocol-relative url' => ['//cdn.example.org/v.m4v#t=1', 'm4v'],

            // Unremarkable cases.
            'plain filename'      => ['normal.mp4', 'mp4'],
            'uppercase extension' => ['SERMON.MP3', 'mp3'],
            'no extension'        => ['sermon', ''],
        ];
    }

    /**
     * extensionOf() finds the extension of a name holding # or ?, drops a URL's query and
     * fragment, lower-cases the result, and returns an empty string when there is none.
     *
     * @param   string  $input     Filename or URL
     * @param   string  $expected  Expected extension
     *
     * @return  void
     *
     * @since   10.5.6
     */
    #[DataProvider('extensionProvider')]
    #[TestDox('The extension survives characters parse_url() would treat as delimiters')]
    public function testExtensionOf(string $input, string $expected): void
    {
        $this->assertSame(
            $expected,
            Cwmmime::extensionOf($input),
            'parse_url() split a bare filename on # or ? and lost the extension — see #1578'
        );
    }

    /**
     * Filenames, a path and a URL with the MIME type fromExtension() should give, null for
     * an unknown extension.
     *
     * @return  array<string, array{0: string, 1: ?string}>  Each input and its MIME type
     *
     * @since   10.5.6
     */
    public static function mimeProvider(): array
    {
        return [
            'hash in filename'     => ['Sermon #12.mp4', 'video/mp4'],
            'question in filename' => ['episode?12.mp3', 'audio/mpeg'],
            'hash in a path'       => ['media/talk #2.m4v', 'video/mp4'],
            'url with query'       => ['https://cdn.example.org/a/b.mp4?v=1', 'video/mp4'],
            'plain filename'       => ['normal.mp3', 'audio/mpeg'],
            'unknown extension'    => ['notes.xyz', null],
        ];
    }

    /**
     * The consequence that matters: a video whose name contains '#' was
     * reported as having no detectable type, and the podcast enclosure then
     * fell back to a hardcoded audio/mpeg.
     *
     * @param   string   $input     Filename or URL
     * @param   ?string  $expected  Expected MIME type
     *
     * @return  void
     *
     * @since   10.5.6
     */
    #[DataProvider('mimeProvider')]
    #[TestDox('MIME detection works for filenames containing # or ?')]
    public function testFromExtension(string $input, ?string $expected): void
    {
        $this->assertSame(
            $expected,
            Cwmmime::fromExtension($input),
            'A video declared as audio/mpeg in an enclosure is what got episodes delisted — see #1578'
        );
    }

    /**
     * The tracked enclosure URL is built from an extension and returns null
     * without one, which is precisely what Apple refuses to ingest. It must
     * derive that extension the same way.
     *
     * @return  void
     *
     * @since   10.5.6
     */
    #[TestDox('The tracked enclosure builder uses the shared extension helper')]
    public function testEnclosureBuilderUsesTheSharedHelper(): void
    {
        $source = file_get_contents(JPATH_ROOT . '/site/src/Helper/Cwmpodcast.php');

        $this->assertIsString($source);
        $this->assertStringContainsString(
            'Cwmmime::extensionOf($path)',
            $source,
            'buildTrackedEnclosureUrl() must not re-derive the extension with bare parse_url() — see #1578'
        );
    }
}

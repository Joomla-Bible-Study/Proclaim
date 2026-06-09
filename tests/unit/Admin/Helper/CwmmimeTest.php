<?php

/**
 * Unit tests for Cwmmime helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmmime;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for the shared Cwmmime helper.
 *
 * Pure-logic tests covering the extension map and download-header resolution
 * that were previously duplicated across Cwmbackup, CWMAddon and Cwmpodcast.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmmime::class)]
class CwmmimeTest extends ProclaimTestCase
{
    public static function extensionProvider(): array
    {
        return [
            // Media (preserves the previous addon/podcast map values)
            'mp3 -> audio/mpeg'  => ['sermon.mp3', 'audio/mpeg'],
            'm4a -> audio/mp4'   => ['talk.m4a', 'audio/mp4'],
            'ogg -> audio/ogg'   => ['clip.ogg', 'audio/ogg'],
            'mp4 -> video/mp4'   => ['video.mp4', 'video/mp4'],
            'webm -> video/webm' => ['v.webm', 'video/webm'],
            'mov -> quicktime'   => ['v.mov', 'video/quicktime'],
            // Documents / archives (preserves the previous backup map intent)
            'pdf -> pdf'    => ['notes.pdf', 'application/pdf'],
            'zip -> zip'    => ['backup.zip', 'application/zip'],
            'sql -> x-sql'  => ['dump.sql', 'text/x-sql'],
            'doc -> msword' => ['file.doc', 'application/msword'],
            // Case-insensitive + URL + nested path handling
            'uppercase MP3'  => ['SERMON.MP3', 'audio/mpeg'],
            'url with query' => ['https://cdn.example.com/a/b.mp3?token=xyz', 'audio/mpeg'],
            'nested path'    => ['/var/www/media/file.pdf', 'application/pdf'],
            // Unknown / none
            'unknown extension' => ['file.xyz', null],
            'no extension'      => ['README', null],
        ];
    }

    #[DataProvider('extensionProvider')]
    public function testFromExtension(string $input, ?string $expected): void
    {
        $this->assertSame($expected, Cwmmime::fromExtension($input));
    }

    public function testForDownloadHonoursExplicitMimeType(): void
    {
        $this->assertSame('application/zip', Cwmmime::forDownload('application/zip', 'whatever.bin'));
    }

    public function testForDownloadDerivesFromExtensionWhenBlank(): void
    {
        $this->assertSame('application/zip', Cwmmime::forDownload('', 'backup.zip'));
        $this->assertSame('text/x-sql', Cwmmime::forDownload('', 'dump.sql'));
    }

    public function testForDownloadFallsBackToForceDownloadForUnknownExtension(): void
    {
        $this->assertSame('application/force-download', Cwmmime::forDownload('', 'mystery.xyz'));
    }
}

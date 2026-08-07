<?php

/**
 * Integration tests for podcast thumbnail resizing.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmimagelib;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1577.
 *
 * A PNG whose header is intact but whose data stream is truncated satisfies
 * getimagesize() -- correct mime, correct dimensions -- and then fails to
 * decode, with imagecreatefrompng() returning false. That false reached
 * imagecopyresampled(), which type-hints GdImage and raises a TypeError.
 * TypeError extends Error, not Exception, so the caller's catch(\Exception)
 * never saw it and one bad thumbnail took down the whole public podcast list
 * for every visitor.
 *
 * Decoding also happened before any dimension check, so a small file declaring
 * huge dimensions could exhaust memory_limit -- a raw fatal that no catch can
 * reach.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmimagelib::class)]
class CwmimagelibResizeTest extends IntegrationTestCase
{
    private string $dir = '';

    /** @var string[] */
    private array $created = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (!\extension_loaded('gd')) {
            $this->markTestSkipped('GD is not available');
        }

        $this->dir = JPATH_ROOT . '/images/biblestudy/cwm1577';

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
            $this->created[] = $this->dir;
        }
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            @unlink($file);
        }

        foreach ($this->created as $dir) {
            @rmdir($dir);
        }

        parent::tearDown();
    }

    /**
     * Write a real PNG.
     *
     * @param   string  $path    Destination
     * @param   int     $width   Width
     * @param   int     $height  Height
     *
     * @return  void
     */
    private function writePng(string $path, int $width = 200, int $height = 150): void
    {
        $im = imagecreatetruecolor($width, $height);
        imagefilledrectangle($im, 0, 0, $width, $height, imagecolorallocate($im, 10, 120, 200));
        imagepng($im, $path);
    }

    /**
     * Write a PNG whose header survives but whose data stream is cut short.
     *
     * @param   string  $path  Destination
     *
     * @return  void
     */
    private function writeTruncatedPng(string $path): void
    {
        $full = $this->dir . '/source-for-truncation.png';
        $this->writePng($full);

        $bytes = (string) file_get_contents($full);
        file_put_contents($path, substr($bytes, 0, (int) (\strlen($bytes) * 0.6)));

        @unlink($full);
    }

    // -------------------------------------------------------------------------
    // Finding 1 -- a failed decode must not escape as an Error
    // -------------------------------------------------------------------------

    #[TestDox('A truncated image raises a catchable exception, not a TypeError')]
    public function testTruncatedImageRaisesACatchableException(): void
    {
        $source = $this->dir . '/truncated.png';
        $this->writeTruncatedPng($source);

        // Precondition: this is the shape that fooled the old guard — the
        // header reads fine, the decode does not.
        $this->assertIsArray(@getimagesize($source), 'Precondition: the header still parses');
        $this->assertFalse(@imagecreatefrompng($source), 'Precondition: the data does not decode');

        $ref = new \ReflectionMethod(Cwmimagelib::class, 'resizeImage');

        try {
            $ref->invoke(null, $this->dir . '/out.png', $source);
            $this->fail('Expected the failed decode to raise');
        } catch (\TypeError $e) {
            $this->fail(
                'A TypeError escapes catch(\Exception) and takes the page down — see #1577: ' . $e->getMessage()
            );
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('decode', $e->getMessage());
        }
    }

    /**
     * The behaviour the page actually depends on: one bad thumbnail falls back
     * to the original rather than aborting the request.
     */
    #[TestDox('A corrupted thumbnail falls back to the original image')]
    public function testCorruptedImageFallsBackToTheOriginal(): void
    {
        $relative = 'images/biblestudy/cwm1577/broken.png';
        $this->writeTruncatedPng(JPATH_ROOT . '/' . $relative);

        $result = Cwmimagelib::getSeriesPodcast($relative);

        $this->assertSame(
            $relative,
            $result,
            'One corrupted image crashed the whole public podcast list — see #1577'
        );
    }

    #[TestDox('A valid image is resized and the resized path returned')]
    public function testValidImageIsResized(): void
    {
        $relative = 'images/biblestudy/cwm1577/good.png';
        $this->writePng(JPATH_ROOT . '/' . $relative, 800, 600);

        $result = Cwmimagelib::getSeriesPodcast($relative);

        $this->assertSame('images/biblestudy/cwm1577/good-fit.png', $result);
        $this->assertFileExists(JPATH_ROOT . '/' . $result, 'The resized file should exist on disk');

        [$w, $h] = getimagesize(JPATH_ROOT . '/' . $result);
        $this->assertSame(300, $w, 'Canvas width');
        $this->assertSame(169, $h, 'Canvas height');
    }

    // -------------------------------------------------------------------------
    // Finding 2 -- refuse oversized sources before decoding them
    // -------------------------------------------------------------------------

    /**
     * Decoding is what allocates the memory, so the check has to happen first.
     * Exceeding memory_limit is a raw fatal that no catch can reach.
     */
    #[TestDox('An image beyond the dimension cap is refused before decoding')]
    public function testOversizedImageIsRefusedBeforeDecoding(): void
    {
        $source = $this->dir . '/huge.png';

        // Declare huge dimensions in the header without allocating them: the
        // guard reads getimagesize(), which trusts the header.
        $this->writePng($source, 64, 64);
        $bytes = (string) file_get_contents($source);
        $over  = Cwmimagelib::MAX_SOURCE_DIMENSION + 1000;
        // IHDR width/height are the 4-byte big-endian values at offsets 16 and 20.
        $bytes = substr_replace($bytes, pack('N', $over), 16, 4);
        $bytes = substr_replace($bytes, pack('N', $over), 20, 4);
        file_put_contents($source, $bytes);

        $reported = @getimagesize($source);
        $this->assertSame($over, $reported[0] ?? 0, 'Precondition: the header advertises huge dimensions');

        $ref = new \ReflectionMethod(Cwmimagelib::class, 'resizeImage');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/limit|Refusing/i');

        $ref->invoke(null, $this->dir . '/out.png', $source);
    }

    #[TestDox('An unreadable file is refused rather than decoded')]
    public function testUnreadableFileIsRefused(): void
    {
        $source = $this->dir . '/notanimage.png';
        file_put_contents($source, 'this is not a PNG at all');

        $ref = new \ReflectionMethod(Cwmimagelib::class, 'resizeImage');

        $this->expectException(\RuntimeException::class);

        $ref->invoke(null, $this->dir . '/out.png', $source);
    }
}

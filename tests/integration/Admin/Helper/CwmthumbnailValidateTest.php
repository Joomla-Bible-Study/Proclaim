<?php

/**
 * Integration tests for Cwmthumbnail::validate()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * validate() accepts a real image, and refuses a missing file, a file over the size or
 * dimension limit, a type it doesn't allow, and an extension that doesn't match the
 * content, giving each refusal its reason.
 *
 * @since  10.1.0
 */
#[CoversClass(Cwmthumbnail::class)]
class CwmthumbnailValidateTest extends IntegrationTestCase
{
    /**
     * Temp files created during tests, cleaned up in tearDown.
     *
     * @var string[]
     * @since  10.1.0
     */
    private array $tempFiles = [];

    /**
     * Remove the temporary files the test created.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            $this->removeTempFile($file);
        }

        parent::tearDown();
    }

    /**
     * Create a valid PNG image at a temp path.
     *
     * @param   int  $width   The image width in pixels
     * @param   int  $height  The image height in pixels
     *
     * @return  string  The file's path, recorded for removal after the test
     *
     * @since   10.2.0
     */
    private function createTempPng(int $width = 10, int $height = 10): string
    {
        $img  = imagecreatetruecolor($width, $height);
        $path = sys_get_temp_dir() . '/proclaim_test_' . uniqid() . '.png';
        imagepng($img, $path);
        $this->tempFiles[] = $path;

        return $path;
    }

    /**
     * validate() refuses a file that doesn't exist, with an error saying it was not found.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testValidateReturnsErrorForNonexistentFile(): void
    {
        $result = Cwmthumbnail::validate('/nonexistent/path/image.png');

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('not found', $result['error']);
    }

    /**
     * validate() accepts a small PNG, with no error.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testValidateReturnsValidForGoodImage(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path);

        $this->assertTrue($result['valid']);
        $this->assertNull($result['error']);
    }

    /**
     * validate() refuses a PNG over a 1-byte size limit, saying the size exceeds it.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testValidateReturnsErrorForOversizedFile(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path, maxSizeBytes: 1);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('size exceeds', $result['error']);
    }

    /**
     * validate() refuses a text file as an invalid file type.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testValidateReturnsErrorForDisallowedMimeType(): void
    {
        $path = sys_get_temp_dir() . '/proclaim_test_' . uniqid() . '.txt';
        file_put_contents($path, 'not an image');
        $this->tempFiles[] = $path;

        $result = Cwmthumbnail::validate($path);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Invalid file type', $result['error']);
    }

    /**
     * validate() refuses a PNG saved with a .jpg extension, saying the extension does not
     * match.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testValidateReturnsErrorForMismatchedExtension(): void
    {
        // Create a PNG but save with .jpg extension
        $img  = imagecreatetruecolor(10, 10);
        $path = sys_get_temp_dir() . '/proclaim_test_' . uniqid() . '.jpg';
        imagepng($img, $path);
        $this->tempFiles[] = $path;

        $result = Cwmthumbnail::validate($path);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('extension does not match', $result['error']);
    }

    /**
     * validate() refuses a PNG over a 1-pixel dimension limit, saying the dimensions exceed
     * it.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testValidateReturnsErrorForOversizedDimensions(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path, maxDimension: 1);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('dimensions exceed', $result['error']);
    }

    /**
     * validate() returns an array with a valid key holding a boolean, and an error key.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testValidateReturnsCorrectArrayStructure(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path);

        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsBool($result['valid']);
    }

    /**
     * validate() refuses a PNG as an invalid file type when only JPEG is allowed.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testValidateWithCustomAllowedTypes(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path, allowedTypes: ['image/jpeg']);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Invalid file type', $result['error']);
    }
}

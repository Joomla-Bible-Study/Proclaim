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

#[CoversClass(Cwmthumbnail::class)]
class CwmthumbnailValidateTest extends IntegrationTestCase
{
    /**
     * Temp files created during tests, cleaned up in tearDown.
     *
     * @var string[]
     */
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            $this->removeTempFile($file);
        }

        parent::tearDown();
    }

    /**
     * Create a valid PNG image at a temp path.
     */
    private function createTempPng(int $width = 10, int $height = 10): string
    {
        $img  = imagecreatetruecolor($width, $height);
        $path = sys_get_temp_dir() . '/proclaim_test_' . uniqid() . '.png';
        imagepng($img, $path);
        imagedestroy($img);
        $this->tempFiles[] = $path;

        return $path;
    }

    public function testValidateReturnsErrorForNonexistentFile(): void
    {
        $result = Cwmthumbnail::validate('/nonexistent/path/image.png');

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('not found', $result['error']);
    }

    public function testValidateReturnsValidForGoodImage(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path);

        $this->assertTrue($result['valid']);
        $this->assertNull($result['error']);
    }

    public function testValidateReturnsErrorForOversizedFile(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path, maxSizeBytes: 1);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('size exceeds', $result['error']);
    }

    public function testValidateReturnsErrorForDisallowedMimeType(): void
    {
        $path = sys_get_temp_dir() . '/proclaim_test_' . uniqid() . '.txt';
        file_put_contents($path, 'not an image');
        $this->tempFiles[] = $path;

        $result = Cwmthumbnail::validate($path);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Invalid file type', $result['error']);
    }

    public function testValidateReturnsErrorForMismatchedExtension(): void
    {
        // Create a PNG but save with .jpg extension
        $img  = imagecreatetruecolor(10, 10);
        $path = sys_get_temp_dir() . '/proclaim_test_' . uniqid() . '.jpg';
        imagepng($img, $path);
        imagedestroy($img);
        $this->tempFiles[] = $path;

        $result = Cwmthumbnail::validate($path);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('extension does not match', $result['error']);
    }

    public function testValidateReturnsErrorForOversizedDimensions(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path, maxDimension: 1);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('dimensions exceed', $result['error']);
    }

    public function testValidateReturnsCorrectArrayStructure(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path);

        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsBool($result['valid']);
    }

    public function testValidateWithCustomAllowedTypes(): void
    {
        $path   = $this->createTempPng();
        $result = Cwmthumbnail::validate($path, allowedTypes: ['image/jpeg']);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Invalid file type', $result['error']);
    }
}

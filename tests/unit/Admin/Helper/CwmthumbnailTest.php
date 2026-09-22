<?php

/**
 * Unit tests for Cwmthumbnail Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmthumbnail helper
 *
 *#[CoversClass(Cwmthumbnail::class)]
 * @since  10.0.0
 */
class CwmthumbnailTest extends ProclaimTestCase
{
    /**
     * Test that the SCALE_INSIDE constant is defined correctly
     *
     * @return void
     *#[CoversClass(Cwmthumbnail::class)]::SCALE_INSIDE
     */
    public function testScaleInsideConstant(): void
    {
        $this->assertEquals(2, Cwmthumbnail::SCALE_INSIDE);
    }

    /**
     * Test check method returns false for non-existent directory
     *
     * @return void
     *#[CoversClass(Cwmthumbnail::class)]::check
     */
    public function testCheckReturnsFalseForNonExistentDirectory(): void
    {
        $result = Cwmthumbnail::check('/non/existent/path');

        $this->assertFalse($result);
    }

    /**
     * Test check method returns true for existing directory
     *
     * @return void
     *#[CoversClass(Cwmthumbnail::class)]::check
     */
    public function testCheckReturnsTrueForExistingDirectory(): void
    {
        $result = Cwmthumbnail::check(sys_get_temp_dir());

        $this->assertTrue($result);
    }

    /**
     * Test check method with file parameter returns false for non-existent file
     *
     * @return void
     *#[CoversClass(Cwmthumbnail::class)]::check
     */
    public function testCheckReturnsFalseForNonExistentFile(): void
    {
        $result = Cwmthumbnail::check(sys_get_temp_dir(), '/non_existent_file.jpg');

        $this->assertFalse($result);
    }

    /**
     * @return  void
     */
    #[\PHPUnit\Framework\Attributes\TestDox('JPEG output is named .jpg, not .jpeg')]
    public function testJpegOutputIsNamedJpg(): void
    {
        $this->assertSame('jpg', Cwmthumbnail::extensionForType(IMAGETYPE_JPEG), 'Every other path writes .jpg; JPEG must match.');
    }

    /**
     * @return  void
     */
    #[\PHPUnit\Framework\Attributes\TestDox('Other image types keep their own extension')]
    public function testOtherTypesKeepTheirExtension(): void
    {
        $this->assertSame('png', Cwmthumbnail::extensionForType(IMAGETYPE_PNG));
        $this->assertSame('gif', Cwmthumbnail::extensionForType(IMAGETYPE_GIF));
        $this->assertSame('webp', Cwmthumbnail::extensionForType(IMAGETYPE_WEBP));
    }
}

<?php

/**
 * Unit tests for Cwmthumbnail Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail;

/**
 * Test class for Cwmthumbnail helper
 *
 * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail
 * @since  10.0.0
 */
class CwmthumbnailTest extends ProclaimTestCase
{
    /**
     * Test that the SCALE_INSIDE constant is defined correctly
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail::SCALE_INSIDE
     */
    public function testScaleInsideConstant(): void
    {
        $this->assertEquals(2, Cwmthumbnail::SCALE_INSIDE);
    }

    /**
     * Test check method returns false for non-existent directory
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail::check
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
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail::check
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
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmthumbnail::check
     */
    public function testCheckReturnsFalseForNonExistentFile(): void
    {
        $result = Cwmthumbnail::check(sys_get_temp_dir(), '/non_existent_file.jpg');

        $this->assertFalse($result);
    }
}
<?php

/**
 * Unit tests for Cwmmedia Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmmedia;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmmedia helper
 *
 * @since  10.0.0
 */
class CwmmediaTest extends ProclaimTestCase
{
    /**
     * Test isExternal method
     *
     * @return void
     */
    public function testIsExternal(): void
    {
        // Test absolute URLs
        $this->assertTrue(Cwmmedia::isExternal('http://google.com/image.jpg'));
        $this->assertTrue(Cwmmedia::isExternal('https://example.com/file.mp3'));

        // Test relative URLs (should be false)
        $this->assertFalse(Cwmmedia::isExternal('images/local.jpg'));
        $this->assertFalse(Cwmmedia::isExternal('/images/local.jpg'));
    }
}

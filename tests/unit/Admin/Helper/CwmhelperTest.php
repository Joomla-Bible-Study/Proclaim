<?php

/**
 * Unit tests for Cwmhelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmhelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;

/**
 * Test class for Cwmhelper
 *
 * @since  10.0.0
 */
class CwmhelperTest extends ProclaimTestCase
{
    /**
     * Test extension name constant
     *
     * @return void
     */
    public function testExtensionNameIsCorrect(): void
    {
        $this->assertEquals('com_proclaim', Cwmhelper::$extension);
    }

    /**
     * Test getRemoteFileSize returns 0 for empty URL
     *
     * @return void
     */
    public function testGetRemoteFileSizeReturnsZeroForEmptyUrl(): void
    {
        $result = Cwmhelper::getRemoteFileSize('');
        $this->assertEquals(0, $result);
    }

    /**
     * Test getRemoteFileSize returns 0 for YouTube URLs
     *
     * @return void
     */
    public function testGetRemoteFileSizeReturnsZeroForYoutubeUrls(): void
    {
        $this->assertEquals(0, Cwmhelper::getRemoteFileSize('https://youtu.be/abc123'));
        $this->assertEquals(0, Cwmhelper::getRemoteFileSize('https://youtube.com/watch?v=abc123'));
    }

    /**
     * Test removeHttp removes http protocol
     *
     * @return void
     */
    public function testRemoveHttpRemovesHttpProtocol(): void
    {
        $this->assertEquals('example.com/path', Cwmhelper::removeHttp('http://example.com/path'));
    }

    /**
     * Test removeHttp removes https protocol
     *
     * @return void
     */
    public function testRemoveHttpRemovesHttpsProtocol(): void
    {
        $this->assertEquals('example.com/path', Cwmhelper::removeHttp('https://example.com/path'));
    }

    /**
     * Test removeHttp leaves non-http URLs unchanged
     *
     * @return void
     */
    public function testRemoveHttpLeavesNonHttpUrlsUnchanged(): void
    {
        $this->assertEquals('ftp://example.com', Cwmhelper::removeHttp('ftp://example.com'));
        $this->assertEquals('example.com', Cwmhelper::removeHttp('example.com'));
    }

    /**
     * The indicator is what tells an administrator the missing fields are a
     * setting rather than a fault, so an unconfigured site has to get it. It
     * used to cast an absent param to 0 and stay silent — the same state that
     * made a Simple Mode message form read as a broken editor.
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    public function testSimpleModeIndicatorDefaultsOnWhenUnset(): void
    {
        $simple = Cwmhelper::getSimpleView(new Registry(['simple_mode' => 1]));

        $this->assertSame(1, $simple->mode);
        $this->assertSame(1, $simple->display, 'An unset display param must not silence the indicator');
    }

    /**
     * Turning it off explicitly still has to work — the default is for the
     * absent case only.
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    public function testSimpleModeIndicatorRespectsAnExplicitOff(): void
    {
        $simple = Cwmhelper::getSimpleView(new Registry(['simple_mode' => 1, 'simple_mode_display' => 0]));

        $this->assertSame(0, $simple->display, 'An explicit 0 must still turn the indicator off');
    }

    /**
     * These params have been seen stored as an empty string, which Registry
     * treats as set and so would not fall back.
     *
     * @return  void
     * @since   __DEPLOY_VERSION__
     */
    public function testSimpleModeIndicatorTreatsAnEmptyStringAsUnset(): void
    {
        $simple = Cwmhelper::getSimpleView(new Registry(['simple_mode' => 1, 'simple_mode_display' => '']));

        $this->assertSame(1, $simple->display, 'An empty string is not an explicit "no"');
    }
}

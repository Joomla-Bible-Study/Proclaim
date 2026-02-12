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
}

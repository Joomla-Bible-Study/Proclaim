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
     * Test getRemoteFileSize method signature
     *
     * @return void
     */
    public function testGetRemoteFileSizeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmhelper::class, 'getRemoteFileSize');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('url', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
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
     * Test removeHttp method signature
     *
     * @return void
     */
    public function testRemoveHttpMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmhelper::class, 'removeHttp');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('url', $params[0]->getName());
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
     * Test mediaBuildUrl method exists
     *
     * @return void
     */
    public function testMediaBuildUrlMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmhelper::class, 'mediaBuildUrl'));
    }

    /**
     * Test clearCache method signature
     *
     * @return void
     */
    public function testClearCacheMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmhelper::class, 'clearCache');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }

    /**
     * Test getSimpleView method signature
     *
     * @return void
     */
    public function testGetSimpleViewMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmhelper::class, 'getSimpleView');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertTrue($params[0]->allowsNull());
    }

    /**
     * Test setFileSize method signature
     *
     * @return void
     */
    public function testSetFileSizeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmhelper::class, 'setFileSize');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
        $this->assertEquals('size', $params[1]->getName());
        $this->assertEquals('int', $params[1]->getType()->getName());
    }
}

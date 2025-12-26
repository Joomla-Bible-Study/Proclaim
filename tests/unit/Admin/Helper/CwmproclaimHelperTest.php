<?php

/**
 * Unit tests for CwmproclaimHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;

/**
 * Test class for CwmproclaimHelper
 *
 * @since  10.0.0
 */
class CwmproclaimHelperTest extends ProclaimTestCase
{
    /**
     * Test extension name constant
     *
     * @return void
     */
    public function testExtensionNameIsCorrect(): void
    {
        $this->assertEquals('com_proclaim', CwmproclaimHelper::$extension);
    }

    /**
     * Test admin_params default is null
     *
     * @return void
     */
    public function testAdminParamsDefaultIsNull(): void
    {
        $this->assertNull(CwmproclaimHelper::$admin_params);
    }

    /**
     * Test applyViewAndController method exists
     *
     * @return void
     */
    public function testApplyViewAndControllerMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmproclaimHelper::class, 'applyViewAndController');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('defaultController', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    /**
     * Test addSubmenu method exists
     *
     * @return void
     */
    public function testAddSubmenuMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmproclaimHelper::class, 'addSubmenu');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('vName', $params[0]->getName());
    }

    /**
     * Test rendermenu method exists
     *
     * @return void
     */
    public function testRendermenuMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmproclaimHelper::class, 'rendermenu');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('text', $params[0]->getName());
        $this->assertEquals('url', $params[1]->getName());
        $this->assertEquals('vName', $params[2]->getName());
    }

    /**
     * Test filterText method signature
     *
     * @return void
     */
    public function testFilterTextMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmproclaimHelper::class, 'filterText');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('text', $params[0]->getName());
    }

    /**
     * Test debug method signature
     *
     * @return void
     */
    public function testDebugMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmproclaimHelper::class, 'debug');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    /**
     * Test arraySortByColumn method signature
     *
     * @return void
     */
    public function testArraySortByColumnMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmproclaimHelper::class, 'arraySortByColumn');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('arr', $params[0]->getName());
        $this->assertTrue($params[0]->isPassedByReference());
    }

    /**
     * Test arraySortByColumn sorts array correctly
     *
     * @return void
     */
    public function testArraySortByColumnSortsCorrectly(): void
    {
        $arr = [
            ['name' => 'Charlie', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 35],
        ];

        CwmproclaimHelper::arraySortByColumn($arr, 'name');

        $this->assertEquals('Alice', $arr[0]['name']);
        $this->assertEquals('Bob', $arr[1]['name']);
        $this->assertEquals('Charlie', $arr[2]['name']);
    }

    /**
     * Test startsWith method (deprecated but still available)
     *
     * @return void
     */
    public function testStartsWithMethod(): void
    {
        $this->assertTrue(CwmproclaimHelper::startsWith('Hello World', 'Hello'));
        $this->assertFalse(CwmproclaimHelper::startsWith('Hello World', 'World'));
        $this->assertTrue(CwmproclaimHelper::startsWith('Hello', ''));
    }

    /**
     * Test endsWith method (deprecated but still available)
     *
     * @return void
     */
    public function testEndsWithMethod(): void
    {
        $this->assertTrue(CwmproclaimHelper::endsWith('Hello World', 'World'));
        $this->assertFalse(CwmproclaimHelper::endsWith('Hello World', 'Hello'));
        $this->assertTrue(CwmproclaimHelper::endsWith('Hello', ''));
    }

    /**
     * Test halfarray method
     *
     * @return void
     */
    public function testHalfarrayMethod(): void
    {
        $array = [1, 2, 3, 4, 5, 6];
        $result = CwmproclaimHelper::halfarray($array);

        $this->assertIsObject($result);
        $this->assertEquals(3, $result->half);
        $this->assertEquals(6, $result->count);
    }

    /**
     * Test halfarray with odd count
     *
     * @return void
     */
    public function testHalfarrayWithOddCount(): void
    {
        $array = [1, 2, 3, 4, 5];
        $result = CwmproclaimHelper::halfarray($array);

        $this->assertEquals(2, $result->half);
        $this->assertEquals(5, $result->count);
    }
}
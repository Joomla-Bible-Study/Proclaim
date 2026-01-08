<?php

/**
 * Unit tests for CwmdbHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmdbHelper
 *
 * @since  10.0.0
 */
class CwmdbHelperTest extends ProclaimTestCase
{
    /**
     * Test extension name constant
     *
     * @return void
     */
    public function testExtensionNameIsCorrect(): void
    {
        $this->assertEquals('com_proclaim', CwmdbHelper::$extension);
    }

    /**
     * Test install_state default value
     *
     * @return void
     */
    public function testInstallStateDefaultIsFalse(): void
    {
        $this->assertFalse(CwmdbHelper::$install_state);
    }

    /**
     * Test checkIfTable method exists and has correct signature
     *
     * @return void
     */
    public function testCheckIfTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdbHelper::class, 'checkIfTable');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('cktable', $params[0]->getName());
    }

    /**
     * Test alterDB method exists and has correct signature
     *
     * @return void
     */
    public function testAlterDBMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdbHelper::class, 'alterDB');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('tables', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
        $this->assertEquals('from', $params[1]->getName());
        $this->assertTrue($params[1]->allowsNull());
    }

    /**
     * Test checkTables method exists and has correct signature
     *
     * @return void
     */
    public function testCheckTablesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdbHelper::class, 'checkTables');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('table', $params[0]->getName());
        $this->assertEquals('field', $params[1]->getName());
    }

    /**
     * Test performDB method exists and has correct signature
     *
     * @return void
     */
    public function testPerformDBMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdbHelper::class, 'performDB');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('query', $params[0]->getName());
        $this->assertEquals('from', $params[1]->getName());
        $this->assertEquals('limit', $params[2]->getName());
    }

    /**
     * Test getObjects method exists and returns array
     *
     * @return void
     */
    public function testGetObjectsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdbHelper::class, 'getObjects');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test getInstallState method exists and returns bool
     *
     * @return void
     */
    public function testGetInstallStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdbHelper::class, 'getInstallState');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    /**
     * Test resetdb method exists
     *
     * @return void
     */
    public function testResetdbMethodExists(): void
    {
        $this->assertTrue(method_exists(CwmdbHelper::class, 'resetdb'));
    }

    /**
     * Test cleanStudyTopics method exists
     *
     * @return void
     */
    public function testCleanStudyTopicsMethodExists(): void
    {
        $reflection = new \ReflectionMethod(CwmdbHelper::class, 'cleanStudyTopics');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}

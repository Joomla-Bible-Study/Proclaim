<?php

/**
 * Unit tests for Cwmassets
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmassets
 *
 * @since  10.0.0
 */
class CwmassetsTest extends ProclaimTestCase
{
    /**
     * Test parent_id default value
     *
     * @return void
     */
    public function testParentIdDefaultIsZero(): void
    {
        $this->assertEquals(0, Cwmassets::$parent_id);
    }

    /**
     * Test query default value is empty array
     *
     * @return void
     */
    public function testQueryDefaultIsEmptyArray(): void
    {
        $this->assertIsArray(Cwmassets::$query);
    }

    /**
     * Test count default value is zero
     *
     * @return void
     */
    public function testCountDefaultIsZero(): void
    {
        $this->assertEquals(0, Cwmassets::$count);
    }

    /**
     * Test fixAssets method signature
     *
     * @return void
     */
    public function testFixAssetsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmassets::class, 'fixAssets');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertEquals('result', $params[1]->getName());
    }

    /**
     * Test parentId method signature
     *
     * @return void
     */
    public function testParentIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmassets::class, 'parentId');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    /**
     * Test build method exists and returns object
     *
     * @return void
     */
    public function testBuildMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmassets::class, 'build');

        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('object', $reflection->getReturnType()->getName());
    }

    /**
     * Test getAssetObjects method returns array
     *
     * @return void
     */
    public function testGetAssetObjectsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmassets::class, 'getAssetObjects');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test getAssetObjects returns expected structure
     *
     * @return void
     */
    public function testGetAssetObjectsReturnsExpectedStructure(): void
    {
        $objects = Cwmassets::getAssetObjects();

        $this->assertIsArray($objects);
        $this->assertNotEmpty($objects);

        // Check first element has expected keys
        $first = $objects[0];
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('titlefield', $first);
        $this->assertArrayHasKey('assetname', $first);
        $this->assertArrayHasKey('realname', $first);
    }

    /**
     * Test getAssetObjects includes servers table
     *
     * @return void
     */
    public function testGetAssetObjectsIncludesServers(): void
    {
        $objects = Cwmassets::getAssetObjects();
        $names   = array_column($objects, 'name');

        $this->assertContains('#__bsms_servers', $names);
    }

    /**
     * Test getAssetObjects includes studies table
     *
     * @return void
     */
    public function testGetAssetObjectsIncludesStudies(): void
    {
        $objects = Cwmassets::getAssetObjects();
        $names   = array_column($objects, 'name');

        $this->assertContains('#__bsms_studies', $names);
    }

    /**
     * Test getAssetObjects includes teachers table
     *
     * @return void
     */
    public function testGetAssetObjectsIncludesTeachers(): void
    {
        $objects = Cwmassets::getAssetObjects();
        $names   = array_column($objects, 'name');

        $this->assertContains('#__bsms_teachers', $names);
    }

    /**
     * Test getAssetObjects includes series table
     *
     * @return void
     */
    public function testGetAssetObjectsIncludesSeries(): void
    {
        $objects = Cwmassets::getAssetObjects();
        $names   = array_column($objects, 'name');

        $this->assertContains('#__bsms_series', $names);
    }
}

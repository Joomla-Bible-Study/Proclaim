<?php

/**
 * Unit tests for Cwmassets
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
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
     * Test fixSingleRecord method exists with correct signature
     *
     * @return void
     */
    public function testFixSingleRecordMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmassets::class, 'fixSingleRecord'));

        $method = new \ReflectionMethod(Cwmassets::class, 'fixSingleRecord');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertReturnTypeName('bool', $method);
    }

    /**
     * Test fixAllAssets method exists
     *
     * @return void
     */
    public function testFixAllAssetsMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmassets::class, 'fixAllAssets'));

        $method = new \ReflectionMethod(Cwmassets::class, 'fixAllAssets');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test cleanOrphanedAssets method exists
     *
     * @return void
     */
    public function testCleanOrphanedAssetsMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmassets::class, 'cleanOrphanedAssets'));

        $method = new \ReflectionMethod(Cwmassets::class, 'cleanOrphanedAssets');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
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

<?php

/**
 * Unit tests for Cwmalias Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmalias;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmalias helper
 *
 * @since  10.0.0
 */
#[CoversClass(Cwmalias::class)]
class CwmaliasTest extends ProclaimTestCase
{
    /**
     * Test extension name constant
     *
     * @return void
     */
    public function testExtensionNameIsCorrect(): void
    {
        $this->assertEquals('com_proclaim', Cwmalias::$extension);
    }

    /**
     * Test getObjects method returns expected table structure
     *
     * @return void
     */
    public function testGetObjectsReturnsExpectedStructure(): void
    {
        // Use reflection to access private method
        $objects = $this->invokeProtectedMethod(new Cwmalias(), 'getObjects');

        $this->assertIsArray($objects);
        $this->assertNotEmpty($objects);

        // Check that each object has required keys
        foreach ($objects as $object) {
            $this->assertArrayHasKey('name', $object);
            $this->assertArrayHasKey('titlefield', $object);
            $this->assertStringStartsWith('#__bsms_', $object['name']);
        }
    }

    /**
     * Test getObjects method includes series table
     *
     * @return void
     */
    public function testGetObjectsIncludesSeriesTable(): void
    {
        $objects = $this->invokeProtectedMethod(new Cwmalias(), 'getObjects');

        $tableNames = array_column($objects, 'name');

        $this->assertContains('#__bsms_series', $tableNames);
    }

    /**
     * Test getObjects method includes studies table
     *
     * @return void
     */
    public function testGetObjectsIncludesStudiesTable(): void
    {
        $objects = $this->invokeProtectedMethod(new Cwmalias(), 'getObjects');

        $tableNames = array_column($objects, 'name');

        $this->assertContains('#__bsms_studies', $tableNames);
    }

    /**
     * Test getObjects method includes teachers table
     *
     * @return void
     */
    public function testGetObjectsIncludesTeachersTable(): void
    {
        $objects = $this->invokeProtectedMethod(new Cwmalias(), 'getObjects');

        $tableNames = array_column($objects, 'name');

        $this->assertContains('#__bsms_teachers', $tableNames);
    }

    /**
     * Test getObjects method includes message_type table
     *
     * @return void
     */
    public function testGetObjectsIncludesMessageTypeTable(): void
    {
        $objects = $this->invokeProtectedMethod(new Cwmalias(), 'getObjects');

        $tableNames = array_column($objects, 'name');

        $this->assertContains('#__bsms_message_type', $tableNames);
    }

    /**
     * Test getTableQuery returns false for empty table name
     *
     * @return void
     */
    public function testGetTableQueryReturnsFalseForEmptyTable(): void
    {
        $result = $this->invokeProtectedMethod(new Cwmalias(), 'getTableQuery', ['', 'title']);

        $this->assertFalse($result);
    }
}

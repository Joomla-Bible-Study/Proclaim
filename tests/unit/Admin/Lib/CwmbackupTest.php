<?php

/**
 * Unit tests for Cwmbackup
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmbackup
 *
 * @since  10.0.0
 */
class CwmbackupTest extends ProclaimTestCase
{
    /**
     * Test class file exists
     *
     * @return void
     */
    public function testClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Lib/Cwmbackup.php';
        $this->assertFileExists($filePath);
    }

    /**
     * Test class has correct namespace
     *
     * @return void
     */
    public function testClassHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Lib/Cwmbackup.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Administrator\Lib;',
            $content
        );
    }

    /**
     * Test exportdb method exists
     *
     * @return void
     */
    public function testExportdbMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmbackup::class, 'exportdb'));
    }

    /**
     * Test exportdb method signature
     *
     * @return void
     */
    public function testExportdbMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmbackup::class, 'exportdb');

        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test getExportTable method exists
     *
     * @return void
     */
    public function testGetExportTableMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmbackup::class, 'getExportTable'));
    }

    /**
     * Test outputFile method exists
     *
     * @return void
     */
    public function testOutputFileMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmbackup::class, 'outputFile'));
    }

    /**
     * Test class can be instantiated
     *
     * @return void
     */
    public function testClassCanBeInstantiated(): void
    {
        $reflection = new \ReflectionClass(Cwmbackup::class);
        $this->assertTrue($reflection->isInstantiable());
    }
}

<?php

/**
 * Unit tests for Cwmstats
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmstats;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmstats
 *
 * @since  10.0.0
 */
class CwmstatsTest extends ProclaimTestCase
{
    /**
     * Test class file exists
     *
     * @return void
     */
    public function testClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Lib/Cwmstats.php';
        $this->assertFileExists($filePath);
    }

    /**
     * Test class has correct namespace
     *
     * @return void
     */
    public function testClassHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Lib/Cwmstats.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Administrator\Lib;',
            $content
        );
    }

    /**
     * Test totalPlays method exists
     *
     * @return void
     */
    public function testTotalPlaysMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmstats::class, 'totalPlays'));
    }

    /**
     * Test totalPlays method signature
     *
     * @return void
     */
    public function testTotalPlaysMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmstats::class, 'totalPlays');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    /**
     * Test getTotalDownloads method exists
     *
     * @return void
     */
    public function testGetTotalDownloadsMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmstats::class, 'getTotalDownloads'));
    }

    /**
     * Test getTotalDownloads method signature
     *
     * @return void
     */
    public function testGetTotalDownloadsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmstats::class, 'getTotalDownloads');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    /**
     * Test getTotalMessages method exists
     *
     * @return void
     */
    public function testGetTotalMessagesMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmstats::class, 'getTotalMessages'));
    }

    /**
     * Test class can be instantiated
     *
     * @return void
     */
    public function testClassCanBeInstantiated(): void
    {
        $reflection = new \ReflectionClass(Cwmstats::class);
        $this->assertTrue($reflection->isInstantiable());
    }
}

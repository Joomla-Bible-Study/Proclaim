<?php

/**
 * Unit tests for Cwmhelperroute
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Component\Proclaim\Site\Helper\Cwmhelperroute;

/**
 * Test class for Cwmhelperroute
 *
 * @since  10.0.0
 */
class CwmhelperrouteTest extends ProclaimTestCase
{
    /**
     * Test class file exists
     *
     * @return void
     */
    public function testClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/site/src/Helper/Cwmhelperroute.php';
        $this->assertFileExists($filePath);
    }

    /**
     * Test class has correct namespace
     *
     * @return void
     */
    public function testClassHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/site/src/Helper/Cwmhelperroute.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Site\Helper;',
            $content
        );
    }

    /**
     * Test getArticleRoute method exists
     *
     * @return void
     */
    public function testGetArticleRouteMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmhelperroute::class, 'getArticleRoute'));
    }

    /**
     * Test getArticleRoute method signature
     *
     * @return void
     */
    public function testGetArticleRouteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmhelperroute::class, 'getArticleRoute');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());
    }

    /**
     * Test getSeriesRoute method exists
     *
     * @return void
     */
    public function testGetSeriesRouteMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmhelperroute::class, 'getSeriesRoute'));
    }

    /**
     * Test getTeacherRoute method exists
     *
     * @return void
     */
    public function testGetTeacherRouteMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmhelperroute::class, 'getTeacherRoute'));
    }

    /**
     * Test class uses Joomla Factory
     *
     * @return void
     */
    public function testClassUsesJoomlaFactory(): void
    {
        $filePath = JPATH_ROOT . '/site/src/Helper/Cwmhelperroute.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('use Joomla\CMS\Factory;', $content);
    }

    /**
     * Test addScheme method exists
     *
     * @return void
     */
    public function testAddSchemeMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmhelperroute::class, 'addScheme'));
    }
}
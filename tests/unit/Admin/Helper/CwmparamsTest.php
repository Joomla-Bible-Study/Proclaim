<?php

/**
 * Unit tests for Cwmparams Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;

/**
 * Test class for Cwmparams helper
 *
 * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmparams
 * @since  10.0.0
 */
class CwmparamsTest extends ProclaimTestCase
{
    /**
     * Reset static properties between tests
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Reset static properties using reflection
        $reflection = new \ReflectionClass(Cwmparams::class);

        if ($reflection->hasProperty('templateId')) {
            $prop = $reflection->getProperty('templateId');
            $prop->setAccessible(true);
            $prop->setValue(null, 1);
        }

        parent::tearDown();
    }

    /**
     * Test extension name constant
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmparams::$extension
     */
    public function testExtensionNameIsCorrect(): void
    {
        $this->assertEquals('com_proclaim', Cwmparams::$extension);
    }

    /**
     * Test default template ID
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmparams::$templateId
     */
    public function testDefaultTemplateIdIsOne(): void
    {
        $this->assertEquals(1, Cwmparams::$templateId);
    }

    /**
     * Test setCompParams method signature accepts array
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmparams::setCompParams
     */
    public function testSetCompParamsAcceptsArrayParameter(): void
    {
        $reflection = new \ReflectionMethod(Cwmparams::class, 'setCompParams');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('paramArray', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
    }

    /**
     * Test getTemplateparams method signature
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmparams::getTemplateparams
     */
    public function testGetTemplateparamsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmparams::class, 'getTemplateparams');
        $params = $reflection->getParameters();

        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getAdmin method signature
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmparams::getAdmin
     */
    public function testGetAdminMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmparams::class, 'getAdmin');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('object', $reflection->getReturnType()->getName());
    }

    /**
     * Test setCompParams is a void method
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmparams::setCompParams
     */
    public function testSetCompParamsReturnsVoid(): void
    {
        $reflection = new \ReflectionMethod(Cwmparams::class, 'setCompParams');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }

    /**
     * Test getTemplateparams returns object
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Helper\Cwmparams::getTemplateparams
     */
    public function testGetTemplateparamsReturnsObject(): void
    {
        $reflection = new \ReflectionMethod(Cwmparams::class, 'getTemplateparams');
        $returnType = $reflection->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('object', $returnType->getName());
    }
}

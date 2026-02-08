<?php

/**
 * Unit tests for Cwmpagebuilder Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmpagebuilder;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmpagebuilder helper
 *
 * #[CoversClass(Cwmpagebuilder::class)]
 * @since  10.0.0
 */
class CwmpagebuilderTest extends ProclaimTestCase
{
    /**
     * Test extension name property
     *
     * @return void
     * #[CoversClass(Cwmpagebuilder::class)]::$extension
     */
    public function testExtensionNameIsCorrect(): void
    {
        $builder = new Cwmpagebuilder();
        $this->assertEquals('com_proclaim', $builder->extension);
    }

    /**
     * Test buildPage method signature
     *
     * @return void
     * #[CoversClass(Cwmpagebuilder::class)]::buildPage
     */
    public function testBuildPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpagebuilder::class, 'buildPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('object', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('item', $params[0]->getName());
        // No type hint in method signature for item
        
        $this->assertEquals('params', $params[1]->getName());
        // No type hint in method signature for params
        
        $this->assertEquals('template', $params[2]->getName());
        // No type hint in method signature for template
    }

    /**
     * Test studyBuilder method signature
     *
     * @return void
     * #[CoversClass(Cwmpagebuilder::class)]::studyBuilder
     */
    public function testStudyBuilderMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpagebuilder::class, 'studyBuilder');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(6, $params);
        $this->assertEquals('whereitem', $params[0]->getName());
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('wherefield', $params[1]->getName());
        $this->assertTrue($params[1]->allowsNull());
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('params', $params[2]->getName());
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
        
        $this->assertEquals('limit', $params[3]->getName());
        $this->assertTrue($params[3]->isOptional());
        
        $this->assertEquals('order', $params[4]->getName());
        $this->assertTrue($params[4]->isOptional());
        
        $this->assertEquals('template', $params[5]->getName());
        $this->assertTrue($params[5]->allowsNull());
        $this->assertTrue($params[5]->isOptional());
    }

    /**
     * Test runContentPlugins method signature
     *
     * @return void
     * #[CoversClass(Cwmpagebuilder::class)]::runContentPlugins
     */
    public function testRunContentPluginsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmpagebuilder::class, 'runContentPlugins');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('object', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('item', $params[0]->getName());
        $this->assertEquals('object', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('object', $params[1]->getType()->getName());
    }
}

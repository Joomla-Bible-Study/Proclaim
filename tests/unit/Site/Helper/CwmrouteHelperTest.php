<?php

/**
 * Unit tests for CwmrouteHelper Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\CwmrouteHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmrouteHelper helper
 *
 * #[CoversClass(CwmrouteHelper::class)]
 * @since  10.0.0
 */
class CwmrouteHelperTest extends ProclaimTestCase
{
    /**
     * Test getMessageRoute method signature
     *
     * @return void
     * #[CoversClass(CwmrouteHelper::class)]::getMessageRoute
     */
    public function testGetMessageRouteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmrouteHelper::class, 'getMessageRoute');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
        
        $this->assertEquals('language', $params[1]->getName());
        // Type is int|string, which reflection might show differently
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('layout', $params[2]->getName());
        $this->assertEquals('string', $params[2]->getType()->getName());
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getSeriesRoute method signature
     *
     * @return void
     * #[CoversClass(CwmrouteHelper::class)]::getSeriesRoute
     */
    public function testGetSeriesRouteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmrouteHelper::class, 'getSeriesRoute');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('seriesid', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
        
        $this->assertEquals('language', $params[1]->getName());
        // Type is int|string, which reflection might show differently
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('layout', $params[2]->getName());
        $this->assertEquals('string', $params[2]->getType()->getName());
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getLocationsRoute method signature
     *
     * @return void
     * #[CoversClass(CwmrouteHelper::class)]::getLocationsRoute
     */
    public function testGetLocationsRouteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmrouteHelper::class, 'getLocationsRoute');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('seriesid', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
        
        $this->assertEquals('language', $params[1]->getName());
        // Type is int|string, which reflection might show differently
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('layout', $params[2]->getName());
        $this->assertEquals('string', $params[2]->getType()->getName());
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getTeachersRoute method signature
     *
     * @return void
     * #[CoversClass(CwmrouteHelper::class)]::getTeachersRoute
     */
    public function testGetTeachersRouteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmrouteHelper::class, 'getTeachersRoute');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
        
        $this->assertEquals('language', $params[1]->getName());
        // Type is int|string, which reflection might show differently
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('layout', $params[2]->getName());
        $this->assertEquals('string', $params[2]->getType()->getName());
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getFormRoute method signature
     *
     * @return void
     * #[CoversClass(CwmrouteHelper::class)]::getFormRoute
     */
    public function testGetFormRouteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmrouteHelper::class, 'getFormRoute');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
    }
}

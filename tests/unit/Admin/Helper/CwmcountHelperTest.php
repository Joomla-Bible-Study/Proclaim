<?php

/**
 * Unit tests for CwmcountHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmcountHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmcountHelper
 *
 * #[CoversClass(CwmcountHelper::class)]
 * @since  10.1.0
 */
class CwmcountHelperTest extends ProclaimTestCase
{
    /**
     * Reset static properties between tests
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Reset static properties using reflection
        $reflection = new \ReflectionClass(CwmcountHelper::class);

        if ($reflection->hasProperty('cache')) {
            $prop = $reflection->getProperty('cache');
            $prop->setAccessible(true);
            $prop->setValue(null, []);
        }

        parent::tearDown();
    }

    /**
     * Test getCountByState method signature
     *
     * @return void
     * #[CoversClass(CwmcountHelper::class)]::getCountByState
     */
    public function testGetCountByStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcountHelper::class, 'getCountByState');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('tableName', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        
        $this->assertEquals('state', $params[1]->getName());
        $this->assertEquals('int', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
        $this->assertEquals(1, $params[1]->getDefaultValue());
    }

    /**
     * Test getTotalCount method signature
     *
     * @return void
     * #[CoversClass(CwmcountHelper::class)]::getTotalCount
     */
    public function testGetTotalCountMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcountHelper::class, 'getTotalCount');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('tableName', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    /**
     * Test sendQuickIconResponse method signature
     *
     * @return void
     * #[CoversClass(CwmcountHelper::class)]::sendQuickIconResponse
     */
    public function testSendQuickIconResponseMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcountHelper::class, 'sendQuickIconResponse');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('tableName', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        
        $this->assertEquals('langKey', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
    }
}

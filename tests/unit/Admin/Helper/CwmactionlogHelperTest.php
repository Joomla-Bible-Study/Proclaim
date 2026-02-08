<?php

/**
 * Unit tests for CwmactionlogHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmactionlogHelper
 *
 * #[CoversClass(CwmactionlogHelper::class)]
 * @since  10.1.0
 */
class CwmactionlogHelperTest extends ProclaimTestCase
{
    /**
     * Test log method signature
     *
     * @return void
     * #[CoversClass(CwmactionlogHelper::class)]::log
     */
    public function testLogMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmactionlogHelper::class, 'log');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(5, $params);
        
        $this->assertEquals('messageKey', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        
        $this->assertEquals('title', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
        
        $this->assertEquals('type', $params[2]->getName());
        $this->assertEquals('string', $params[2]->getType()->getName());
        
        $this->assertEquals('id', $params[3]->getName());
        $this->assertEquals('int', $params[3]->getType()->getName());
        
        $this->assertEquals('extra', $params[4]->getName());
        $this->assertEquals('array', $params[4]->getType()->getName());
        $this->assertTrue($params[4]->isOptional());
        $this->assertEquals([], $params[4]->getDefaultValue());
    }
}

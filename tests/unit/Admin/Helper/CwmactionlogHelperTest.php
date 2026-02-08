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
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(5, $params);
        
        $this->assertEquals('messageKey', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        
        $this->assertEquals('title', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        
        $this->assertEquals('type', $params[2]->getName());
        $this->assertParamTypeName('string', $params[2]);
        
        $this->assertEquals('id', $params[3]->getName());
        $this->assertParamTypeName('int', $params[3]);
        
        $this->assertEquals('extra', $params[4]->getName());
        $this->assertParamTypeName('array', $params[4]);
        $this->assertTrue($params[4]->isOptional());
        $this->assertEquals([], $params[4]->getDefaultValue());
    }
}

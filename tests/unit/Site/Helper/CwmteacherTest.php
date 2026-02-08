<?php

/**
 * Unit tests for Cwmteacher Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmteacher;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for Cwmteacher helper
 *
 * #[CoversClass(Cwmteacher::class)]
 * @since  10.0.0
 */
class CwmteacherTest extends ProclaimTestCase
{
    /**
     * Test getTeachersFluid method signature
     *
     * @return void
     * #[CoversClass(Cwmteacher::class)]::getTeachersFluid
     */
    public function testGetTeachersFluidMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(Cwmteacher::class, 'getTeachersFluid');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertEquals('Joomla\Registry\Registry', $params[0]->getType()->getName());
    }
}

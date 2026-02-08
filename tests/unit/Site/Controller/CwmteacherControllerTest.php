<?php

/**
 * Unit tests for CwmteacherController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmteacherController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmteacherController
 *
 * #[CoversClass(CwmteacherController::class)]
 * @since  10.0.0
 */
class CwmteacherControllerTest extends ProclaimTestCase
{
    /**
     * Test view method signature
     *
     * @return void
     * #[CoversClass(CwmteacherController::class)]::view
     */
    public function testViewMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmteacherController::class, 'view');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}

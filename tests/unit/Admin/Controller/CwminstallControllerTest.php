<?php

/**
 * Unit tests for CwminstallController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwminstallController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwminstallController
 *
 * #[CoversClass(CwminstallController::class)]
 * @since  10.0.0
 */
class CwminstallControllerTest extends ProclaimTestCase
{
    /**
     * Test execute method signature
     *
     * @return void
     * #[CoversClass(CwminstallController::class)]::execute
     */
    public function testExecuteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwminstallController::class, 'execute');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('task', $params[0]->getName());
        // $task parameter has no type hint in Joomla BaseController
    }

    /**
     * Test browse method signature
     *
     * @return void
     * #[CoversClass(CwminstallController::class)]::browse
     */
    public function testBrowseMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwminstallController::class, 'browse');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test run method signature
     *
     * @return void
     * #[CoversClass(CwminstallController::class)]::run
     */
    public function testRunMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwminstallController::class, 'run');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test clear method signature
     *
     * @return void
     * #[CoversClass(CwminstallController::class)]::clear
     */
    public function testClearMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwminstallController::class, 'clear');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }
}

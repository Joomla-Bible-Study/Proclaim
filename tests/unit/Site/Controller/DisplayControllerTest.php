<?php

/**
 * Unit tests for DisplayController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\DisplayController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for DisplayController
 *
 * #[CoversClass(DisplayController::class)]
 * @since  10.0.0
 */
class DisplayControllerTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(DisplayController::class)]::__construct
     */
    public function testConstructor(): void
    {
        $controller = new DisplayController();
        $this->assertInstanceOf(DisplayController::class, $controller);
    }

    /**
     * Test display method signature
     *
     * @return void
     * #[CoversClass(DisplayController::class)]::display
     */
    public function testDisplayMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(DisplayController::class, 'display');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is DisplayController, which reflection might show differently

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('cachable', $params[0]->getName());
        $this->assertParamTypeName('bool', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('urlparams', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }
}

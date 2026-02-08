<?php

/**
 * Unit tests for CwmseriesdisplayController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmseriesdisplayController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmseriesdisplayController
 *
 * #[CoversClass(CwmseriesdisplayController::class)]
 * @since  10.0.0
 */
class CwmseriesdisplayControllerTest extends ProclaimTestCase
{
    /**
     * Test display method signature
     *
     * @return void
     * #[CoversClass(CwmseriesdisplayController::class)]::display
     */
    public function testDisplayMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplayController::class, 'display');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is static|CwmseriesdisplayController, which reflection might show differently

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

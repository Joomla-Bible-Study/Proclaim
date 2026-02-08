<?php

/**
 * Unit tests for CwmcommentsController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmcommentsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmcommentsController
 *
 * #[CoversClass(CwmcommentsController::class)]
 * @since  10.0.0
 */
class CwmcommentsControllerTest extends ProclaimTestCase
{
    /**
     * Test getModel method signature
     *
     * @return void
     * #[CoversClass(CwmcommentsController::class)]::getModel
     */
    public function testGetModelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcommentsController::class, 'getModel');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('Joomla\CMS\MVC\Model\BaseDatabaseModel', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('prefix', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());

        $this->assertEquals('config', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getQuickIconComments method signature
     *
     * @return void
     * #[CoversClass(CwmcommentsController::class)]::getQuickIconComments
     */
    public function testGetQuickIconCommentsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcommentsController::class, 'getQuickIconComments');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }
}

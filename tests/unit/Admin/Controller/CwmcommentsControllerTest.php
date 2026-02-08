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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
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
        $this->assertEquals('Joomla\CMS\MVC\Model\BaseDatabaseModel', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('prefix', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('config', $params[2]->getName());
        $this->assertEquals('array', $params[2]->getType()->getName());
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
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}

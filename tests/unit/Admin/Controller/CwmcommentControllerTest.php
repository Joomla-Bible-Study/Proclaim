<?php

/**
 * Unit tests for CwmcommentController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmcommentController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmcommentController
 *
 * #[CoversClass(CwmcommentController::class)]
 * @since  10.0.0
 */
class CwmcommentControllerTest extends ProclaimTestCase
{
    /**
     * Test batch method signature
     *
     * @return void
     * #[CoversClass(CwmcommentController::class)]::batch
     */
    public function testBatchMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcommentController::class, 'batch');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('model', $params[0]->getName());
        $this->assertEquals('Joomla\CMS\MVC\Model\BaseDatabaseModel', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test allowAdd method signature
     *
     * @return void
     * #[CoversClass(CwmcommentController::class)]::allowAdd
     */
    public function testAllowAddMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcommentController::class, 'allowAdd');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test allowEdit method signature
     *
     * @return void
     * #[CoversClass(CwmcommentController::class)]::allowEdit
     */
    public function testAllowEditMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmcommentController::class, 'allowEdit');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('key', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
    }
}

<?php

/**
 * Unit tests for CwmserverController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmserverController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmserverController
 *
 * #[CoversClass(CwmserverController::class)]
 * @since  10.0.0
 */
class CwmserverControllerTest extends ProclaimTestCase
{
    /**
     * Test add method signature
     *
     * @return void
     * #[CoversClass(CwmserverController::class)]::add
     */
    public function testAddMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverController::class, 'add');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test edit method signature
     *
     * @return void
     * #[CoversClass(CwmserverController::class)]::edit
     */
    public function testEditMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverController::class, 'edit');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('urlVar', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->allowsNull());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test setType method signature
     *
     * @return void
     * #[CoversClass(CwmserverController::class)]::setType
     */
    public function testSetTypeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverController::class, 'setType');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test addonAjax method signature
     *
     * @return void
     * #[CoversClass(CwmserverController::class)]::addonAjax
     */
    public function testAddonAjaxMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverController::class, 'addonAjax');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test batch method signature
     *
     * @return void
     * #[CoversClass(CwmserverController::class)]::batch
     */
    public function testBatchMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverController::class, 'batch');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('model', $params[0]->getName());
        $this->assertParamTypeName('CWM\Component\Proclaim\Administrator\Model\CwmserverModel', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test postSaveHook method signature
     *
     * @return void
     * #[CoversClass(CwmserverController::class)]::postSaveHook
     */
    public function testPostSaveHookMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverController::class, 'postSaveHook');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('model', $params[0]->getName());
        $this->assertParamTypeName('Joomla\CMS\MVC\Model\BaseDatabaseModel', $params[0]);

        $this->assertEquals('validData', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }
}

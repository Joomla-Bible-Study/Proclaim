<?php

/**
 * Unit tests for CwmmediafileController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmmediafileController;
use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Model\BaseModel;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmmediafileController
 *
 * #[CoversClass(CwmmediafileController::class)]
 * @since  10.0.0
 */
class CwmmediafileControllerTest extends ProclaimTestCase
{
    /**
     * Test add method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileController::class)]::add
     */
    public function testAddMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileController::class, 'add');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test edit method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileController::class)]::edit
     */
    public function testEditMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileController::class, 'edit');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertParamTypeName('int', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('urlVar', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->allowsNull());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test xhr method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileController::class)]::xhr
     */
    public function testXhrMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileController::class, 'xhr');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test batch method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileController::class)]::batch
     */
    public function testBatchMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileController::class, 'batch');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('model', $params[0]->getName());
        $this->assertParamTypeName('CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test cancel method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileController::class)]::cancel
     */
    public function testCancelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileController::class, 'cancel');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getRedirectToItemAppend method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileController::class)]::getRedirectToItemAppend
     */
    public function testGetRedirectToItemAppendMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileController::class, 'getRedirectToItemAppend');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('recordId', $params[0]->getName());
        $this->assertParamTypeName('int', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('urlVar', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test setServer method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileController::class)]::setServer
     */
    public function testSetServerMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileController::class, 'setServer');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test postSaveHook method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileController::class)]::postSaveHook
     */
    public function testPostSaveHookMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileController::class, 'postSaveHook');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('model', $params[0]->getName());
        $this->assertParamTypeName('Joomla\CMS\MVC\Model\BaseModel', $params[0]);
        
        $this->assertEquals('validData', $params[1]->getName());
        $this->assertParamTypeName('array', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }
}

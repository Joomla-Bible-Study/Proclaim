<?php

/**
 * Unit tests for CwmsermonController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmsermonController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmsermonController
 *
 * #[CoversClass(CwmsermonController::class)]
 * @since  10.0.0
 */
class CwmsermonControllerTest extends ProclaimTestCase
{
    /**
     * Test add method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::add
     */
    public function testAddMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'add');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test getReturnPage method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::getReturnPage
     */
    public function testGetReturnPageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'getReturnPage');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test cancel method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::cancel
     */
    public function testCancelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'cancel');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test edit method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::edit
     */
    public function testEditMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'edit');

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
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test save method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::save
     */
    public function testSaveMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'save');

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
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test comment method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::comment
     */
    public function testCommentMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'comment');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getModel method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::getModel
     */
    public function testGetModelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'getModel');

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
     * Test commentsEmail method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::commentsEmail
     */
    public function testCommentsEmailMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'commentsEmail');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('params', $params[0]->getName());
        // No type hint in method signature for params
    }

    /**
     * Test download method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::download
     */
    public function testDownloadMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'download');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test allowAdd method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::allowAdd
     */
    public function testAllowAddMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'allowAdd');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test allowEdit method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::allowEdit
     */
    public function testAllowEditMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'allowEdit');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('key', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getRedirectToItemAppend method signature
     *
     * @return void
     * #[CoversClass(CwmsermonController::class)]::getRedirectToItemAppend
     */
    public function testGetRedirectToItemAppendMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonController::class, 'getRedirectToItemAppend');

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
}

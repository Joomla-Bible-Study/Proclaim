<?php

/**
 * Unit tests for CwmtemplateController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmtemplateController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtemplateController
 *
 * #[CoversClass(CwmtemplateController::class)]
 * @since  10.0.0
 */
class CwmtemplateControllerTest extends ProclaimTestCase
{
    /**
     * Test copy method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateController::class)]::copy
     */
    public function testCopyMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateController::class, 'copy');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test loadFieldset method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateController::class)]::loadFieldset
     */
    public function testLoadFieldsetMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateController::class, 'loadFieldset');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test loadLayoutEditor method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateController::class)]::loadLayoutEditor
     */
    public function testLoadLayoutEditorMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateController::class, 'loadLayoutEditor');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test batch method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateController::class)]::batch
     */
    public function testBatchMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateController::class, 'batch');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('model', $params[0]->getName());
        // No type hint in method signature for model
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test postSaveHook method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateController::class)]::postSaveHook
     */
    public function testPostSaveHookMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateController::class, 'postSaveHook');

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

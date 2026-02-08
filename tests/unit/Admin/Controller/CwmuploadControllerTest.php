<?php

/**
 * Unit tests for CwmuploadController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmuploadController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmuploadController
 *
 * #[CoversClass(CwmuploadController::class)]
 * @since  10.0.0
 */
class CwmuploadControllerTest extends ProclaimTestCase
{
    /**
     * Test upload method signature
     *
     * @return void
     * #[CoversClass(CwmuploadController::class)]::upload
     */
    public function testUploadMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmuploadController::class, 'upload');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test setResponse method signature
     *
     * @return void
     * #[CoversClass(CwmuploadController::class)]::setResponse
     */
    public function testSetResponseMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmuploadController::class, 'setResponse');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('code', $params[0]->getName());
        $this->assertParamTypeName('int', $params[0]);

        $this->assertEquals('msg', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->allowsNull());
        $this->assertTrue($params[1]->isOptional());

        $this->assertEquals('error', $params[2]->getName());
        $this->assertParamTypeName('bool', $params[2]);
        $this->assertTrue($params[2]->allowsNull());
        $this->assertTrue($params[2]->isOptional());
    }
}

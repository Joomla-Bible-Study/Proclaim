<?php

/**
 * Unit tests for CwmteachersController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmteachersController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\CurrentUserInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmteachersController
 *
 * #[CoversClass(CwmteachersController::class)]
 * @since  10.0.0
 */
class CwmteachersControllerTest extends ProclaimTestCase
{
    /**
     * Test getModel method signature
     *
     * @return void
     * #[CoversClass(CwmteachersController::class)]::getModel
     */
    public function testGetModelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmteachersController::class, 'getModel');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is bool|BaseDatabaseModel|CurrentUserInterface, which reflection might show differently

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
}

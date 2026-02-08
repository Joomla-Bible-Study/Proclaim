<?php

/**
 * Unit tests for CwmmediafilesController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmmediafilesController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmmediafilesController
 *
 * #[CoversClass(CwmmediafilesController::class)]
 * @since  10.0.0
 */
class CwmmediafilesControllerTest extends ProclaimTestCase
{
    /**
     * Test checkin method signature
     *
     * @return void
     * #[CoversClass(CwmmediafilesController::class)]::checkin
     */
    public function testCheckinMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafilesController::class, 'checkin');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    /**
     * Test getModel method signature
     *
     * @return void
     * #[CoversClass(CwmmediafilesController::class)]::getModel
     */
    public function testGetModelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafilesController::class, 'getModel');

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
     * Test getQuickIconMediaFiles method signature
     *
     * @return void
     * #[CoversClass(CwmmediafilesController::class)]::getQuickIconMediaFiles
     */
    public function testGetQuickIconMediaFilesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafilesController::class, 'getQuickIconMediaFiles');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}

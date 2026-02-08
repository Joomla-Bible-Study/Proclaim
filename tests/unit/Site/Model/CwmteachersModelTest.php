<?php

/**
 * Unit tests for CwmteachersModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmteachersModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\DatabaseQuery;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmteachersModel
 *
 * #[CoversClass(CwmteachersModel::class)]
 * @since  10.0.0
 */
class CwmteachersModelTest extends ProclaimTestCase
{
    /**
     * Test getItems method signature
     *
     * @return void
     * #[CoversClass(CwmteachersModel::class)]::getItems
     */
    public function testGetItemsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmteachersModel::class, 'getItems');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test getListQuery method signature
     *
     * @return void
     * #[CoversClass(CwmteachersModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmteachersModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('Joomla\Database\DatabaseQuery', $reflection->getReturnType()->getName());
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmteachersModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmteachersModel::class, 'populateState');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('ordering', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('direction', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
    }
}

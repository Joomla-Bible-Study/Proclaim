<?php

/**
 * Unit tests for CwmtopicsModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmtopicsModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtopicsModel
 *
 * #[CoversClass(CwmtopicsModel::class)]
 * @since  10.0.0
 */
class CwmtopicsModelTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmtopicsModel::class)]::__construct
     */
    public function testConstructor(): void
    {
        $model = new CwmtopicsModel();
        $this->assertInstanceOf(CwmtopicsModel::class, $model);
    }

    /**
     * Test getTranslated method signature
     *
     * @return void
     * #[CoversClass(CwmtopicsModel::class)]::getTranslated
     */
    public function testGetTranslatedMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicsModel::class, 'getTranslated');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('items', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmtopicsModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicsModel::class, 'populateState');

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

    /**
     * Test getStoreId method signature
     *
     * @return void
     * #[CoversClass(CwmtopicsModel::class)]::getStoreId
     */
    public function testGetStoreIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicsModel::class, 'getStoreId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getListQuery method signature
     *
     * @return void
     * #[CoversClass(CwmtopicsModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicsModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        // Return type is QueryInterface|string, which reflection might show differently
    }
}

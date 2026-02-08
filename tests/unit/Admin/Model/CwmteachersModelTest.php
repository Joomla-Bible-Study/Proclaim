<?php

/**
 * Unit tests for CwmteachersModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmteachersModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
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
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmteachersModel::class)]::__construct
     */
    public function testConstructor(): void
    {
        $model = new CwmteachersModel();
        $this->assertInstanceOf(CwmteachersModel::class, $model);
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
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('ordering', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('direction', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getStoreId method signature
     *
     * @return void
     * #[CoversClass(CwmteachersModel::class)]::getStoreId
     */
    public function testGetStoreIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmteachersModel::class, 'getStoreId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());
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
        $this->assertReturnTypeName('mixed', $reflection);
    }
}

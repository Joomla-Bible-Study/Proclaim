<?php

/**
 * Unit tests for CwmmediafilesModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmmediafilesModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmmediafilesModel
 *
 * #[CoversClass(CwmmediafilesModel::class)]
 * @since  10.0.0
 */
class CwmmediafilesModelTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmmediafilesModel::class)]::__construct
     */
    public function testConstructor(): void
    {
        $model = new CwmmediafilesModel();
        $this->assertInstanceOf(CwmmediafilesModel::class, $model);
    }

    /**
     * Test getItems method signature
     *
     * @return void
     * #[CoversClass(CwmmediafilesModel::class)]::getItems
     */
    public function testGetItemsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafilesModel::class, 'getItems');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test getStoreId method signature
     *
     * @return void
     * #[CoversClass(CwmmediafilesModel::class)]::getStoreId
     */
    public function testGetStoreIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafilesModel::class, 'getStoreId');

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
     * Test getDeletes method signature
     *
     * @return void
     * #[CoversClass(CwmmediafilesModel::class)]::getDeletes
     */
    public function testGetDeletesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafilesModel::class, 'getDeletes');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmmediafilesModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafilesModel::class, 'populateState');

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
     * Test getListQuery method signature
     *
     * @return void
     * #[CoversClass(CwmmediafilesModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafilesModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        // Return type is QueryInterface|string, which reflection might show differently
    }
}

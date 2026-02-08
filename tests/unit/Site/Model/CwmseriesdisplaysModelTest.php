<?php

/**
 * Unit tests for CwmseriesdisplaysModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmseriesdisplaysModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmseriesdisplaysModel
 *
 * #[CoversClass(CwmseriesdisplaysModel::class)]
 * @since  10.0.0
 */
class CwmseriesdisplaysModelTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmseriesdisplaysModel::class)]::__construct
     */
    public function testConstructor(): void
    {
        $model = new CwmseriesdisplaysModel();
        $this->assertInstanceOf(CwmseriesdisplaysModel::class, $model);
    }

    /**
     * Test getTeachers method signature
     *
     * @return void
     * #[CoversClass(CwmseriesdisplaysModel::class)]::getTeachers
     */
    public function testGetTeachersMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysModel::class, 'getTeachers');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test getYears method signature
     *
     * @return void
     * #[CoversClass(CwmseriesdisplaysModel::class)]::getYears
     */
    public function testGetYearsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysModel::class, 'getYears');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test getSeries method signature
     *
     * @return void
     * #[CoversClass(CwmseriesdisplaysModel::class)]::getSeries
     */
    public function testGetSeriesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysModel::class, 'getSeries');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmseriesdisplaysModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysModel::class, 'populateState');

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
     * #[CoversClass(CwmseriesdisplaysModel::class)]::getStoreId
     */
    public function testGetStoreIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysModel::class, 'getStoreId');

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
     * #[CoversClass(CwmseriesdisplaysModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('Joomla\Database\QueryInterface', $reflection);
    }

    /**
     * Test getStart method signature
     *
     * @return void
     * #[CoversClass(CwmseriesdisplaysModel::class)]::getStart
     */
    public function testGetStartMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmseriesdisplaysModel::class, 'getStart');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);
    }
}

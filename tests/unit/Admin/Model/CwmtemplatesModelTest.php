<?php

/**
 * Unit tests for CwmtemplatesModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmtemplatesModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtemplatesModel
 *
 * #[CoversClass(CwmtemplatesModel::class)]
 * @since  10.0.0
 */
class CwmtemplatesModelTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmtemplatesModel::class)]::__construct
     */
    public function testConstructor(): void
    {
        $model = new CwmtemplatesModel();
        $this->assertInstanceOf(CwmtemplatesModel::class, $model);
    }

    /**
     * Test getTemplates method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesModel::class)]::getTemplates
     */
    public function testGetTemplatesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesModel::class, 'getTemplates');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test getTypes method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesModel::class)]::getTypes
     */
    public function testGetTypesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesModel::class, 'getTypes');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesModel::class, 'populateState');

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
     * #[CoversClass(CwmtemplatesModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('mixed', $reflection);
    }
}

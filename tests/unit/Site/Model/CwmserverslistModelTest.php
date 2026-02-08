<?php

/**
 * Unit tests for CwmserverslistModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmserverslistModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmserverslistModel
 *
 * #[CoversClass(CwmserverslistModel::class)]
 * @since  10.0.0
 */
class CwmserverslistModelTest extends ProclaimTestCase
{
    /**
     * Test that CwmserverslistModel extends CwmserversModel
     *
     * @return void
     * #[CoversClass(CwmserverslistModel::class)]
     */
    public function testExtendsAdminServersModel(): void
    {
        $reflection = new \ReflectionClass(CwmserverslistModel::class);

        $this->assertEquals(
            'CWM\Component\Proclaim\Administrator\Model\CwmserversModel',
            $reflection->getParentClass()->getName()
        );
    }

    /**
     * Test getListQuery method signature (inherited from CwmserversModel)
     *
     * @return void
     * #[CoversClass(CwmserverslistModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverslistModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        // Return type is QueryInterface|string, which is a union type
    }

    /**
     * Test populateState method signature (inherited from CwmserversModel)
     *
     * @return void
     * #[CoversClass(CwmserverslistModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverslistModel::class, 'populateState');

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
     * Test getStoreId method signature (inherited from ListModel)
     *
     * @return void
     * #[CoversClass(CwmserverslistModel::class)]::getStoreId
     */
    public function testGetStoreIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverslistModel::class, 'getStoreId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('id', $params[0]->getName());
        $this->assertTrue($params[0]->isOptional());
    }
}

<?php

/**
 * Unit tests for CwmtemplatecodesModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmtemplatecodesModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtemplatecodesModel
 *
 * #[CoversClass(CwmtemplatecodesModel::class)]
 * @since  10.0.0
 */
class CwmtemplatecodesModelTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmtemplatecodesModel::class)]::__construct
     */
    public function testConstructor(): void
    {
        $model = new CwmtemplatecodesModel();
        $this->assertInstanceOf(CwmtemplatecodesModel::class, $model);
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatecodesModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodesModel::class, 'populateState');

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
     * #[CoversClass(CwmtemplatecodesModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodesModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('mixed', $reflection);
    }
}

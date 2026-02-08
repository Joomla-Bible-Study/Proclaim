<?php

/**
 * Unit tests for CwmserversModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmserversModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmserversModel
 *
 * #[CoversClass(CwmserversModel::class)]
 * @since  10.0.0
 */
class CwmserversModelTest extends ProclaimTestCase
{
    /**
     * Test getIdToNameReverseLookup method signature
     *
     * @return void
     * #[CoversClass(CwmserversModel::class)]::getIdToNameReverseLookup
     */
    public function testGetIdToNameReverseLookupMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserversModel::class, 'getIdToNameReverseLookup');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test getTypeReverseLookup method signature
     *
     * @return void
     * #[CoversClass(CwmserversModel::class)]::getTypeReverseLookup
     */
    public function testGetTypeReverseLookupMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserversModel::class, 'getTypeReverseLookup');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test getServerOptions method signature
     *
     * @return void
     * #[CoversClass(CwmserversModel::class)]::getServerOptions
     */
    public function testGetServerOptionsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserversModel::class, 'getServerOptions');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is array|bool, which reflection might show differently
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmserversModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserversModel::class, 'populateState');

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
     * #[CoversClass(CwmserversModel::class)]::getListQuery
     */
    public function testGetListQueryMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserversModel::class, 'getListQuery');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        // Return type is QueryInterface|string, which reflection might show differently
    }
}

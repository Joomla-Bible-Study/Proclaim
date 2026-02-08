<?php

/**
 * Unit tests for CwmmediafileModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmmediafileModel
 *
 * #[CoversClass(CwmmediafileModel::class)]
 * @since  10.0.0
 */
class CwmmediafileModelTest extends ProclaimTestCase
{
    /**
     * Test move method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileModel::class)]::move
     */
    public function testMoveMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileModel::class, 'move');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('direction', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test getTable method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileModel::class)]::getTable
     */
    public function testGetTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileModel::class, 'getTable');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('Joomla\CMS\Table\Table', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('prefix', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('options', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test save method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileModel::class)]::save
     */
    public function testSaveMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileModel::class, 'save');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('data', $params[0]->getName());
        // No type hint in method signature for data
    }

    /**
     * Test autoDetectMetadata method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileModel::class)]::autoDetectMetadata
     */
    public function testAutoDetectMetadataMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileModel::class, 'autoDetectMetadata');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(4, $params);
        $this->assertEquals('params', $params[0]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[0]);
        
        $this->assertEquals('server', $params[1]->getName());
        $this->assertParamTypeName('object', $params[1]);
        
        $this->assertEquals('set_path', $params[2]->getName());
        $this->assertParamTypeName('string', $params[2]);
        
        $this->assertEquals('path', $params[3]->getName());
        $this->assertParamTypeName('Joomla\Registry\Registry', $params[3]);
    }

    /**
     * Test getMediaForm method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileModel::class)]::getMediaForm
     */
    public function testGetMediaFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileModel::class, 'getMediaForm');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test getItem method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileModel::class)]::getItem
     */
    public function testGetItemMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileModel::class, 'getItem');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        // No type hint in method signature for pk
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getForm method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileModel::class)]::getForm
     */
    public function testGetFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileModel::class, 'getForm');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is bool|object, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('loadData', $params[1]->getName());
        $this->assertParamTypeName('bool', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test checkin method signature
     *
     * @return void
     * #[CoversClass(CwmmediafileModel::class)]::checkin
     */
    public function testCheckinMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmediafileModel::class, 'checkin');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is false|int, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pks', $params[0]->getName());
        $this->assertParamTypeName('mixed', $params[0]);
        $this->assertTrue($params[0]->isOptional());
    }
}

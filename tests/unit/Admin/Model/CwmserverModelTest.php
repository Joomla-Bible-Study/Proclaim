<?php

/**
 * Unit tests for CwmserverModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmserverModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmserverModel
 *
 * #[CoversClass(CwmserverModel::class)]
 * @since  10.0.0
 */
class CwmserverModelTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::__construct
     */
    public function testConstructor(): void
    {
        $model = new CwmserverModel();
        $this->assertInstanceOf(CwmserverModel::class, $model);
    }

    /**
     * Test getType method signature
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::getType
     */
    public function testGetTypeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverModel::class, 'getType');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('pk', $params[0]->getName());
        $this->assertEquals('int', $params[0]->getType()->getName());
        
        $this->assertEquals('ext', $params[1]->getName());
        $this->assertEquals('bool', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getItem method signature
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::getItem
     */
    public function testGetItemMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverModel::class, 'getItem');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('mixed', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('pk', $params[0]->getName());
        // No type hint in method signature for pk
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('ext', $params[1]->getName());
        $this->assertEquals('bool', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getConfig method signature
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::getConfig
     */
    public function testGetConfigMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverModel::class, 'getConfig');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is \SimpleXMLElement|bool, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('addon', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    /**
     * Test save method signature
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::save
     */
    public function testSaveMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverModel::class, 'save');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('data', $params[0]->getName());
        // No type hint in method signature for data
    }

    /**
     * Test getAddonServerForm method signature
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::getAddonServerForm
     */
    public function testGetAddonServerFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverModel::class, 'getAddonServerForm');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('Joomla\CMS\Form\Form', $reflection->getReturnType()->getName());
        $this->assertTrue($reflection->getReturnType()->allowsNull());
    }

    /**
     * Test getForm method signature
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::getForm
     */
    public function testGetFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverModel::class, 'getForm');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('mixed', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('loadData', $params[1]->getName());
        $this->assertEquals('bool', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getTable method signature
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::getTable
     */
    public function testGetTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverModel::class, 'getTable');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('Joomla\CMS\Table\Table', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('prefix', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('options', $params[2]->getName());
        $this->assertEquals('array', $params[2]->getType()->getName());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test populateState method signature
     *
     * @return void
     * #[CoversClass(CwmserverModel::class)]::populateState
     */
    public function testPopulateStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserverModel::class, 'populateState');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}

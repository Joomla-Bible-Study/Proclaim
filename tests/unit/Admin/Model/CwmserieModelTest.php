<?php

/**
 * Unit tests for CwmserieModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmserieModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Table\Table;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmserieModel
 *
 * #[CoversClass(CwmserieModel::class)]
 * @since  10.0.0
 */
class CwmserieModelTest extends ProclaimTestCase
{
    /**
     * Test getForm method signature
     *
     * @return void
     * #[CoversClass(CwmserieModel::class)]::getForm
     */
    public function testGetFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserieModel::class, 'getForm');

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
     * Test getItem method signature
     *
     * @return void
     * #[CoversClass(CwmserieModel::class)]::getItem
     */
    public function testGetItemMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserieModel::class, 'getItem');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('mixed', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        // No type hint in method signature for pk
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getTeacher method signature
     *
     * @return void
     * #[CoversClass(CwmserieModel::class)]::getTeacher
     */
    public function testGetTeacherMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserieModel::class, 'getTeacher');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('mixed', $reflection->getReturnType()->getName());
    }

    /**
     * Test save method signature
     *
     * @return void
     * #[CoversClass(CwmserieModel::class)]::save
     */
    public function testSaveMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserieModel::class, 'save');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('data', $params[0]->getName());
        // No type hint in method signature for data
    }

    /**
     * Test checkout method signature
     *
     * @return void
     * #[CoversClass(CwmserieModel::class)]::checkout
     */
    public function testCheckoutMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserieModel::class, 'checkout');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        // No type hint in method signature for pk
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getTable method signature
     *
     * @return void
     * #[CoversClass(CwmserieModel::class)]::getTable
     */
    public function testGetTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmserieModel::class, 'getTable');

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
}

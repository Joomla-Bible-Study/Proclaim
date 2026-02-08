<?php

/**
 * Unit tests for CwmtemplateModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmtemplateModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Table\Table;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtemplateModel
 *
 * #[CoversClass(CwmtemplateModel::class)]
 * @since  10.0.0
 */
class CwmtemplateModelTest extends ProclaimTestCase
{
    /**
     * Test save method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateModel::class)]::save
     */
    public function testSaveMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateModel::class, 'save');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('data', $params[0]->getName());
        // No type hint in method signature for data
    }

    /**
     * Test copy method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateModel::class)]::copy
     */
    public function testCopyMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateModel::class, 'copy');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('cid', $params[0]->getName());
        $this->assertEquals('array', $params[0]->getType()->getName());
    }

    /**
     * Test publish method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateModel::class)]::publish
     */
    public function testPublishMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateModel::class, 'publish');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('pks', $params[0]->getName());
        $this->assertTrue($params[0]->isPassedByReference());
        
        $this->assertEquals('value', $params[1]->getName());
        $this->assertEquals('int', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getForm method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateModel::class)]::getForm
     */
    public function testGetFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateModel::class, 'getForm');

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
     * Test checkout method signature
     *
     * @return void
     * #[CoversClass(CwmtemplateModel::class)]::checkout
     */
    public function testCheckoutMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateModel::class, 'checkout');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
        $this->assertTrue($reflection->getReturnType()->allowsNull());

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
     * #[CoversClass(CwmtemplateModel::class)]::getTable
     */
    public function testGetTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplateModel::class, 'getTable');

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

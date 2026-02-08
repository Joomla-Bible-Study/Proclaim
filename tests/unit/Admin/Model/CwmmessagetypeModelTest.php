<?php

/**
 * Unit tests for CwmmessagetypeModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmmessagetypeModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Table\Table;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmmessagetypeModel
 *
 * #[CoversClass(CwmmessagetypeModel::class)]
 * @since  10.0.0
 */
class CwmmessagetypeModelTest extends ProclaimTestCase
{
    /**
     * Test getForm method signature
     *
     * @return void
     * #[CoversClass(CwmmessagetypeModel::class)]::getForm
     */
    public function testGetFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmessagetypeModel::class, 'getForm');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);

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
     * Test checkout method signature
     *
     * @return void
     * #[CoversClass(CwmmessagetypeModel::class)]::checkout
     */
    public function testCheckoutMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmessagetypeModel::class, 'checkout');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

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
     * #[CoversClass(CwmmessagetypeModel::class)]::getTable
     */
    public function testGetTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmessagetypeModel::class, 'getTable');

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
}

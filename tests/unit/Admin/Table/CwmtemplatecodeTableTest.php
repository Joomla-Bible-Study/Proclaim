<?php

/**
 * Unit tests for CwmtemplatecodeTable
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmtemplatecodeTable;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtemplatecodeTable
 *
 * #[CoversClass(CwmtemplatecodeTable::class)]
 * @since  10.0.0
 */
class CwmtemplatecodeTableTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeTable::class)]::__construct
     */
    public function testConstructor(): void
    {
        $db = $this->createMock(DatabaseDriver::class);
        $table = new CwmtemplatecodeTable($db);
        $this->assertInstanceOf(CwmtemplatecodeTable::class, $table);
    }

    /**
     * Test check method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeTable::class)]::check
     */
    public function testCheckMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodeTable::class, 'check');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());
    }

    /**
     * Test bind method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeTable::class)]::bind
     */
    public function testBindMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodeTable::class, 'bind');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('src', $params[0]->getName());
        $this->assertEquals('mixed', $params[0]->getType()->getName());
        
        $this->assertEquals('ignore', $params[1]->getName());
        $this->assertEquals('mixed', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test store method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeTable::class)]::store
     */
    public function testStoreMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodeTable::class, 'store');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('updateNulls', $params[0]->getName());
        $this->assertEquals('bool', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test delete method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeTable::class)]::delete
     */
    public function testDeleteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodeTable::class, 'delete');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        $this->assertEquals('mixed', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test _getAssetName method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeTable::class)]::_getAssetName
     */
    public function testGetAssetNameMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodeTable::class, '_getAssetName');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('string', $reflection->getReturnType()->getName());
    }

    /**
     * Test _getAssetTitle method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeTable::class)]::_getAssetTitle
     */
    public function testGetAssetTitleMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodeTable::class, '_getAssetTitle');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('string', $reflection->getReturnType()->getName());
    }

    /**
     * Test _getAssetParentId method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeTable::class)]::_getAssetParentId
     */
    public function testGetAssetParentIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatecodeTable::class, '_getAssetParentId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertEquals('int', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('table', $params[0]->getName());
        $this->assertEquals('Joomla\CMS\Table\Table', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('id', $params[1]->getName());
        // No type hint in method signature for id
        $this->assertTrue($params[1]->isOptional());
    }
}

<?php

/**
 * Unit tests for CwmtopicTable
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmtopicTable;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtopicTable
 *
 * #[CoversClass(CwmtopicTable::class)]
 * @since  10.0.0
 */
class CwmtopicTableTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::__construct
     */
    public function testConstructor(): void
    {
        $db    = $this->createMock(DatabaseDriver::class);
        $table = new CwmtopicTable($db);
        $this->assertInstanceOf(CwmtopicTable::class, $table);
    }

    /**
     * Test check method signature
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::check
     */
    public function testCheckMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicTable::class, 'check');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test bind method signature
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::bind
     */
    public function testBindMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicTable::class, 'bind');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('array', $params[0]->getName());
        $this->assertParamTypeName('mixed', $params[0]);

        $this->assertEquals('ignore', $params[1]->getName());
        $this->assertParamTypeName('mixed', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test store method signature
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::store
     */
    public function testStoreMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicTable::class, 'store');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('updateNulls', $params[0]->getName());
        $this->assertParamTypeName('bool', $params[0]);
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test load method signature
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::load
     */
    public function testLoadMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicTable::class, 'load');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('keys', $params[0]->getName());
        $this->assertParamTypeName('mixed', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('reset', $params[1]->getName());
        $this->assertParamTypeName('bool', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test checkAlias method signature
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::checkAlias
     */
    public function testCheckAliasMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicTable::class, 'checkAlias');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is bool|array, which reflection might show differently

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('data', $params[0]->getName());
        $this->assertParamTypeName('array', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('recordId', $params[1]->getName());
        $this->assertParamTypeName('int', $params[1]);
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test _getAssetName method signature
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::_getAssetName
     */
    public function testGetAssetNameMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicTable::class, '_getAssetName');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test _getAssetTitle method signature
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::_getAssetTitle
     */
    public function testGetAssetTitleMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicTable::class, '_getAssetTitle');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test _getAssetParentId method signature
     *
     * @return void
     * #[CoversClass(CwmtopicTable::class)]::_getAssetParentId
     */
    public function testGetAssetParentIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtopicTable::class, '_getAssetParentId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isProtected());
        $this->assertReturnTypeName('int', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('table', $params[0]->getName());
        $this->assertParamTypeName('Joomla\CMS\Table\Table', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('id', $params[1]->getName());
        // No type hint in method signature for id
        $this->assertTrue($params[1]->isOptional());
    }
}

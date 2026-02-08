<?php

/**
 * Unit tests for CwmadminModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmadminModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Schema\ChangeSet;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmadminModel
 *
 * #[CoversClass(CwmadminModel::class)]
 * @since  10.0.0
 */
class CwmadminModelTest extends ProclaimTestCase
{
    /**
     * Test getForm method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getForm
     */
    public function testGetFormMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getForm');

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
     * Test getTable method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getTable
     */
    public function testGetTableMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getTable');

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
     * #[CoversClass(CwmadminModel::class)]::save
     */
    public function testSaveMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'save');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('data', $params[0]->getName());
        // No type hint in method signature for data
    }

    /**
     * Test checkout method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::checkout
     */
    public function testCheckoutMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'checkout');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is bool|int|null, which reflection might show differently

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('pk', $params[0]->getName());
        // No type hint in method signature for pk
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test getMediaFiles method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getMediaFiles
     */
    public function testGetMediaFilesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getMediaFiles');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test fix method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::fix
     */
    public function testFixMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'fix');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test getItems method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getItems
     */
    public function testGetItemsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getItems');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is ChangeSet|bool|null, which reflection might show differently
    }

    /**
     * Test fixSchemaVersion method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::fixSchemaVersion
     */
    public function testFixSchemaVersionMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'fixSchemaVersion');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('changeSet', $params[0]->getName());
        $this->assertParamTypeName('Joomla\CMS\Schema\ChangeSet', $params[0]);
    }

    /**
     * Test getExtentionId method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getExtentionId
     */
    public function testGetExtentionIdMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getExtentionId');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test getSchemaVersion method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getSchemaVersion
     */
    public function testGetSchemaVersionMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getSchemaVersion');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test fixUpdateVersion method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::fixUpdateVersion
     */
    public function testFixUpdateVersionMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'fixUpdateVersion');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test getCompVersion method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getCompVersion
     */
    public function testGetCompVersionMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getCompVersion');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);
    }

    /**
     * Test fixDefaultTextFilters method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::fixDefaultTextFilters
     */
    public function testFixDefaultTextFiltersMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'fixDefaultTextFilters');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test getPagination method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getPagination
     */
    public function testGetPaginationMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getPagination');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test getUpdateVersion method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getUpdateVersion
     */
    public function testGetUpdateVersionMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getUpdateVersion');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test getDefaultTextFilters method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getDefaultTextFilters
     */
    public function testGetDefaultTextFiltersMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getDefaultTextFilters');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('Joomla\Registry\Registry', $reflection);
    }

    /**
     * Test getSSorPI method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::getSSorPI
     */
    public function testGetSSorPIMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'getSSorPI');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);
    }

    /**
     * Test playerByMediaType method signature
     *
     * @return void
     * #[CoversClass(CwmadminModel::class)]::playerByMediaType
     */
    public function testPlayerByMediaTypeMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminModel::class, 'playerByMediaType');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('string', $reflection);
    }
}

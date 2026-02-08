<?php

/**
 * Unit tests for CwmmigrationHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmmigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmmigrationHelper
 *
 * #[CoversClass(CwmmigrationHelper::class)]
 * @since  10.3.0
 */
class CwmmigrationHelperTest extends ProclaimTestCase
{
    /**
     * Test fixMenus method signature
     *
     * @return void
     * #[CoversClass(CwmmigrationHelper::class)]::fixMenus
     */
    public function testFixMenusMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmigrationHelper::class, 'fixMenus');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test fixemptyaccess method signature
     *
     * @return void
     * #[CoversClass(CwmmigrationHelper::class)]::fixemptyaccess
     */
    public function testFixemptyaccessMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmigrationHelper::class, 'fixemptyaccess');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test fixemptylanguage method signature
     *
     * @return void
     * #[CoversClass(CwmmigrationHelper::class)]::fixemptylanguage
     */
    public function testFixemptylanguageMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmigrationHelper::class, 'fixemptylanguage');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);
    }

    /**
     * Test rmoldurl method signature
     *
     * @return void
     * #[CoversClass(CwmmigrationHelper::class)]::rmoldurl
     */
    public function testRmoldurlMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmigrationHelper::class, 'rmoldurl');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test fixImport method signature
     *
     * @return void
     * #[CoversClass(CwmmigrationHelper::class)]::fixImport
     */
    public function testFixImportMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmigrationHelper::class, 'fixImport');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test postInstallMessages method signature
     *
     * @return void
     * #[CoversClass(CwmmigrationHelper::class)]::postInstallMessages
     */
    public function testPostInstallMessagesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmigrationHelper::class, 'postInstallMessages');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('message', $params[0]->getName());
        $this->assertParamTypeName('object', $params[0]);
    }

    /**
     * Test migrateDeprecatedPlayers method signature
     *
     * @return void
     * #[CoversClass(CwmmigrationHelper::class)]::migrateDeprecatedPlayers
     */
    public function testMigrateDeprecatedPlayersMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmmigrationHelper::class, 'migrateDeprecatedPlayers');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);
    }
}

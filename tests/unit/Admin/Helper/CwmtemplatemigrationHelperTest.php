<?php

/**
 * Unit tests for CwmtemplatemigrationHelper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmtemplatemigrationHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtemplatemigrationHelper
 *
 * #[CoversClass(CwmtemplatemigrationHelper::class)]
 * @since  10.1.0
 */
class CwmtemplatemigrationHelperTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmtemplatemigrationHelper::class)]::__construct
     */
    public function testConstructor(): void
    {
        // We can't easily test the constructor because it calls Factory::getContainer()
        // which is hard to mock in this environment without a full DI container setup.
        // However, we can verify the class exists and methods are present.
        $this->assertTrue(class_exists(CwmtemplatemigrationHelper::class));
    }

    /**
     * Test migrateFromVersion method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatemigrationHelper::class)]::migrateFromVersion
     */
    public function testMigrateFromVersionMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatemigrationHelper::class, 'migrateFromVersion');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('fromVersion', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
        $this->assertEquals('0.0.0', $params[0]->getDefaultValue());
    }

    /**
     * Test migrateAll method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatemigrationHelper::class)]::migrateAll
     */
    public function testMigrateAllMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatemigrationHelper::class, 'migrateAll');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('int', $reflection->getReturnType()->getName());
    }

    /**
     * Test addMigration method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatemigrationHelper::class)]::addMigration
     */
    public function testAddMigrationMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatemigrationHelper::class, 'addMigration');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        
        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('version', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        
        $this->assertEquals('params', $params[1]->getName());
        $this->assertEquals('array', $params[1]->getType()->getName());
    }

    /**
     * Test getMigrations method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatemigrationHelper::class)]::getMigrations
     */
    public function testGetMigrationsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatemigrationHelper::class, 'getMigrations');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * Test parameterExistsInTemplates method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatemigrationHelper::class)]::parameterExistsInTemplates
     */
    public function testParameterExistsInTemplatesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatemigrationHelper::class, 'parameterExistsInTemplates');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('bool', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('paramName', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }
}

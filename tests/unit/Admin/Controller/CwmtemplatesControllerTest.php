<?php

/**
 * Unit tests for CwmtemplatesController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmtemplatesController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtemplatesController
 *
 * #[CoversClass(CwmtemplatesController::class)]
 * @since  10.0.0
 */
class CwmtemplatesControllerTest extends ProclaimTestCase
{
    /**
     * Test templateImport method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesController::class)]::templateImport
     */
    public function testTemplateImportMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesController::class, 'templateImport');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is CwmtemplatesController|int, which reflection might show differently
    }

    /**
     * Test performDB method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesController::class)]::performDB
     */
    public function testPerformDBMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesController::class, 'performDB');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertEquals('void', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('query', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
    }

    /**
     * Test templateExport method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesController::class)]::templateExport
     */
    public function testTemplateExportMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesController::class, 'templateExport');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is bool|CwmtemplatesController, which reflection might show differently
    }

    /**
     * Test getExportSetting method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesController::class)]::getExportSetting
     */
    public function testGetExportSettingMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesController::class, 'getExportSetting');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertEquals('string', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('result', $params[0]->getName());
        // No type hint in method signature for result
    }

    /**
     * Test getTemplate method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesController::class)]::getTemplate
     */
    public function testGetTemplateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesController::class, 'getTemplate');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        // Return type is bool|string, which reflection might show differently
        
        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('template', $params[0]->getName());
        // No type hint in method signature for template
    }

    /**
     * Test getModel method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesController::class)]::getModel
     */
    public function testGetModelMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesController::class, 'getModel');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('Joomla\CMS\MVC\Model\BaseDatabaseModel', $reflection->getReturnType()->getName());

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertEquals('string', $params[0]->getType()->getName());
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('prefix', $params[1]->getName());
        $this->assertEquals('string', $params[1]->getType()->getName());
        $this->assertTrue($params[1]->isOptional());
        
        $this->assertEquals('config', $params[2]->getName());
        $this->assertEquals('array', $params[2]->getType()->getName());
        $this->assertTrue($params[2]->isOptional());
    }

    /**
     * Test getQuickIconTemplates method signature
     *
     * @return void
     * #[CoversClass(CwmtemplatesController::class)]::getQuickIconTemplates
     */
    public function testGetQuickIconTemplatesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmtemplatesController::class, 'getQuickIconTemplates');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}

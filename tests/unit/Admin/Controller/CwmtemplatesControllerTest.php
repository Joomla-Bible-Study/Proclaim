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
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('query', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
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
        $this->assertReturnTypeName('string', $reflection);

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
        $this->assertReturnTypeName('Joomla\CMS\MVC\Model\BaseDatabaseModel', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(3, $params);
        $this->assertEquals('name', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('prefix', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->isOptional());

        $this->assertEquals('config', $params[2]->getName());
        $this->assertParamTypeName('array', $params[2]);
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
        $this->assertReturnTypeName('void', $reflection);
    }
}

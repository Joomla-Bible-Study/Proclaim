<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmtemplatesController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Template import and export must be gated by the template-code section
 * permission, before they touch a file.
 *
 * Template code is PHP the front end executes, so writing it (import) or reading
 * it (export) has to require the section's own permission -- not merely the
 * `core.manage` that reaching the component grants. The gate is checked over the
 * source because the controller cannot be exercised without a full request; what
 * matters and is asserted is that the check exists, targets the right asset, and
 * runs before any file work.
 *
 * @since __DEPLOY_VERSION__
 */
class TemplateImportExportAuthTest extends ProclaimTestCase
{
    /**
     * The source body of a controller method.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $ref   = new \ReflectionMethod(CwmtemplatesController::class, $method);
        $lines = file($ref->getFileName());

        return implode('', \array_slice($lines, $ref->getStartLine() - 1, $ref->getEndLine() - $ref->getStartLine() + 1));
    }

    #[TestDox('templateImport requires core.create on the templatecode section before reading the upload')]
    public function testImportGatedBeforeFileRead(): void
    {
        $body = self::methodBody('templateImport');

        $gate = strpos($body, "userCanWriteTemplateCode('core.create')");
        $read = strpos($body, "template_import");

        $this->assertNotFalse($gate, 'templateImport must call the permission gate.');
        $this->assertNotFalse($read, 'sanity: templateImport reads the uploaded file.');
        $this->assertLessThan($read, $gate, 'The permission check must run before the file is read.');
    }

    #[TestDox('templateExport requires core.edit on the templatecode section before reading the request')]
    public function testExportGatedBeforeWork(): void
    {
        $body = self::methodBody('templateExport');

        $gate = strpos($body, "userCanWriteTemplateCode('core.edit')");
        $work = strpos($body, "template_export");

        $this->assertNotFalse($gate, 'templateExport must call the permission gate.');
        $this->assertNotFalse($work, 'sanity: templateExport reads the requested template.');
        $this->assertLessThan($work, $gate, 'The permission check must run before the export work.');
    }

    #[TestDox('The gate authorises against the templatecode section asset')]
    public function testGateChecksTheSectionAsset(): void
    {
        $body = self::methodBody('userCanWriteTemplateCode');

        $this->assertStringContainsString('authorise(', $body, 'The gate must call authorise().');
        $this->assertStringContainsString("'com_proclaim.templatecode'", $body, 'It must target the templatecode section, not the component.');
    }
}

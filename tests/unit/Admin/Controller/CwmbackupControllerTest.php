<?php

/**
 * Unit tests for CwmbackupController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmbackupController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmbackupController
 *
 * #[CoversClass(CwmbackupController::class)]
 * @since  10.1.0
 */
class CwmbackupControllerTest extends ProclaimTestCase
{
    /**
     * Test getExportTablesXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::getExportTablesXHR
     */
    public function testGetExportTablesXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'getExportTablesXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test exportTableXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::exportTableXHR
     */
    public function testExportTableXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'exportTableXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test finalizeExportXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::finalizeExportXHR
     */
    public function testFinalizeExportXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'finalizeExportXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test downloadExportXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::downloadExportXHR
     */
    public function testDownloadExportXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'downloadExportXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test uploadImportFileXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::uploadImportFileXHR
     */
    public function testUploadImportFileXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'uploadImportFileXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test prepareImportXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::prepareImportXHR
     */
    public function testPrepareImportXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'prepareImportXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getImportInfoXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::getImportInfoXHR
     */
    public function testGetImportInfoXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'getImportInfoXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test importBatchXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::importBatchXHR
     */
    public function testImportBatchXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'importBatchXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test finalizeImportXHR method signature
     *
     * @return void
     * #[CoversClass(CwmbackupController::class)]::finalizeImportXHR
     */
    public function testFinalizeImportXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmbackupController::class, 'finalizeImportXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }
}

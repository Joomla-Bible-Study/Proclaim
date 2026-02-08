<?php

/**
 * Unit tests for BibleImporter
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Bible;

use CWM\Component\Proclaim\Administrator\Bible\BibleImporter;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for BibleImporter
 *
 * @since  10.1.0
 */
class BibleImporterTest extends ProclaimTestCase
{
    /**
     * Test class exists and has expected methods
     *
     * @return void
     */
    public function testClassExists(): void
    {
        $this->assertTrue(class_exists(BibleImporter::class));
    }

    /**
     * Test downloadAndImport method signature
     *
     * @return void
     */
    public function testDownloadAndImportMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(BibleImporter::class, 'downloadAndImport');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('abbreviation', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test importFromJson method signature
     *
     * @return void
     */
    public function testImportFromJsonMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(BibleImporter::class, 'importFromJson');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('int', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('json', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
        $this->assertEquals('abbreviation', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
    }

    /**
     * Test removeTranslation method signature
     *
     * @return void
     */
    public function testRemoveTranslationMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(BibleImporter::class, 'removeTranslation');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('abbreviation', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test isInstalled method signature
     *
     * @return void
     */
    public function testIsInstalledMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(BibleImporter::class, 'isInstalled');

        $this->assertTrue($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('abbreviation', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test importFromJson returns -1 for invalid JSON
     *
     * @return void
     */
    public function testImportFromJsonReturnsNegativeForInvalidJson(): void
    {
        // importFromJson calls Factory::getContainer() which isn't available in unit tests
        // but we can verify it returns -1 for truly invalid JSON (before DB access)
        $result = BibleImporter::importFromJson('not valid json', 'test');
        $this->assertSame(-1, $result);
    }
}

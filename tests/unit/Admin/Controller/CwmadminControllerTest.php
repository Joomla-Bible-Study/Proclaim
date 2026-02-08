<?php

/**
 * Unit tests for CwmadminController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmadminController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmadminController
 *
 * #[CoversClass(CwmadminController::class)]
 * @since  10.0.0
 */
class CwmadminControllerTest extends ProclaimTestCase
{
    /**
     * Test tools method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::tools
     */
    public function testToolsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'tools');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test changePlayers method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::changePlayers
     */
    public function testChangePlayersMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'changePlayers');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test changePopup method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::changePopup
     */
    public function testChangePopupMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'changePopup');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test mediaimages method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::mediaimages
     */
    public function testMediaimagesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'mediaimages');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test resetHits method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::resetHits
     */
    public function testResetHitsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'resetHits');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test resetDownloads method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::resetDownloads
     */
    public function testResetDownloadsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'resetDownloads');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test resetPlays method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::resetPlays
     */
    public function testResetPlaysMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'resetPlays');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test back method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::back
     */
    public function testBackMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'back');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test convertSermonSpeaker method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::convertSermonSpeaker
     */
    public function testConvertSermonSpeakerMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'convertSermonSpeaker');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test convertPreachIt method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::convertPreachIt
     */
    public function testConvertPreachItMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'convertPreachIt');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test dbReset method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::dbReset
     */
    public function testDbResetMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'dbReset');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test aliasUpdate method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::aliasUpdate
     */
    public function testAliasUpdateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'aliasUpdate');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test doimport method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::doimport
     */
    public function testDoimportMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'doimport');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('parent', $params[0]->getName());
        $this->assertParamTypeName('bool', $params[0]);
        $this->assertTrue($params[0]->isOptional());
    }

    /**
     * Test copyTables method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::copyTables
     */
    public function testCopyTablesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'copyTables');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('oldprefix', $params[0]->getName());
        $this->assertParamTypeName('string', $params[0]);
    }

    /**
     * Test import method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::import
     */
    public function testImportMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'import');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test export method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::export
     */
    public function testExportMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'export');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getThumbnailListXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::getThumbnailListXHR
     */
    public function testGetThumbnailListXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'getThumbnailListXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test createThumbnailXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::createThumbnailXHR
     */
    public function testCreateThumbnailXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'createThumbnailXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test doArchive method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::doArchive
     */
    public function testDoArchiveMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'doArchive');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test submit method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::submit
     */
    public function testSubmitMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'submit');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('bool', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('key', $params[0]->getName());
        $this->assertParamTypeName('int', $params[0]);
        $this->assertTrue($params[0]->allowsNull());
        $this->assertTrue($params[0]->isOptional());

        $this->assertEquals('urlVar', $params[1]->getName());
        $this->assertParamTypeName('string', $params[1]);
        $this->assertTrue($params[1]->allowsNull());
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test getMigrationCountsXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::getMigrationCountsXHR
     */
    public function testGetMigrationCountsXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'getMigrationCountsXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getMigrationBatchXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::getMigrationBatchXHR
     */
    public function testGetMigrationBatchXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'getMigrationBatchXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test migrateRecordXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::migrateRecordXHR
     */
    public function testMigrateRecordXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'migrateRecordXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getOrphanedFoldersXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::getOrphanedFoldersXHR
     */
    public function testGetOrphanedFoldersXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'getOrphanedFoldersXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test deleteOrphanedFoldersXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::deleteOrphanedFoldersXHR
     */
    public function testDeleteOrphanedFoldersXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'deleteOrphanedFoldersXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getPlayerStatsXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::getPlayerStatsXHR
     */
    public function testGetPlayerStatsXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'getPlayerStatsXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getPopupStatsXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::getPopupStatsXHR
     */
    public function testGetPopupStatsXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'getPopupStatsXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test doArchiveXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::doArchiveXHR
     */
    public function testDoArchiveXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'doArchiveXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test aliasUpdateXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::aliasUpdateXHR
     */
    public function testAliasUpdateXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'aliasUpdateXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test changePlayersXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::changePlayersXHR
     */
    public function testChangePlayersXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'changePlayersXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test changePopupXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::changePopupXHR
     */
    public function testChangePopupXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'changePopupXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test changePlayerByMediaTypeXHR method signature
     *
     * @return void
     * #[CoversClass(CwmadminController::class)]::changePlayerByMediaTypeXHR
     */
    public function testChangePlayerByMediaTypeXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmadminController::class, 'changePlayerByMediaTypeXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }
}

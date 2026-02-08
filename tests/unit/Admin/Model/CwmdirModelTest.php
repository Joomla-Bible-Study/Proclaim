<?php

/**
 * Unit tests for CwmdirModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmdirModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmdirModel
 *
 * #[CoversClass(CwmdirModel::class)]
 * @since  10.0.0
 */
class CwmdirModelTest extends ProclaimTestCase
{
    /**
     * Test getBreadcrumbs method signature
     *
     * @return void
     * #[CoversClass(CwmdirModel::class)]::getBreadcrumbs
     */
    public function testGetBreadcrumbsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdirModel::class, 'getBreadcrumbs');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test getCurrentDir method signature
     *
     * @return void
     * #[CoversClass(CwmdirModel::class)]::getCurrentDir
     */
    public function testGetCurrentDirMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdirModel::class, 'getCurrentDir');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertReturnTypeName('string', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(2, $params);
        $this->assertEquals('fullPath', $params[0]->getName());
        // No type hint in method signature for fullPath
        $this->assertTrue($params[0]->isOptional());
        
        $this->assertEquals('separator', $params[1]->getName());
        // No type hint in method signature for separator
        $this->assertTrue($params[1]->isOptional());
    }

    /**
     * Test setDirectoryState method signature
     *
     * @return void
     * #[CoversClass(CwmdirModel::class)]::setDirectoryState
     */
    public function testSetDirectoryStateMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdirModel::class, 'setDirectoryState');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertReturnTypeName('void', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('directoryPath', $params[0]->getName());
        // No type hint in method signature for directoryPath
    }

    /**
     * Test getFolders method signature
     *
     * @return void
     * #[CoversClass(CwmdirModel::class)]::getFolders
     */
    public function testGetFoldersMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdirModel::class, 'getFolders');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test setFolderInfo method signature
     *
     * @return void
     * #[CoversClass(CwmdirModel::class)]::setFolderInfo
     */
    public function testSetFolderInfoMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdirModel::class, 'setFolderInfo');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('folderPaths', $params[0]->getName());
        // No type hint in method signature for folderPaths
    }

    /**
     * Test getFiles method signature
     *
     * @return void
     * #[CoversClass(CwmdirModel::class)]::getFiles
     */
    public function testGetFilesMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdirModel::class, 'getFiles');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('array', $reflection);
    }

    /**
     * Test setFileInfo method signature
     *
     * @return void
     * #[CoversClass(CwmdirModel::class)]::setFileInfo
     */
    public function testSetFileInfoMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmdirModel::class, 'setFileInfo');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPrivate());
        $this->assertReturnTypeName('array', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('filePaths', $params[0]->getName());
        // No type hint in method signature for filePaths
    }
}

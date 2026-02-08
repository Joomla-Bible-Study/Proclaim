<?php

/**
 * Unit tests for CwmassetsController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmassetsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmassetsController
 *
 * #[CoversClass(CwmassetsController::class)]
 * @since  10.0.0
 */
class CwmassetsControllerTest extends ProclaimTestCase
{
    /**
     * Test execute method signature
     *
     * @return void
     * #[CoversClass(CwmassetsController::class)]::execute
     */
    public function testExecuteMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsController::class, 'execute');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('mixed', $reflection);

        $params = $reflection->getParameters();
        $this->assertCount(1, $params);
        $this->assertEquals('task', $params[0]->getName());
        // No type hint in method signature for task
    }

    /**
     * Test checkassets method signature
     *
     * @return void
     * #[CoversClass(CwmassetsController::class)]::checkassets
     */
    public function testCheckassetsMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsController::class, 'checkassets');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test clear method signature
     *
     * @return void
     * #[CoversClass(CwmassetsController::class)]::clear
     */
    public function testClearMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsController::class, 'clear');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test checkAssetsXHR method signature
     *
     * @return void
     * #[CoversClass(CwmassetsController::class)]::checkAssetsXHR
     */
    public function testCheckAssetsXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsController::class, 'checkAssetsXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test getAssetTablesXHR method signature
     *
     * @return void
     * #[CoversClass(CwmassetsController::class)]::getAssetTablesXHR
     */
    public function testGetAssetTablesXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsController::class, 'getAssetTablesXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test fixAssetBatchXHR method signature
     *
     * @return void
     * #[CoversClass(CwmassetsController::class)]::fixAssetBatchXHR
     */
    public function testFixAssetBatchXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsController::class, 'fixAssetBatchXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test rebuildAssetTreeXHR method signature
     *
     * @return void
     * #[CoversClass(CwmassetsController::class)]::rebuildAssetTreeXHR
     */
    public function testRebuildAssetTreeXHRMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmassetsController::class, 'rebuildAssetTreeXHR');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertReturnTypeName('void', $reflection);
    }
}

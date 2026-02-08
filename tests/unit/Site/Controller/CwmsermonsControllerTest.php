<?php

/**
 * Unit tests for CwmsermonsController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Controller;

use CWM\Component\Proclaim\Site\Controller\CwmsermonsController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmsermonsController
 *
 * #[CoversClass(CwmsermonsController::class)]
 * @since  10.0.0
 */
class CwmsermonsControllerTest extends ProclaimTestCase
{
    /**
     * Test download method signature
     *
     * @return void
     * #[CoversClass(CwmsermonsController::class)]::download
     */
    public function testDownloadMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonsController::class, 'download');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }

    /**
     * Test playHit method signature
     *
     * @return void
     * #[CoversClass(CwmsermonsController::class)]::playHit
     */
    public function testPlayHitMethodSignature(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonsController::class, 'playHit');

        $this->assertFalse($reflection->isStatic());
        $this->assertTrue($reflection->isPublic());
        $this->assertEquals('void', $reflection->getReturnType()->getName());
    }
}

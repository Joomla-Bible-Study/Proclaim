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

/**
 * Test class for CwmsermonsController
 *
 * @since  10.1.0
 */
class CwmsermonsControllerTest extends ProclaimTestCase
{
    /**
     * Test that filterAjax method exists and is public
     *
     * @return void
     */
    public function testFilterAjaxMethodExists(): void
    {
        $this->assertTrue(
            method_exists(CwmsermonsController::class, 'filterAjax'),
            'filterAjax() method should exist on CwmsermonsController'
        );

        $reflection = new \ReflectionMethod(CwmsermonsController::class, 'filterAjax');
        $this->assertTrue($reflection->isPublic(), 'filterAjax() should be public');
    }

    /**
     * Test that filterAjax has correct return type
     *
     * @return void
     */
    public function testFilterAjaxReturnType(): void
    {
        $reflection = new \ReflectionMethod(CwmsermonsController::class, 'filterAjax');
        $this->assertReturnTypeName('void', $reflection);
    }

    /**
     * Test that playHitAjax method exists (existing AJAX endpoint)
     *
     * @return void
     */
    public function testPlayHitAjaxMethodExists(): void
    {
        $this->assertTrue(
            method_exists(CwmsermonsController::class, 'playHitAjax'),
            'playHitAjax() method should exist on CwmsermonsController'
        );
    }

    /**
     * Test that download method exists (existing method)
     *
     * @return void
     */
    public function testDownloadMethodExists(): void
    {
        $this->assertTrue(
            method_exists(CwmsermonsController::class, 'download'),
            'download() method should exist on CwmsermonsController'
        );
    }
}

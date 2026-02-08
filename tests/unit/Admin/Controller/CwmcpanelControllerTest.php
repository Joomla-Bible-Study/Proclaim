<?php

/**
 * Unit tests for CwmcpanelController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmcpanelController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmcpanelController
 *
 * #[CoversClass(CwmcpanelController::class)]
 * @since  10.0.0
 */
class CwmcpanelControllerTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmcpanelController::class)]::__construct
     */
    public function testConstructor(): void
    {
        $controller = new CwmcpanelController();
        $this->assertInstanceOf(CwmcpanelController::class, $controller);
    }
}

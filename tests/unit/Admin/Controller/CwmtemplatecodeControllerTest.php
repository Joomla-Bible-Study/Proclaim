<?php

/**
 * Unit tests for CwmtemplatecodeController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmtemplatecodeController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtemplatecodeController
 *
 * #[CoversClass(CwmtemplatecodeController::class)]
 * @since  10.0.0
 */
class CwmtemplatecodeControllerTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmtemplatecodeController::class)]::__construct
     */
    public function testConstructor(): void
    {
        $controller = new CwmtemplatecodeController();
        $this->assertInstanceOf(CwmtemplatecodeController::class, $controller);
    }
}

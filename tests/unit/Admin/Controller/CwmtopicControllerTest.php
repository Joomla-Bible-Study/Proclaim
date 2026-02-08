<?php

/**
 * Unit tests for CwmtopicController
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Administrator\Controller\CwmtopicController;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test class for CwmtopicController
 *
 * #[CoversClass(CwmtopicController::class)]
 * @since  10.0.0
 */
class CwmtopicControllerTest extends ProclaimTestCase
{
    /**
     * Test constructor
     *
     * @return void
     * #[CoversClass(CwmtopicController::class)]::__construct
     */
    public function testConstructor(): void
    {
        $controller = new CwmtopicController();
        $this->assertInstanceOf(CwmtopicController::class, $controller);
    }
}

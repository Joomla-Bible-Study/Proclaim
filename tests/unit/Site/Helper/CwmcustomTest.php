<?php

/**
 * Unit tests for Cwmcustom Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Site\Helper\Cwmcustom;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmcustom helper
 *
 * @since  10.0.0
 */
class CwmcustomTest extends ProclaimTestCase
{
    /**
     * Test getElementnumber returns correct values
     *
     * @return void
     */
    public function testGetElementnumberValues(): void
    {
        $this->assertEquals(1, Cwmcustom::getElementnumber('scripture1'));
        $this->assertEquals(5, Cwmcustom::getElementnumber('studytitle'));
        $this->assertEquals(7, Cwmcustom::getElementnumber('teachername'));
        $this->assertEquals(0, Cwmcustom::getElementnumber('nonexistent'));
    }
}

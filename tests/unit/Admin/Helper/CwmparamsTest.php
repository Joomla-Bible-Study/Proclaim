<?php

/**
 * Unit tests for Cwmparams Helper
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for Cwmparams helper
 *
 * @since  10.0.0
 */
class CwmparamsTest extends ProclaimTestCase
{
    /**
     * Test extension name constant
     *
     * @return void
     */
    public function testExtensionNameIsCorrect(): void
    {
        $this->assertEquals('com_proclaim', Cwmparams::$extension);
    }
}

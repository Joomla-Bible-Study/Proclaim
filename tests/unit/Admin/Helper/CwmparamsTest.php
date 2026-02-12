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
     * Reset static properties between tests
     *
     * @return void
     */
    protected function tearDown(): void
    {
        // Reset static properties using reflection
        $reflection = new \ReflectionClass(Cwmparams::class);

        if ($reflection->hasProperty('templateId')) {
            $prop = $reflection->getProperty('templateId');
            $prop->setAccessible(true);
            $prop->setValue(null, 1);
        }

        parent::tearDown();
    }

    /**
     * Test extension name constant
     *
     * @return void
     */
    public function testExtensionNameIsCorrect(): void
    {
        $this->assertEquals('com_proclaim', Cwmparams::$extension);
    }

    /**
     * Test default template ID
     *
     * @return void
     */
    public function testDefaultTemplateIdIsOne(): void
    {
        $this->assertEquals(1, Cwmparams::$templateId);
    }
}

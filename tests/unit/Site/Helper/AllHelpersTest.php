<?php

/**
 * Unit tests for All Site Helpers
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Site Helpers
 *
 * @since  10.0.0
 */
class AllHelpersTest extends ProclaimTestCase
{
    /**
     * Test all helpers exist and have correct namespace
     *
     * @return void
     */
    public function testAllHelpersSanity(): void
    {
        $helperDir = JPATH_ROOT . '/site/src/Helper';
        $files     = glob($helperDir . '/*.php');

        foreach ($files as $file) {
            $content   = file_get_contents($file);
            $className = basename($file, '.php');

            // Check namespace
            $this->assertStringContainsString(
                'namespace CWM\Component\Proclaim\Site\Helper;',
                $content,
                "File $className should have correct namespace"
            );

            // Check class definition
            $this->assertStringContainsString(
                "class $className",
                $content,
                "File $className should define class $className"
            );
        }
    }
}

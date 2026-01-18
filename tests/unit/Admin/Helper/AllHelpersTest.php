<?php

/**
 * Unit tests for All Admin Helpers
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Admin Helpers
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
        $helperDir = JPATH_ROOT . '/admin/src/Helper';
        $files = glob($helperDir . '/*.php');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $className = basename($file, '.php');
            
            // Skip if it's not a class file (e.g. tagDefinitions.helper.php might not be a class)
            if (strpos($className, '.helper') !== false) {
                continue;
            }

            // Check namespace
            $this->assertStringContainsString(
                'namespace CWM\Component\Proclaim\Administrator\Helper;',
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

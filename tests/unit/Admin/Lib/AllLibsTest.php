<?php

/**
 * Unit tests for All Admin Libs
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Admin Libs
 *
 * @since  10.0.0
 */
class AllLibsTest extends ProclaimTestCase
{
    /**
     * Test all libs exist and have correct namespace
     *
     * @return void
     */
    public function testAllLibsSanity(): void
    {
        $libDir = JPATH_ROOT . '/admin/src/Lib';
        $files = glob($libDir . '/*.php');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $className = basename($file, '.php');
            
            // Check namespace
            $this->assertStringContainsString(
                'namespace CWM\Component\Proclaim\Administrator\Lib;',
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

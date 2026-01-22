<?php

/**
 * Unit tests for All Admin Controllers
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Controller;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Admin Controllers
 *
 * @since  10.0.0
 */
class AllControllersTest extends ProclaimTestCase
{
    /**
     * Test all controllers exist and have correct namespace
     *
     * @return void
     */
    public function testAllControllersSanity(): void
    {
        $controllerDir = JPATH_ROOT . '/admin/src/Controller';
        $files         = glob($controllerDir . '/*.php');

        foreach ($files as $file) {
            $content   = file_get_contents($file);
            $className = basename($file, '.php');

            // Check namespace
            $this->assertStringContainsString(
                'namespace CWM\Component\Proclaim\Administrator\Controller;',
                $content,
                "File $className should have correct namespace"
            );

            // Check class definition
            $this->assertStringContainsString(
                "class $className",
                $content,
                "File $className should define class $className"
            );

            // Check inheritance (most extend BaseController or AdminController or FormController)
            $this->assertMatchesRegularExpression(
                '/class\s+' . $className . '\s+extends\s+[a-zA-Z0-9_]+/',
                $content,
                "File $className should extend a base class"
            );
        }
    }
}

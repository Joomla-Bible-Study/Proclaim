<?php

/**
 * Unit tests for All Admin Views
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\View;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Admin Views
 *
 * @since  10.0.0
 */
class AllViewsTest extends ProclaimTestCase
{
    /**
     * Test all views exist and have correct namespace
     *
     * @return void
     */
    public function testAllViewsSanity(): void
    {
        $viewDir = JPATH_ROOT . '/admin/src/View';
        $dirs = glob($viewDir . '/*', GLOB_ONLYDIR);

        foreach ($dirs as $dir) {
            $viewName = basename($dir);
            $file = $dir . '/HtmlView.php';

            if (file_exists($file)) {
                $content = file_get_contents($file);
                
                // Check namespace
                $this->assertStringContainsString(
                    "namespace CWM\Component\Proclaim\Administrator\View\\$viewName;",
                    $content,
                    "View $viewName should have correct namespace"
                );
                
                // Check class definition
                $this->assertStringContainsString(
                    "class HtmlView",
                    $content,
                    "View $viewName should define class HtmlView"
                );
                
                // Check inheritance
                $this->assertStringContainsString(
                    'extends BaseHtmlView',
                    $content,
                    "View $viewName should extend BaseHtmlView"
                );
            }
        }
    }
}

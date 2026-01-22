<?php

/**
 * Unit tests for All Plugins
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Plugins;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Plugins
 *
 * @since  10.0.0
 */
class AllPluginsTest extends ProclaimTestCase
{
    /**
     * Test all plugins exist and have correct namespace
     *
     * @return void
     */
    public function testAllPluginsSanity(): void
    {
        $pluginsDir = JPATH_ROOT . '/plugins';
        $groups     = ['finder', 'task'];

        foreach ($groups as $group) {
            $groupDir = $pluginsDir . '/' . $group;
            if (!is_dir($groupDir)) {
                continue;
            }

            $plugins = glob($groupDir . '/*', GLOB_ONLYDIR);

            foreach ($plugins as $pluginDir) {
                $pluginName = basename($pluginDir);
                $srcDir     = $pluginDir . '/src';

                if (is_dir($srcDir)) {
                    // Check Extension
                    $extensionDir = $srcDir . '/Extension';
                    if (is_dir($extensionDir)) {
                        $files = glob($extensionDir . '/*.php');
                        foreach ($files as $file) {
                            $content   = file_get_contents($file);
                            $className = basename($file, '.php');

                            // Check namespace
                            // Namespace format: CWM\Plugin\Group\PluginName\Extension
                            $this->assertStringContainsString(
                                'namespace CWM\Plugin\\',
                                $content,
                                "File $className in $pluginName should have correct namespace"
                            );
                        }
                    }
                }
            }
        }
    }
}

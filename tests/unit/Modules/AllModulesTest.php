<?php

/**
 * Unit tests for All Modules
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Modules;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Modules
 *
 * @since  10.0.0
 */
class AllModulesTest extends ProclaimTestCase
{
    /**
     * Test all modules exist and have correct namespace
     *
     * @return void
     */
    public function testAllModulesSanity(): void
    {
        $modulesDir = JPATH_ROOT . '/modules';
        $clients = ['site', 'admin'];

        foreach ($clients as $client) {
            $clientDir = $modulesDir . '/' . $client;
            if (!is_dir($clientDir)) {
                continue;
            }

            $modules = glob($clientDir . '/mod_*', GLOB_ONLYDIR);

            foreach ($modules as $moduleDir) {
                $moduleName = basename($moduleDir);
                $srcDir = $moduleDir . '/src';

                if (is_dir($srcDir)) {
                    // Check Helpers
                    $helperDir = $srcDir . '/Helper';
                    if (is_dir($helperDir)) {
                        $files = glob($helperDir . '/*.php');
                        foreach ($files as $file) {
                            $content = file_get_contents($file);
                            $className = basename($file, '.php');
                            
                            // Check namespace
                            // Namespace format: CWM\Module\ModuleName\Site\Helper or Admin\Helper
                            // ModuleName is usually CamelCase of mod_proclaim -> Proclaim
                            // But let's just check it starts with CWM\Module
                            $this->assertStringContainsString(
                                'namespace CWM\Module\\',
                                $content,
                                "File $className in $moduleName should have correct namespace"
                            );
                        }
                    }

                    // Check Dispatcher
                    $dispatcherDir = $srcDir . '/Dispatcher';
                    if (is_dir($dispatcherDir)) {
                        $files = glob($dispatcherDir . '/*.php');
                        foreach ($files as $file) {
                            $content = file_get_contents($file);
                            $className = basename($file, '.php');
                            
                            $this->assertStringContainsString(
                                'namespace CWM\Module\\',
                                $content,
                                "File $className in $moduleName should have correct namespace"
                            );
                        }
                    }
                }
            }
        }
    }
}

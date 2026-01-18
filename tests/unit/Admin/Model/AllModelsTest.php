<?php

/**
 * Unit tests for All Admin Models
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Admin Models
 *
 * @since  10.0.0
 */
class AllModelsTest extends ProclaimTestCase
{
    /**
     * Test all models exist and have correct namespace
     *
     * @return void
     */
    public function testAllModelsSanity(): void
    {
        $modelDir = JPATH_ROOT . '/admin/src/Model';
        $files = glob($modelDir . '/*.php');

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $className = basename($file, '.php');
            
            // Check namespace
            $this->assertStringContainsString(
                'namespace CWM\Component\Proclaim\Administrator\Model;',
                $content,
                "File $className should have correct namespace"
            );
            
            // Check class definition
            $this->assertStringContainsString(
                "class $className",
                $content,
                "File $className should define class $className"
            );
            
            // Check inheritance (most extend AdminModel or ListModel)
            // We check if it extends something, usually AdminModel, ListModel, or FormModel
            $this->assertMatchesRegularExpression(
                '/class\s+' . $className . '\s+extends\s+[a-zA-Z0-9_]+/',
                $content,
                "File $className should extend a base class"
            );
        }
    }
}

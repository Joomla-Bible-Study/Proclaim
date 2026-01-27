<?php

/**
 * Unit tests for All Admin Fields
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Field;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Admin Fields
 *
 * @since  10.0.0
 */
class AllFieldsTest extends ProclaimTestCase
{
    /**
     * Test all fields exist and have correct namespace
     *
     * @return void
     */
    public function testAllFieldsSanity(): void
    {
        $fieldDir = JPATH_ROOT . '/admin/src/Field';
        $files    = glob($fieldDir . '/*.php');

        foreach ($files as $file) {
            $content   = file_get_contents($file);
            $className = basename($file, '.php');

            // Check namespace
            $this->assertStringContainsString(
                'namespace CWM\Component\Proclaim\Administrator\Field;',
                $content,
                "File $className should have correct namespace"
            );

            // Check class definition
            $this->assertStringContainsString(
                "class $className",
                $content,
                "File $className should define class $className"
            );

            // Check inheritance (should extend FormField or ListField)
            $this->assertMatchesRegularExpression(
                '/class\s+' . $className . '\s+extends\s+[a-zA-Z0-9_]+/',
                $content,
                "File $className should extend a base class"
            );
        }
    }
}

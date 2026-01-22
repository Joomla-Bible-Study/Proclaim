<?php

/**
 * Unit tests for All Admin Tables
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for All Admin Tables
 *
 * @since  10.0.0
 */
class AllTablesTest extends ProclaimTestCase
{
    /**
     * Test all tables exist and have correct namespace
     *
     * @return void
     */
    public function testAllTablesSanity(): void
    {
        $tableDir = JPATH_ROOT . '/admin/src/Table';
        $files    = glob($tableDir . '/*.php');

        foreach ($files as $file) {
            $content   = file_get_contents($file);
            $className = basename($file, '.php');

            // Check namespace
            $this->assertStringContainsString(
                'namespace CWM\Component\Proclaim\Administrator\Table;',
                $content,
                "File $className should have correct namespace"
            );

            // Check class definition
            $this->assertStringContainsString(
                "class $className",
                $content,
                "File $className should define class $className"
            );

            // Check inheritance (should extend Table)
            $this->assertStringContainsString(
                'extends Table',
                $content,
                "File $className should extend Table"
            );
        }
    }
}

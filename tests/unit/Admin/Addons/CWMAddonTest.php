<?php

/**
 * Unit tests for CWMAddon
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Addons;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CWMAddon
 *
 * @since  10.0.0
 */
class CWMAddonTest extends ProclaimTestCase
{
    /**
     * @var string Path to the class file
     */
    private string $classFile;

    /**
     * @var string Content of the class file
     */
    private string $classContent;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->classFile    = JPATH_ROOT . '/admin/src/Addons/CWMAddon.php';
        $this->classContent = file_get_contents($this->classFile);
    }

    /**
     * Test class file exists
     *
     * @return void
     */
    public function testClassFileExists(): void
    {
        $this->assertFileExists($this->classFile);
    }

    /**
     * Test class has correct namespace
     *
     * @return void
     */
    public function testClassHasCorrectNamespace(): void
    {
        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Administrator\Addons;',
            $this->classContent
        );
    }

    /**
     * Test class definition
     *
     * @return void
     */
    public function testClassDefinition(): void
    {
        $this->assertStringContainsString('class CWMAddon', $this->classContent);
    }
}

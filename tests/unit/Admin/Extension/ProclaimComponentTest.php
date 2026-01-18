<?php

/**
 * Unit tests for ProclaimComponent
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Extension;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for ProclaimComponent
 *
 * @since  10.0.0
 */
class ProclaimComponentTest extends ProclaimTestCase
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

        $this->classFile    = JPATH_ROOT . '/admin/src/Extension/ProclaimComponent.php';
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
            'namespace CWM\Component\Proclaim\Administrator\Extension;',
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
        $this->assertStringContainsString('class ProclaimComponent', $this->classContent);
    }

    /**
     * Test class implements interfaces
     *
     * @return void
     */
    public function testClassImplementsInterfaces(): void
    {
        $this->assertStringContainsString('implements', $this->classContent);
        $this->assertStringContainsString('BootableExtensionInterface', $this->classContent);
        $this->assertStringContainsString('MVCComponent', $this->classContent);
    }
}

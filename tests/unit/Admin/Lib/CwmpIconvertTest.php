<?php

/**
 * Unit tests for CwmpIconvert
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmpIconvert
 *
 * @since  10.0.0
 */
class CwmpIconvertTest extends ProclaimTestCase
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

        $this->classFile    = JPATH_ROOT . '/admin/src/Lib/CwmpIconvert.php';
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
            'namespace CWM\Component\Proclaim\Administrator\Lib;',
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
        $this->assertStringContainsString('class CwmpIconvert', $this->classContent);
    }
}

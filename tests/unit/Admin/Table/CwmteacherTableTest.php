<?php

/**
 * Unit tests for CwmteacherTable
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmteacherTable
 *
 * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
 * @since  10.0.0
 */
class CwmteacherTableTest extends ProclaimTestCase
{
    /**
     * Flag to check if Joomla framework is available
     *
     * @var bool
     */
    private bool $joomlaAvailable = false;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Check if Joomla Table class exists
        $this->joomlaAvailable = class_exists('Joomla\CMS\Table\Table');
    }

    /**
     * Skip test if Joomla is not available
     *
     * @return void
     */
    private function skipIfNoJoomla(): void
    {
        if (!$this->joomlaAvailable) {
            $this->markTestSkipped('Joomla CMS framework is not available for this test.');
        }
    }

    /**
     * Test table class file exists
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';

        $this->assertFileExists($filePath);
    }

    /**
     * Test table class uses correct namespace
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableClassHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Administrator\Table;',
            $content
        );
    }

    /**
     * Test table class extends Joomla Table
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableClassExtendsJoomlaTable(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('extends Table', $content);
        $this->assertStringContainsString('use Joomla\CMS\Table\Table;', $content);
    }

    /**
     * Test table has bind method
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableHasBindMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('public function bind($src, $ignore = \'\')', $content);
    }

    /**
     * Test table has store method
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableHasStoreMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('public function store($updateNulls = false)', $content);
    }

    /**
     * Test table defines teachername property
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableDefinesTeachernameProperty(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('public ?string $teachername = null;', $content);
    }

    /**
     * Test table defines alias property
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableDefinesAliasProperty(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('public string $alias;', $content);
    }

    /**
     * Test table uses teachers database table
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableUsesCorrectDatabaseTable(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('#__bsms_teachers', $content);
    }

    /**
     * Test table has _getAssetName method
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableHasGetAssetNameMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('protected function _getAssetName()', $content);
        $this->assertStringContainsString('com_proclaim.teacher.', $content);
    }

    /**
     * Test table has _getAssetTitle method
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableHasGetAssetTitleMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('protected function _getAssetTitle()', $content);
        $this->assertStringContainsString('JBS Teacher:', $content);
    }

    /**
     * Test table processes params as Registry
     *
     * @return void
     * @covers \CWM\Component\Proclaim\Administrator\Table\CwmteacherTable
     */
    public function testTableProcessesParamsAsRegistry(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmteacherTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('use Joomla\Registry\Registry;', $content);
        $this->assertStringContainsString('$registry = new Registry();', $content);
    }
}

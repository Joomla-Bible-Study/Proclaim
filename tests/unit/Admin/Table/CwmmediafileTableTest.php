<?php

/**
 * Unit tests for CwmmediafileTable
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmmediafileTable
 *
 * @since  10.0.0
 */
class CwmmediafileTableTest extends ProclaimTestCase
{
    /**
     * Test table class file exists
     *
     * @return void
     */
    public function testTableClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';

        $this->assertFileExists($filePath);
    }

    /**
     * Test table class uses correct namespace
     *
     * @return void
     */
    public function testTableClassHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
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
     */
    public function testTableClassExtendsJoomlaTable(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('extends Table', $content);
        $this->assertStringContainsString('use Joomla\CMS\Table\Table;', $content);
    }

    /**
     * Test table uses mediafiles database table
     *
     * @return void
     */
    public function testTableUsesCorrectDatabaseTable(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('#__bsms_mediafiles', $content);
    }

    /**
     * Test table defines server_id property
     *
     * @return void
     */
    public function testTableDefinesServerIdProperty(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('$server_id', $content);
    }

    /**
     * Test table defines study_id property
     *
     * @return void
     */
    public function testTableDefinesStudyIdProperty(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('$study_id', $content);
    }

    /**
     * Test table has bind method
     *
     * @return void
     */
    public function testTableHasBindMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('function bind(', $content);
    }

    /**
     * Test table has store method
     *
     * @return void
     */
    public function testTableHasStoreMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('function store(', $content);
    }

    /**
     * Test table has _getAssetName method
     *
     * @return void
     */
    public function testTableHasGetAssetNameMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('function _getAssetName()', $content);
        $this->assertStringContainsString('com_proclaim.mediafile.', $content);
    }

    /**
     * Test table processes params as Registry
     *
     * @return void
     */
    public function testTableProcessesParamsAsRegistry(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmediafileTable.php';
        $content = file_get_contents($filePath);

        $this->assertStringContainsString('use Joomla\Registry\Registry;', $content);
    }
}
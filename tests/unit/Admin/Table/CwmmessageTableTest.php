<?php

/**
 * Unit tests for CwmmessageTable
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmmessageTable
 *
 * @since  10.0.0
 */
class CwmmessageTableTest extends ProclaimTestCase
{
    /**
     * Test table class file exists
     *
     * @return void
     */
    public function testTableClassFileExists(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php';

        $this->assertFileExists($filePath);
    }

    /**
     * Test table class uses correct namespace
     *
     * @return void
     */
    public function testTableClassHasCorrectNamespace(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php';
        $content  = file_get_contents($filePath);

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
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('extends Table', $content);
        $this->assertStringContainsString('use Joomla\CMS\Table\Table;', $content);
    }

    /**
     * Test table uses studies database table
     *
     * @return void
     */
    public function testTableUsesCorrectDatabaseTable(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('#__bsms_studies', $content);
    }

    /**
     * Test table defines id property
     *
     * @return void
     */
    public function testTableDefinesIdProperty(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('public $id', $content);
    }

    /**
     * Test table has store method
     *
     * @return void
     */
    public function testTableHasStoreMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('function store(', $content);
    }

    /**
     * Test table has _getAssetName method
     *
     * @return void
     */
    public function testTableHasGetAssetNameMethod(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('function _getAssetName()', $content);
        $this->assertStringContainsString('com_proclaim.message.', $content);
    }

    /**
     * Test table uses Cwmassets
     *
     * @return void
     */
    public function testTableUsesCwmassets(): void
    {
        $filePath = JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php';
        $content  = file_get_contents($filePath);

        $this->assertStringContainsString('use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;', $content);
    }
}

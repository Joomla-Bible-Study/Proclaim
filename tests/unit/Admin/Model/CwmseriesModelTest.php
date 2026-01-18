<?php

/**
 * Unit tests for CwmseriesModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmseriesModel
 *
 * @since  10.0.0
 */
class CwmseriesModelTest extends ProclaimTestCase
{
    /**
     * @var string Path to the model class file
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

        $this->classFile    = JPATH_ROOT . '/admin/src/Model/CwmseriesModel.php';
        $this->classContent = file_get_contents($this->classFile);
    }

    /**
     * Test model class file exists
     *
     * @return void
     */
    public function testModelClassFileExists(): void
    {
        $this->assertFileExists($this->classFile);
    }

    /**
     * Test model has correct namespace
     *
     * @return void
     */
    public function testModelHasCorrectNamespace(): void
    {
        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Administrator\Model;',
            $this->classContent
        );
    }

    /**
     * Test model extends ListModel
     *
     * @return void
     */
    public function testModelExtendsListModel(): void
    {
        $this->assertStringContainsString('extends ListModel', $this->classContent);
        $this->assertStringContainsString('use Joomla\CMS\MVC\Model\ListModel;', $this->classContent);
    }

    /**
     * Test model has getItems method
     *
     * @return void
     */
    public function testModelHasGetItemsMethod(): void
    {
        $this->assertStringContainsString('public function getItems(): mixed', $this->classContent);
    }

    /**
     * Test model has populateState method
     *
     * @return void
     */
    public function testModelHasPopulateStateMethod(): void
    {
        $this->assertStringContainsString('protected function populateState($ordering = \'series.series_text\', $direction = \'asc\'): void', $this->classContent);
    }

    /**
     * Test model has getListQuery method
     *
     * @return void
     */
    public function testModelHasGetListQueryMethod(): void
    {
        $this->assertStringContainsString('protected function getListQuery(): QueryInterface|string', $this->classContent);
    }
}

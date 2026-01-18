<?php

/**
 * Unit tests for CwmmessageModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmmessageModel
 *
 * @since  10.0.0
 */
class CwmmessageModelTest extends ProclaimTestCase
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

        $this->classFile    = JPATH_ROOT . '/admin/src/Model/CwmmessageModel.php';
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
     * Test model extends AdminModel
     *
     * @return void
     */
    public function testModelExtendsAdminModel(): void
    {
        $this->assertStringContainsString('extends AdminModel', $this->classContent);
        $this->assertStringContainsString('use Joomla\CMS\MVC\Model\AdminModel;', $this->classContent);
    }

    /**
     * Test model has save method
     *
     * @return void
     */
    public function testModelHasSaveMethod(): void
    {
        $this->assertStringContainsString('public function save($data): bool', $this->classContent);
    }

    /**
     * Test model has getForm method
     *
     * @return void
     */
    public function testModelHasGetFormMethod(): void
    {
        $this->assertStringContainsString('public function getForm($data = [], $loadData = true): bool|Form', $this->classContent);
    }

    /**
     * Test model has getTopics method
     *
     * @return void
     */
    public function testModelHasGetTopicsMethod(): void
    {
        $this->assertStringContainsString('public function getTopics(): string', $this->classContent);
    }

    /**
     * Test model has getAlltopics method
     *
     * @return void
     */
    public function testModelHasGetAlltopicsMethod(): void
    {
        $this->assertStringContainsString('public function getAlltopics()', $this->classContent);
    }

    /**
     * Test model has getMediaFiles method
     *
     * @return void
     */
    public function testModelHasGetMediaFilesMethod(): void
    {
        $this->assertStringContainsString('public function getMediaFiles()', $this->classContent);
    }

    /**
     * Test model has getItem method
     *
     * @return void
     */
    public function testModelHasGetItemMethod(): void
    {
        $this->assertStringContainsString('public function getItem($pk = null): mixed', $this->classContent);
    }

    /**
     * Test model has batch methods
     *
     * @return void
     */
    public function testModelHasBatchMethods(): void
    {
        $this->assertStringContainsString('protected function batchTeacher($value, $pks, $contexts): bool', $this->classContent);
        $this->assertStringContainsString('protected function batchSeries($value, $pks, $contexts): bool', $this->classContent);
        $this->assertStringContainsString('protected function batchMessageType(string $value, array $pks, array $contexts): bool', $this->classContent);
    }
}

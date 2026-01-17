<?php

/**
 * Unit tests for CwmsermonsModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmsermonsModel
 *
 *#[CoversClass(CwmsermonsModel::class)]
 * @since  10.0.0
 */
class CwmsermonsModelTest extends ProclaimTestCase
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

        $this->classFile    = JPATH_ROOT . '/site/src/Model/CwmsermonsModel.php';
        $this->classContent = file_get_contents($this->classFile);
    }

    /**
     * Test model class file exists
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]
     */
    public function testModelClassFileExists(): void
    {
        $this->assertFileExists($this->classFile);
    }

    /**
     * Test model has correct namespace
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]
     */
    public function testModelHasCorrectNamespace(): void
    {
        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Site\Model;',
            $this->classContent
        );
    }

    /**
     * Test model extends ListModel
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]
     */
    public function testModelExtendsListModel(): void
    {
        $this->assertStringContainsString('extends ListModel', $this->classContent);
        $this->assertStringContainsString('use Joomla\CMS\MVC\Model\ListModel;', $this->classContent);
    }

    /**
     * Test model has input property
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::$input
     */
    public function testModelHasInputProperty(): void
    {
        $this->assertStringContainsString('public $input;', $this->classContent);
    }

    /**
     * Test model has context property with correct value
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::$context
     */
    public function testModelHasContextProperty(): void
    {
        $this->assertStringContainsString(
            "public \$context = 'com_proclaim.sermons.list';",
            $this->classContent
        );
    }

    /**
     * Test constructor defines filter_fields
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::__construct
     */
    public function testConstructorDefinesFilterFields(): void
    {
        $this->assertStringContainsString("'filter_fields'", $this->classContent);
    }

    /**
     * Test filter fields include studydate
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::__construct
     */
    public function testFilterFieldsIncludeStudydate(): void
    {
        $this->assertStringContainsString("'studydate'", $this->classContent);
        $this->assertStringContainsString("'study.studydate'", $this->classContent);
    }

    /**
     * Test filter fields include studytitle
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::__construct
     */
    public function testFilterFieldsIncludeStudytitle(): void
    {
        $this->assertStringContainsString("'studytitle'", $this->classContent);
        $this->assertStringContainsString("'study.studytitle'", $this->classContent);
    }

    /**
     * Test filter fields include teachername
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::__construct
     */
    public function testFilterFieldsIncludeTeachername(): void
    {
        $this->assertStringContainsString("'teachername'", $this->classContent);
        $this->assertStringContainsString("'teacher.teachername'", $this->classContent);
    }

    /**
     * Test filter fields include bookname
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::__construct
     */
    public function testFilterFieldsIncludeBookname(): void
    {
        $this->assertStringContainsString("'bookname'", $this->classContent);
        $this->assertStringContainsString("'book.bookname'", $this->classContent);
    }

    /**
     * Test filter fields include series_text
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::__construct
     */
    public function testFilterFieldsIncludeSeriesText(): void
    {
        $this->assertStringContainsString("'series_text'", $this->classContent);
        $this->assertStringContainsString("'series.series_text'", $this->classContent);
    }

    /**
     * Test filter fields include language
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]::__construct
     */
    public function testFilterFieldsIncludeLanguage(): void
    {
        $this->assertStringContainsString("'language'", $this->classContent);
        $this->assertStringContainsString("'study.language'", $this->classContent);
    }

    /**
     * Test model uses required Joomla classes
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]
     */
    public function testModelUsesRequiredJoomlaClasses(): void
    {
        $this->assertStringContainsString('use Joomla\CMS\Factory;', $this->classContent);
        $this->assertStringContainsString('use Joomla\Registry\Registry;', $this->classContent);
        $this->assertStringContainsString('use Joomla\Database\ParameterType;', $this->classContent);
    }

    /**
     * Test model uses Cwmparams helper
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]
     */
    public function testModelUsesCwmparamsHelper(): void
    {
        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;',
            $this->classContent
        );
    }

    /**
     * Test model has getListQuery method
     *
     * @return void
     *#[CoversClass(CwmsermonsModel::class)]
     */
    public function testModelHasGetListQueryMethod(): void
    {
        $this->assertStringContainsString('function getListQuery()', $this->classContent);
    }
}

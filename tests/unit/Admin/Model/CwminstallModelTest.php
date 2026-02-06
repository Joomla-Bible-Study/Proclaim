<?php

/**
 * Unit tests for CwminstallModel
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwminstallModel
 *
 * @since  10.0.0
 */
class CwminstallModelTest extends ProclaimTestCase
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

        $this->classFile    = JPATH_ROOT . '/admin/src/Model/CwminstallModel.php';
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
     * Test model has required public properties
     *
     * @return void
     */
    public function testModelHasRequiredPublicProperties(): void
    {
        $this->assertStringContainsString('public $totalSteps', $this->classContent);
        $this->assertStringContainsString('public $doneSteps', $this->classContent);
        $this->assertStringContainsString('public string $step', $this->classContent);
        $this->assertStringContainsString('public $running', $this->classContent);
        $this->assertStringContainsString('public array $callstack', $this->classContent);
        $this->assertStringContainsString('public array $installQuery', $this->classContent);
    }

    /**
     * Test model has required protected properties
     *
     * @return void
     */
    public function testModelHasRequiredProtectedProperties(): void
    {
        $this->assertStringContainsString('protected string $filePath', $this->classContent);
        $this->assertStringContainsString('protected string $phpPath', $this->classContent);
    }

    /**
     * Test model uses required classes
     *
     * @return void
     */
    public function testModelUsesRequiredClasses(): void
    {
        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Administrator\Helper\CwmdbHelper;',
            $this->classContent
        );
        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Administrator\Helper\CwmguidedtourHelper;',
            $this->classContent
        );
        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Administrator\Helper\CwmmigrationHelper;',
            $this->classContent
        );
        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Administrator\Helper\CwmtemplatemigrationHelper;',
            $this->classContent
        );
        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;',
            $this->classContent
        );
        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;',
            $this->classContent
        );
        $this->assertStringContainsString('use Joomla\CMS\Factory;', $this->classContent);
        $this->assertStringNotContainsString('use Joomla\Database\DatabaseInterface;', $this->classContent);
    }

    /**
     * Test model has startScanning method
     *
     * @return void
     */
    public function testModelHasStartScanningMethod(): void
    {
        $this->assertStringContainsString('public function startScanning()', $this->classContent);
    }

    /**
     * Test model has run method
     *
     * @return void
     */
    public function testModelHasRunMethod(): void
    {
        $this->assertStringContainsString('public function run(', $this->classContent);
    }

    /**
     * Test model has uninstall method
     *
     * @return void
     */
    public function testModelHasUninstallMethod(): void
    {
        $this->assertStringContainsString('public function uninstall()', $this->classContent);
    }

    // =========================================================================
    // Tests for private helper methods
    // =========================================================================

    /**
     * Test model has private resetStack method
     *
     * @return void
     */
    public function testModelHasPrivateResetStackMethod(): void
    {
        $this->assertStringContainsString('private function resetStack()', $this->classContent);
    }

    /**
     * Test model has private resetTimer method
     *
     * @return void
     */
    public function testModelHasPrivateResetTimerMethod(): void
    {
        $this->assertStringContainsString('private function resetTimer()', $this->classContent);
    }

    /**
     * Test model has private saveStack method
     *
     * @return void
     */
    public function testModelHasPrivateSaveStackMethod(): void
    {
        $this->assertStringContainsString('private function saveStack()', $this->classContent);
    }

    /**
     * Test model has private loadStack method
     *
     * @return void
     */
    public function testModelHasPrivateLoadStackMethod(): void
    {
        $this->assertStringContainsString('private function loadStack()', $this->classContent);
    }

    /**
     * Test model has private haveEnoughTime method
     *
     * @return void
     */
    public function testModelHasPrivateHaveEnoughTimeMethod(): void
    {
        $this->assertStringContainsString('private function haveEnoughTime()', $this->classContent);
    }

    /**
     * Test model has private microtimeFloat method
     *
     * @return void
     */
    public function testModelHasPrivateMicrotimeFloatMethod(): void
    {
        $this->assertStringContainsString('private function microtimeFloat()', $this->classContent);
    }

    /**
     * Test model has private getSteps method
     *
     * @return void
     */
    public function testModelHasPrivateGetStepsMethod(): void
    {
        $this->assertStringContainsString('private function getSteps()', $this->classContent);
    }

    /**
     * Test model has private getUpdateVersion method
     *
     * @return void
     */
    public function testModelHasPrivateGetUpdateVersionMethod(): void
    {
        $this->assertStringContainsString('private function getUpdateVersion()', $this->classContent);
    }

    /**
     * Test model has private finish method
     *
     * @return void
     */
    public function testModelHasPrivateFinishMethod(): void
    {
        $this->assertStringContainsString('private function finish(', $this->classContent);
    }

    /**
     * Test model has private postinstallclenup method
     *
     * @return void
     */
    public function testModelHasPrivatePostinstallclenupMethod(): void
    {
        $this->assertStringContainsString('private function postinstallclenup()', $this->classContent);
    }

    /**
     * Test model has private setSchemaVersion method
     *
     * @return void
     */
    public function testModelHasPrivateSetSchemaVersionMethod(): void
    {
        $this->assertStringContainsString('private function setSchemaVersion(', $this->classContent);
    }

    /**
     * Test model has private realRun method
     *
     * @return void
     */
    public function testModelHasPrivateRealRunMethod(): void
    {
        $this->assertStringContainsString('private function realRun()', $this->classContent);
    }

    /**
     * Test model has private allUpdate method
     *
     * @return void
     */
    public function testModelHasPrivateAllUpdateMethod(): void
    {
        $this->assertStringContainsString('private function allUpdate(', $this->classContent);
    }

    /**
     * Test model has private runUpdates method
     *
     * @return void
     */
    public function testModelHasPrivateRunUpdatesMethod(): void
    {
        $this->assertStringContainsString('private function runUpdates(', $this->classContent);
    }

    // =========================================================================
    // Tests for helper calls
    // =========================================================================

    /**
     * Test finish method calls helpers
     *
     * @return void
     */
    public function testFinishMethodCallsHelpers(): void
    {
        $this->assertStringContainsString('CwmmigrationHelper::fixMenus()', $this->classContent);
        $this->assertStringContainsString('CwmmigrationHelper::fixemptyaccess()', $this->classContent);
        $this->assertStringContainsString('CwmmigrationHelper::fixemptylanguage()', $this->classContent);
        $this->assertStringContainsString('CwmmigrationHelper::rmoldurl()', $this->classContent);
        $this->assertStringContainsString('new CwmtemplatemigrationHelper()', $this->classContent);
        $this->assertStringContainsString('new CwmguidedtourHelper()', $this->classContent);
    }

    /**
     * Test realRun method calls fixImport
     *
     * @return void
     */
    public function testRealRunMethodCallsFixImport(): void
    {
        $this->assertStringContainsString('CwmmigrationHelper::fixImport()', $this->classContent);
    }

    /**
     * Test uninstall method calls CwmguidedtourHelper
     *
     * @return void
     */
    public function testUninstallMethodCallsGuidedTourHelper(): void
    {
        $this->assertStringContainsString('new CwmguidedtourHelper()', $this->classContent);
        $this->assertStringContainsString('->removeAllTours()', $this->classContent);
    }

    // =========================================================================
    // Tests for stack compression
    // =========================================================================

    /**
     * Test saveStack uses gzdeflate for compression
     *
     * @return void
     */
    public function testSaveStackUsesGzdeflate(): void
    {
        $this->assertStringContainsString('gzdeflate(', $this->classContent);
    }

    /**
     * Test loadStack uses gzinflate for decompression
     *
     * @return void
     */
    public function testLoadStackUsesGzinflate(): void
    {
        $this->assertStringContainsString('gzinflate(', $this->classContent);
    }

    /**
     * Test stack uses base64 encoding
     *
     * @return void
     */
    public function testStackUsesBase64Encoding(): void
    {
        $this->assertStringContainsString('base64_encode(', $this->classContent);
        $this->assertStringContainsString('base64_decode(', $this->classContent);
    }
}

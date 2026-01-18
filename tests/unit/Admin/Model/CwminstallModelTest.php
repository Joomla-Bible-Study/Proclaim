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
            'use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;',
            $this->classContent
        );
        $this->assertStringContainsString(
            'use CWM\Component\Proclaim\Administrator\Lib\Cwmbackup;',
            $this->classContent
        );
        $this->assertStringContainsString('use Joomla\CMS\Factory;', $this->classContent);
        $this->assertStringContainsString('use Joomla\Database\DatabaseInterface;', $this->classContent);
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
     * Test model has fixMenus method
     *
     * @return void
     */
    public function testModelHasFixMenusMethod(): void
    {
        $this->assertStringContainsString('public function fixMenus()', $this->classContent);
    }

    /**
     * Test model has fixemptyaccess method
     *
     * @return void
     */
    public function testModelHasFixEmptyAccessMethod(): void
    {
        $this->assertStringContainsString('public function fixemptyaccess()', $this->classContent);
    }

    /**
     * Test model has fixemptylanguage method
     *
     * @return void
     */
    public function testModelHasFixEmptyLanguageMethod(): void
    {
        $this->assertStringContainsString('public function fixemptylanguage()', $this->classContent);
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

    /**
     * Test model has postInstallMessages method
     *
     * @return void
     */
    public function testModelHasPostInstallMessagesMethod(): void
    {
        $this->assertStringContainsString('public function postInstallMessages(', $this->classContent);
    }

    /**
     * Test model has rmoldurl method
     *
     * @return void
     */
    public function testModelHasRmOldUrlMethod(): void
    {
        $this->assertStringContainsString('public function rmoldurl()', $this->classContent);
    }

    // =========================================================================
    // Tests for fixImport method logic
    // =========================================================================

    /**
     * Test fixImport method uses correct table name from array
     *
     * The fixImport method should use $table['name'] not $table directly
     *
     * @return void
     */
    public function testFixImportUsesTableNameFromArray(): void
    {
        $this->assertStringContainsString(
            '->from($table[\'name\'])',
            $this->classContent,
            'fixImport should use $table[\'name\'] not $table directly'
        );
    }

    /**
     * Test fixImport initializes $set inside the row loop
     *
     * The $set variable should be reset for each row to avoid unnecessary updates
     *
     * @return void
     */
    public function testFixImportInitializesSetInsideLoop(): void
    {
        // Check that $set = false is inside the foreach data loop
        $pattern = '/foreach\s*\(\$data\s+as\s+\$row\)\s*\{\s*\$set\s*=\s*false;/';
        $this->assertMatchesRegularExpression(
            $pattern,
            $this->classContent,
            '$set should be initialized inside the foreach $data loop'
        );
    }

    /**
     * Test fixImport only updates when value actually changes
     *
     * The method should check if stripslashes actually modifies the value
     *
     * @return void
     */
    public function testFixImportOnlyUpdatesWhenValueChanges(): void
    {
        // Check for the comparison pattern: if ($clean !== $row->params)
        $this->assertStringContainsString(
            'if ($clean !== $row->params)',
            $this->classContent,
            'fixImport should only update params when stripslashes changes the value'
        );

        $this->assertStringContainsString(
            'if ($clean !== $row->metadata)',
            $this->classContent,
            'fixImport should only update metadata when stripslashes changes the value'
        );

        $this->assertStringContainsString(
            'if ($clean !== $row->stylecode)',
            $this->classContent,
            'fixImport should only update stylecode when stripslashes changes the value'
        );
    }

    // =========================================================================
    // Tests for fixMenus method logic
    // =========================================================================

    /**
     * Test fixMenus has correct replacements array
     *
     * @return void
     */
    public function testFixMenusHasCorrectReplacements(): void
    {
        $this->assertStringContainsString("'teacherlist'    => 'cwmteachers'", $this->classContent);
        $this->assertStringContainsString("'teacherdisplay' => 'cwmteacher'", $this->classContent);
        $this->assertStringContainsString("'studydetails'   => 'cwmsermon'", $this->classContent);
        $this->assertStringContainsString("'serieslist'     => 'cwmseriesdisplays'", $this->classContent);
        $this->assertStringContainsString("'seriesdetail'   => 'cwmseriesdisplay'", $this->classContent);
        $this->assertStringContainsString("'studieslist'    => 'cwmsermons'", $this->classContent);
    }

    /**
     * Test fixMenus uses SQL REPLACE function
     *
     * The method should use SQL REPLACE instead of loading all rows into PHP
     *
     * @return void
     */
    public function testFixMenusUsesSqlReplace(): void
    {
        $this->assertStringContainsString(
            'REPLACE(',
            $this->classContent,
            'fixMenus should use SQL REPLACE function'
        );
    }

    /**
     * Test fixMenus filters by com_proclaim links
     *
     * @return void
     */
    public function testFixMenusFiltersByProclaimLinks(): void
    {
        $this->assertStringContainsString(
            "'%com_proclaim%'",
            $this->classContent,
            'fixMenus should filter by com_proclaim links'
        );
    }

    /**
     * Test fixMenus excludes main menutype
     *
     * @return void
     */
    public function testFixMenusExcludesMainMenutype(): void
    {
        $this->assertStringContainsString(
            "' != ' . \$this->_db->q('main')",
            $this->classContent,
            'fixMenus should exclude main menutype'
        );
    }

    // =========================================================================
    // Tests for fixemptyaccess method logic
    // =========================================================================

    /**
     * Test fixemptyaccess has correct tables list
     *
     * @return void
     */
    public function testFixEmptyAccessHasCorrectTables(): void
    {
        $expectedTables = [
            '#__bsms_admin',
            '#__bsms_mediafiles',
            '#__bsms_message_type',
            '#__bsms_podcast',
            '#__bsms_series',
            '#__bsms_servers',
            '#__bsms_studies',
            '#__bsms_studytopics',
            '#__bsms_teachers',
            '#__bsms_templates',
            '#__bsms_topics',
        ];

        foreach ($expectedTables as $table) {
            $this->assertStringContainsString(
                "'" . $table . "'",
                $this->classContent,
                "fixemptyaccess should include table: $table"
            );
        }
    }

    /**
     * Test fixemptyaccess uses proper OR condition
     *
     * The WHERE clause should use proper parenthetical OR syntax
     *
     * @return void
     */
    public function testFixEmptyAccessUsesProperOrCondition(): void
    {
        // Check for proper OR syntax with parentheses
        $this->assertStringContainsString(
            "->where(\n                    '(' .",
            $this->classContent,
            'fixemptyaccess should use parenthetical OR condition'
        );

        $this->assertStringContainsString(
            "' OR '",
            $this->classContent,
            'fixemptyaccess should use OR in the condition'
        );
    }

    /**
     * Test fixemptyaccess checks for both zero and empty string
     *
     * @return void
     */
    public function testFixEmptyAccessChecksBothZeroAndEmptyString(): void
    {
        $this->assertStringContainsString(
            "\$this->_db->q('0')",
            $this->classContent,
            'fixemptyaccess should check for access = 0'
        );

        $this->assertStringContainsString(
            "\$this->_db->q(' ')",
            $this->classContent,
            'fixemptyaccess should check for access = empty string'
        );
    }

    // =========================================================================
    // Tests for fixemptylanguage method logic
    // =========================================================================

    /**
     * Test fixemptylanguage has correct tables list
     *
     * @return void
     */
    public function testFixEmptyLanguageHasCorrectTables(): void
    {
        // The fixemptylanguage method should have these tables
        $expectedTables = [
            '#__bsms_comments',
            '#__bsms_mediafiles',
            '#__bsms_series',
            '#__bsms_studies',
            '#__bsms_teachers',
        ];

        // Count occurrences of each table in the method
        foreach ($expectedTables as $table) {
            $this->assertStringContainsString(
                "'" . $table . "'",
                $this->classContent,
                "fixemptylanguage should include table: $table"
            );
        }
    }

    /**
     * Test fixemptylanguage sets language to asterisk
     *
     * @return void
     */
    public function testFixEmptyLanguageSetsToAsterisk(): void
    {
        $this->assertStringContainsString(
            "->set('language = ' . \$this->_db->q('*'))",
            $this->classContent,
            'fixemptylanguage should set language to *'
        );
    }

    // =========================================================================
    // Tests for rmoldurl method logic
    // =========================================================================

    /**
     * Test rmoldurl returns array with correct old URLs
     *
     * @return void
     */
    public function testRmOldUrlReturnsCorrectUrls(): void
    {
        $expectedUrls = [
            'Proclaim Module',
            'Proclaim Podcast Module',
            'Proclaim Finder Plg',
            'Proclaim Backup Plg',
            'Proclaim Podcast Plg',
            'Proclaim',
        ];

        foreach ($expectedUrls as $url) {
            $this->assertStringContainsString(
                "'" . $url . "'",
                $this->classContent,
                "rmoldurl should include: $url"
            );
        }
    }

    // =========================================================================
    // Tests for private helper methods
    // =========================================================================

    /**
     * Test model has private fixImport method
     *
     * @return void
     */
    public function testModelHasPrivateFixImportMethod(): void
    {
        $this->assertStringContainsString('private function fixImport()', $this->classContent);
    }

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

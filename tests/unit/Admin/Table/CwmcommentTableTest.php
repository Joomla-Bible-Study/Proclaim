<?php

/**
 * Unit tests for CwmcommentTable
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Table;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;

/**
 * Test class for CwmcommentTable
 *
 * @since  10.0.0
 */
class CwmcommentTableTest extends ProclaimTestCase
{
    /**
     * @var string Path to the table class file
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

        $this->classFile    = JPATH_ROOT . '/admin/src/Table/CwmcommentTable.php';
        $this->classContent = file_get_contents($this->classFile);
    }

    /**
     * Test table class file exists
     *
     * @return void
     */
    public function testTableClassFileExists(): void
    {
        $this->assertFileExists($this->classFile);
    }

    /**
     * Test table has correct namespace
     *
     * @return void
     */
    public function testTableHasCorrectNamespace(): void
    {
        $this->assertStringContainsString(
            'namespace CWM\Component\Proclaim\Administrator\Table;',
            $this->classContent
        );
    }

    /**
     * Test table extends Table
     *
     * @return void
     */
    public function testTableExtendsTable(): void
    {
        $this->assertStringContainsString('extends Table', $this->classContent);
        $this->assertStringContainsString('use Joomla\CMS\Table\Table;', $this->classContent);
    }

    /**
     * Test table has store method
     *
     * @return void
     */
    public function testTableHasStoreMethod(): void
    {
        $this->assertStringContainsString('public function store($updateNulls = false): bool', $this->classContent);
    }

    /**
     * Test table has check method
     *
     * @return void
     */
    public function testTableHasCheckMethod(): void
    {
        $this->assertStringContainsString('public function check(): bool', $this->classContent);
    }

    /**
     * Test table has _getAssetName method
     *
     * @return void
     */
    public function testTableHasGetAssetNameMethod(): void
    {
        $this->assertStringContainsString('protected function _getAssetName(): string', $this->classContent);
    }

    /**
     * Test table has _getAssetTitle method
     *
     * @return void
     */
    public function testTableHasGetAssetTitleMethod(): void
    {
        $this->assertStringContainsString('protected function _getAssetTitle(): string', $this->classContent);
    }

    /**
     * Test table has _getAssetParentId method
     *
     * @return void
     */
    public function testTableHasGetAssetParentIdMethod(): void
    {
        $this->assertStringContainsString('protected function _getAssetParentId(?Table $table = null, $id = null): int', $this->classContent);
    }

    /**
     * Test table has required properties
     *
     * @return void
     */
    public function testTableHasRequiredProperties(): void
    {
        $this->assertStringContainsString('public ?int $id = null;', $this->classContent);
        $this->assertStringContainsString('public int $published = 1;', $this->classContent);
        $this->assertStringContainsString('public ?int $study_id = null;', $this->classContent);
        $this->assertStringContainsString('public ?int $user_id = null;', $this->classContent);
        $this->assertStringContainsString('public ?string $full_name = null;', $this->classContent);
        $this->assertStringContainsString('public ?string $user_email = null;', $this->classContent);
        $this->assertStringContainsString('public ?string $comment_date = null;', $this->classContent);
        $this->assertStringContainsString('public ?string $comment_text = null;', $this->classContent);
        $this->assertStringContainsString('public ?string $created = null;', $this->classContent);
        $this->assertStringContainsString('public ?int $created_by = null;', $this->classContent);
        $this->assertStringContainsString('public ?string $modified = null;', $this->classContent);
        $this->assertStringContainsString('public ?int $modified_by = null;', $this->classContent);
    }
}

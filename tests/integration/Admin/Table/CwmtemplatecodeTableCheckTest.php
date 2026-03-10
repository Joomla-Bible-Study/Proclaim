<?php

/**
 * Integration tests for CwmtemplatecodeTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmtemplatecodeTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(CwmtemplatecodeTable::class)]
class CwmtemplatecodeTableCheckTest extends IntegrationTestCase
{
    private CwmtemplatecodeTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmtemplatecodeTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->filename = 'mytemplate';
        $this->table->type     = 1;
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenFilenameNull(): void
    {
        $this->table->filename = null;
        $this->table->type     = 1;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenFilenameEmpty(): void
    {
        $this->table->filename = '';
        $this->table->type     = 1;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenFilenameWhitespace(): void
    {
        $this->table->filename = '   ';
        $this->table->type     = 1;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
    #[DataProvider('restrictedFilenameProvider')]
    public function testCheckThrowsForRestrictedFilename(string $name): void
    {
        $this->table->filename = $name;
        $this->table->type     = 1;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * Restricted filenames that cannot be used for template codes.
     */
    public static function restrictedFilenameProvider(): array
    {
        return [
            'main'       => ['main'],
            'simple'     => ['simple'],
            'custom'     => ['custom'],
            'formheader' => ['formheader'],
            'formfooter' => ['formfooter'],
        ];
    }

    public function testCheckThrowsWhenTypeZero(): void
    {
        $this->table->filename = 'mytemplate';
        $this->table->type     = 0;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTypeEight(): void
    {
        $this->table->filename = 'mytemplate';
        $this->table->type     = 8;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
    #[DataProvider('validTypeProvider')]
    public function testCheckPassesForValidTypes(int $type): void
    {
        $this->table->filename = 'mytemplate';
        $this->table->type     = $type;
        $this->assertTrue($this->table->check());
    }

    public static function validTypeProvider(): array
    {
        return [
            'type 1 (Sermons)'        => [1],
            'type 2 (Sermon)'         => [2],
            'type 3 (Teachers)'       => [3],
            'type 4 (Teacher)'        => [4],
            'type 5 (Seriesdisplays)' => [5],
            'type 6 (Seriesdisplay)'  => [6],
            'type 7 (Module)'         => [7],
        ];
    }
}

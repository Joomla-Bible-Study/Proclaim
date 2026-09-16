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

    /**
     * ⚠️ check() is the only place a user sees this refused. Without it the
     * save reports success and silently writes nothing, because the write path
     * refuses the same name further down.
     */
    #[DataProvider('unsafeFilenameProvider')]
    public function testCheckThrowsForUnsafeFilename(string $name): void
    {
        $this->table->filename = $name;
        $this->table->type     = 1;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * Filenames that could place the layout outside its directory.
     */
    public static function unsafeFilenameProvider(): array
    {
        return [
            'traversal'        => ['../../../configuration'],
            'reachable escape' => ['x/../../../configuration'],
            'backslash'        => ['..\\..\\configuration'],
            'nested path'      => ['sub/evil'],
            'leading slash'    => ['/etc/passwd'],
            'leading dot'      => ['.htaccess'],
            'space in name'    => ['my layout'],
        ];
    }

    /**
     * The other half. A fix that refused everything would pass the test above
     * and make every existing record unsavable.
     */
    #[DataProvider('ordinaryFilenameProvider')]
    public function testCheckPassesForOrdinaryFilename(string $name): void
    {
        $this->table->filename = $name;
        $this->table->type     = 1;
        $this->assertTrue($this->table->check());
    }

    public static function ordinaryFilenameProvider(): array
    {
        return [
            'the seeded name' => ['easy'],
            'underscored'     => ['my_layout'],
            'hyphenated'      => ['my-layout'],
            'mixed case'      => ['MyLayout'],
            'dotted'          => ['layout.v2'],
        ];
    }

    /**
     * The seven names that were creatable before #2109 and would each have
     * overwritten a layout the package ships.
     */
    #[DataProvider('shippedFilenameProvider')]
    public function testCheckThrowsForShippedFilename(int $type, string $name): void
    {
        $this->table->filename = $name;
        $this->table->type     = $type;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public static function shippedFilenameProvider(): array
    {
        return [
            'sermons: simple2'     => [1, 'simple2'],
            'sermon: commentsform' => [2, 'commentsform'],
            'sermon: footer'       => [2, 'footer'],
            'sermon: footerlink'   => [2, 'footerlink'],
            'sermon: header'       => [2, 'header'],
            'teacher: cards'       => [4, 'cards'],
            'teacher: list'        => [4, 'list'],
        ];
    }

    /**
     * ⚠️ Per type. `header` ships for type 2 only, so it must stay usable for a
     * type that ships no such file -- the refusal is not global.
     */
    public function testCheckPassesForShippedNameOfAnotherType(): void
    {
        $this->table->filename = 'header';
        $this->table->type     = 3;
        $this->assertTrue($this->table->check());
    }

    /**
     * A record that already carries the name keeps working. Its code is the
     * only copy at that path, so refusing the save would strand it.
     */
    public function testCheckGrandfathersARecordThatAlreadyHasTheName(): void
    {
        $this->table->id       = 42;
        $this->table->filename = 'footer';
        $this->table->type     = 2;
        $this->table->setDatabase($this->databaseReturning((object) ['filename' => 'footer', 'type' => 2]));

        $this->assertTrue($this->table->check());
    }

    /**
     * ⚠️ The grandfather is on the *stored* name, not on having an id. Renaming
     * some other record onto a shipped name is a new collision and is refused.
     */
    public function testCheckRefusesRenamingAnExistingRecordOntoAShippedName(): void
    {
        $this->table->id       = 42;
        $this->table->filename = 'footer';
        $this->table->type     = 2;
        $this->table->setDatabase($this->databaseReturning((object) ['filename' => 'mylayout', 'type' => 2]));

        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * Same name, different type: still a new collision for that type.
     */
    public function testCheckRefusesMovingAnExistingRecordOntoAShippedNameOfAnotherType(): void
    {
        $this->table->id       = 42;
        $this->table->filename = 'main';
        $this->table->type     = 2;
        $this->table->setDatabase($this->databaseReturning((object) ['filename' => 'main', 'type' => 3]));

        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * ⚠️ A failed read must refuse, not grandfather -- the permissive branch is
     * the one that overwrites a shipped file.
     */
    public function testCheckRefusesWhenTheStoredRowCannotBeRead(): void
    {
        $this->table->id       = 42;
        $this->table->filename = 'footer';
        $this->table->type     = 2;
        $this->table->setDatabase($this->databaseThrowing());

        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * A database whose loadObject() yields this row.
     */
    private function databaseReturning(?object $row): \Joomla\Database\DatabaseInterface
    {
        $db = $this->stubDatabase();
        $db->method('loadObject')->willReturn($row);

        return $db;
    }

    /**
     * A database whose loadObject() fails.
     */
    private function databaseThrowing(): \Joomla\Database\DatabaseInterface
    {
        $db = $this->stubDatabase();
        $db->method('loadObject')->willThrowException(new \RuntimeException('read failed'));

        return $db;
    }

    /**
     * A query chain that survives select/from/where/bind.
     */
    private function stubDatabase(): \Joomla\Database\DatabaseDriver&\PHPUnit\Framework\MockObject\Stub
    {
        $query = $this->createStub(\Joomla\Database\DatabaseQuery::class);
        $query->method('select')->willReturnSelf();
        $query->method('from')->willReturnSelf();
        $query->method('where')->willReturnSelf();
        $query->method('bind')->willReturnSelf();

        $db = $this->createStub(\Joomla\Database\DatabaseDriver::class);
        $db->method('createQuery')->willReturn($query);
        $db->method('quoteName')->willReturnCallback(
            static fn ($name) => \is_array($name)
                ? array_map(static fn ($n) => '`' . $n . '`', $name)
                : '`' . $name . '`'
        );
        $db->method('setQuery')->willReturnSelf();

        return $db;
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

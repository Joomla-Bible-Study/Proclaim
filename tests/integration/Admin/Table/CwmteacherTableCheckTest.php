<?php

/**
 * Integration tests for CwmteacherTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmteacherTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmteacherTable::class)]
class CwmteacherTableCheckTest extends IntegrationTestCase
{
    private CwmteacherTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmteacherTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->teachername = 'Pastor John Smith';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenTeachernameNull(): void
    {
        $this->table->teachername = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTeachernameEmpty(): void
    {
        $this->table->teachername = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTeachernameWhitespace(): void
    {
        $this->table->teachername = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckPassesWithUniqueAlias(): void
    {
        $this->table->teachername = 'Unique Teacher';
        $this->table->alias       = 'unique-teacher-test-xyz';
        $this->assertTrue($this->table->check());
    }

    public function testCheckAutoPrependsHttpsToUrls(): void
    {
        $this->table->teachername = 'Test Teacher';
        $this->table->website     = 'www.example.com';
        $this->table->check();
        $this->assertSame('https://www.example.com', $this->table->website);
    }

    public function testCheckPreservesExistingHttpsUrls(): void
    {
        $this->table->teachername = 'Test Teacher';
        $this->table->website     = 'https://www.example.com';
        $this->table->check();
        $this->assertSame('https://www.example.com', $this->table->website);
    }

    public function testCheckPreservesExistingHttpUrls(): void
    {
        $this->table->teachername = 'Test Teacher';
        $this->table->website     = 'http://www.example.com';
        $this->table->check();
        $this->assertSame('http://www.example.com', $this->table->website);
    }
}

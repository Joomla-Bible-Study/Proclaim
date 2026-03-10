<?php

/**
 * Integration tests for CwmmessageTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmmessageTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmmessageTable::class)]
class CwmmessageTableCheckTest extends IntegrationTestCase
{
    private CwmmessageTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmmessageTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->studytitle = 'John 3:16 — For God So Loved';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenStudytitleNull(): void
    {
        $this->table->studytitle = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenStudytitleEmpty(): void
    {
        $this->table->studytitle = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenStudytitleWhitespace(): void
    {
        $this->table->studytitle = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

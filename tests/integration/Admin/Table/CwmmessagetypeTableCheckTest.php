<?php

/**
 * Integration tests for CwmmessagetypeTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmmessagetypeTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmmessagetypeTable::class)]
class CwmmessagetypeTableCheckTest extends IntegrationTestCase
{
    private CwmmessagetypeTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmmessagetypeTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->message_type = 'Sermon';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenMessageTypeNull(): void
    {
        $this->table->message_type = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenMessageTypeEmpty(): void
    {
        $this->table->message_type = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenMessageTypeWhitespace(): void
    {
        $this->table->message_type = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

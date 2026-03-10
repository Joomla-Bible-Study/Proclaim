<?php

/**
 * Integration tests for CwmlocationTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmlocationTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmlocationTable::class)]
class CwmlocationTableCheckTest extends IntegrationTestCase
{
    private CwmlocationTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmlocationTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->location_text = 'Main Sanctuary';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenLocationTextNull(): void
    {
        $this->table->location_text = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenLocationTextEmpty(): void
    {
        $this->table->location_text = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenLocationTextWhitespace(): void
    {
        $this->table->location_text = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

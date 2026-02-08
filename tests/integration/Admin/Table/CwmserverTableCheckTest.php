<?php

/**
 * Integration tests for CwmserverTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmserverTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmserverTable::class)]
class CwmserverTableCheckTest extends IntegrationTestCase
{
    private CwmserverTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmserverTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->server_name = 'YouTube Channel';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenServerNameNull(): void
    {
        $this->table->server_name = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenServerNameEmpty(): void
    {
        $this->table->server_name = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenServerNameWhitespace(): void
    {
        $this->table->server_name = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

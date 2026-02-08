<?php

/**
 * Integration tests for CwmserieTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmserieTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmserieTable::class)]
class CwmserieTableCheckTest extends IntegrationTestCase
{
    private CwmserieTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmserieTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->series_text = 'Romans Study Series';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenSeriesTextNull(): void
    {
        $this->table->series_text = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenSeriesTextEmpty(): void
    {
        $this->table->series_text = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenSeriesTextWhitespace(): void
    {
        $this->table->series_text = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

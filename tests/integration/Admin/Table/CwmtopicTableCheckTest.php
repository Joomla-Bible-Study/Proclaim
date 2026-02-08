<?php

/**
 * Integration tests for CwmtopicTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmtopicTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmtopicTable::class)]
class CwmtopicTableCheckTest extends IntegrationTestCase
{
    private CwmtopicTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmtopicTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->topic_text = 'Salvation';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenTopicTextNull(): void
    {
        $this->table->topic_text = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTopicTextEmpty(): void
    {
        $this->table->topic_text = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTopicTextWhitespace(): void
    {
        $this->table->topic_text = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

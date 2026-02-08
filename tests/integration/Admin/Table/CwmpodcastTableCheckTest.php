<?php

/**
 * Integration tests for CwmpodcastTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmpodcastTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmpodcastTable::class)]
class CwmpodcastTableCheckTest extends IntegrationTestCase
{
    private CwmpodcastTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmpodcastTable::class);
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->title = 'Sunday Sermons Podcast';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenTitleNull(): void
    {
        $this->table->title = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTitleEmpty(): void
    {
        $this->table->title = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenTitleWhitespace(): void
    {
        $this->table->title = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

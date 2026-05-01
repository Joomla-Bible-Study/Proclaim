<?php

/**
 * Integration tests for CwmcommentTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmcommentTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmcommentTable::class)]
class CwmcommentTableCheckTest extends IntegrationTestCase
{
    private CwmcommentTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmcommentTable::class);
    }

    public function testCheckSetsUserIdFromIdentityWhenNull(): void
    {
        $this->table->user_id = null;
        $this->table->check();

        // Real ConsoleApplication has no authenticated identity in the
        // test harness, so the null branch in CwmcommentTable::check()
        // falls back to Guest (user_id 0). The old stub hardcoded 42,
        // which was testing stub behavior rather than real logic.
        $this->assertSame(0, $this->table->user_id);
    }

    public function testCheckPreservesExistingUserId(): void
    {
        $this->table->user_id = 99;
        $this->table->check();
        $this->assertEquals(99, $this->table->user_id);
    }
}

<?php

/**
 * Integration tests for CwmtemplateTable::delete()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmtemplateTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmtemplateTable::class)]
class CwmtemplateTableDeleteTest extends IntegrationTestCase
{
    private CwmtemplateTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmtemplateTable::class);
    }

    public function testDeleteBlocksDefaultTemplateId1(): void
    {
        $this->table->id = 1;
        $this->expectException(\RuntimeException::class);
        $this->table->delete(1);
    }

    public function testDeleteAllowsNonDefaultTemplate(): void
    {
        $this->table->id = 5;
        // parent::delete() will return true from the stub
        $this->assertTrue($this->table->delete(5));
    }
}

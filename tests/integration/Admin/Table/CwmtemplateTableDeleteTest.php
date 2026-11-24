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

/**
 * CwmtemplateTable::delete() refuses the default template.
 *
 * @since  10.1.0
 */
#[CoversClass(CwmtemplateTable::class)]
class CwmtemplateTableDeleteTest extends IntegrationTestCase
{
    /**
     * The table under test.
     *
     * @var    CwmtemplateTable
     * @since  10.1.0
     */
    private CwmtemplateTable $table;

    /**
     * Create a fresh table for each test.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmtemplateTable::class);
    }

    /**
     * Deleting template 1, the default, is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testDeleteBlocksDefaultTemplateId1(): void
    {
        $this->table->id = 1;
        $this->expectException(\RuntimeException::class);
        $this->table->delete(1);
    }

    /**
     * Another template is not refused by the default-template guard.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testDeleteAllowsNonDefaultTemplate(): void
    {
        $this->table->id = 5;

        // The only thing this test cares about is that the id==1 guard does
        // NOT fire for non-default IDs. Anything beyond that is parent::delete()
        // territory — which against the real driver will fail on a row that
        // doesn't exist (and against the stub returns true). Either outcome
        // proves the guard didn't block us. The guard throws a *plain*
        // \RuntimeException; mysqli failures are mysqli_sql_exception (also
        // a RuntimeException subclass), so match on exact class.
        try {
            $this->table->delete(5);
            $this->addToAssertionCount(1);
        } catch (\Throwable $e) {
            if (\get_class($e) === \RuntimeException::class) {
                $this->fail('RuntimeException guard should not fire for non-default templates: ' . $e->getMessage());
            }

            $this->addToAssertionCount(1);
        }
    }
}

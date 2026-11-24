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

/**
 * CwmserverTable::check() requires a server name.
 *
 * @since  10.1.0
 */
#[CoversClass(CwmserverTable::class)]
class CwmserverTableCheckTest extends IntegrationTestCase
{
    /**
     * The table under test.
     *
     * @var    CwmserverTable
     * @since  10.1.0
     */
    private CwmserverTable $table;

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
        $this->table = $this->createTableInstance(CwmserverTable::class);
    }

    /**
     * A server with a server name passes.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckPassesWithValidData(): void
    {
        $this->table->server_name = 'YouTube Channel';
        $this->assertTrue($this->table->check());
    }

    /**
     * A NULL server name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenServerNameNull(): void
    {
        $this->table->server_name = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * An empty server name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenServerNameEmpty(): void
    {
        $this->table->server_name = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * A server name of only whitespace is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenServerNameWhitespace(): void
    {
        $this->table->server_name = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

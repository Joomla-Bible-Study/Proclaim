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

/**
 * CwmmessagetypeTable::check() requires a message type name.
 *
 * @since  10.1.0
 */
#[CoversClass(CwmmessagetypeTable::class)]
class CwmmessagetypeTableCheckTest extends IntegrationTestCase
{
    /**
     * The table under test.
     *
     * @var    CwmmessagetypeTable
     * @since  10.1.0
     */
    private CwmmessagetypeTable $table;

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
        $this->table = $this->createTableInstance(CwmmessagetypeTable::class);
    }

    /**
     * A message type with a message type name passes.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckPassesWithValidData(): void
    {
        $this->table->message_type = 'Sermon';
        $this->assertTrue($this->table->check());
    }

    /**
     * A NULL message type name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenMessageTypeNull(): void
    {
        $this->table->message_type = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * An empty message type name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenMessageTypeEmpty(): void
    {
        $this->table->message_type = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * A message type name of only whitespace is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenMessageTypeWhitespace(): void
    {
        $this->table->message_type = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

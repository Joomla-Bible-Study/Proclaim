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

/**
 * CwmlocationTable::check() requires a location name.
 *
 * @since  10.1.0
 */
#[CoversClass(CwmlocationTable::class)]
class CwmlocationTableCheckTest extends IntegrationTestCase
{
    /**
     * The table under test.
     *
     * @var    CwmlocationTable
     * @since  10.1.0
     */
    private CwmlocationTable $table;

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
        $this->table = $this->createTableInstance(CwmlocationTable::class);
    }

    /**
     * A location with a location name passes.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckPassesWithValidData(): void
    {
        $this->table->location_text = 'Main Sanctuary';
        $this->assertTrue($this->table->check());
    }

    /**
     * A NULL location name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenLocationTextNull(): void
    {
        $this->table->location_text = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * An empty location name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenLocationTextEmpty(): void
    {
        $this->table->location_text = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * A location name of only whitespace is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenLocationTextWhitespace(): void
    {
        $this->table->location_text = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

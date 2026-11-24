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

/**
 * CwmtopicTable::check() requires a topic name.
 *
 * @since  10.1.0
 */
#[CoversClass(CwmtopicTable::class)]
class CwmtopicTableCheckTest extends IntegrationTestCase
{
    /**
     * The table under test.
     *
     * @var    CwmtopicTable
     * @since  10.1.0
     */
    private CwmtopicTable $table;

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
        $this->table = $this->createTableInstance(CwmtopicTable::class);
    }

    /**
     * A topic with a topic name passes.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckPassesWithValidData(): void
    {
        $this->table->topic_text = 'Salvation';
        $this->assertTrue($this->table->check());
    }

    /**
     * A NULL topic name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenTopicTextNull(): void
    {
        $this->table->topic_text = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * An empty topic name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenTopicTextEmpty(): void
    {
        $this->table->topic_text = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * A topic name of only whitespace is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenTopicTextWhitespace(): void
    {
        $this->table->topic_text = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

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

/**
 * CwmserieTable::check() requires a series title.
 *
 * @since  10.2.0
 */
#[CoversClass(CwmserieTable::class)]
class CwmserieTableCheckTest extends IntegrationTestCase
{
    /**
     * The table under test.
     *
     * @var    CwmserieTable
     * @since  10.2.0
     */
    private CwmserieTable $table;

    /**
     * Create a fresh table for each test.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmserieTable::class);
    }

    /**
     * A series with a series title passes.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public function testCheckPassesWithValidData(): void
    {
        $this->table->series_text = 'Romans Study Series';
        $this->assertTrue($this->table->check());
    }

    /**
     * A NULL series title is refused.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public function testCheckThrowsWhenSeriesTextNull(): void
    {
        $this->table->series_text = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * An empty series title is refused.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public function testCheckThrowsWhenSeriesTextEmpty(): void
    {
        $this->table->series_text = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * A series title of only whitespace is refused.
     *
     * @return  void
     *
     * @since   10.2.0
     */
    public function testCheckThrowsWhenSeriesTextWhitespace(): void
    {
        $this->table->series_text = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }
}

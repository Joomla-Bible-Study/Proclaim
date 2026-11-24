<?php

/**
 * Integration tests for CwmteacherTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmteacherTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * CwmteacherTable::check() requires a name.
 *
 * @since  10.1.0
 */
#[CoversClass(CwmteacherTable::class)]
class CwmteacherTableCheckTest extends IntegrationTestCase
{
    /**
     * The table under test.
     *
     * @var    CwmteacherTable
     * @since  10.1.0
     */
    private CwmteacherTable $table;

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
        $this->table = $this->createTableInstance(CwmteacherTable::class);
    }

    /**
     * A teacher with a name passes.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckPassesWithValidData(): void
    {
        $this->table->teachername = 'Pastor John Smith';
        $this->assertTrue($this->table->check());
    }

    /**
     * A NULL name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenTeachernameNull(): void
    {
        $this->table->teachername = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * An empty name is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenTeachernameEmpty(): void
    {
        $this->table->teachername = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * A name of only whitespace is refused.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckThrowsWhenTeachernameWhitespace(): void
    {
        $this->table->teachername = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * A unique alias passes.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckPassesWithUniqueAlias(): void
    {
        $this->table->teachername = 'Unique Teacher';
        $this->table->alias       = 'unique-teacher-test-xyz';
        $this->assertTrue($this->table->check());
    }

    /**
     * A website without a scheme gains https://.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckAutoPrependsHttpsToUrls(): void
    {
        $this->table->teachername = 'Test Teacher';
        $this->table->website     = 'www.example.com';
        $this->table->check();
        $this->assertSame('https://www.example.com', $this->table->website);
    }

    /**
     * An https:// website is kept as it is.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckPreservesExistingHttpsUrls(): void
    {
        $this->table->teachername = 'Test Teacher';
        $this->table->website     = 'https://www.example.com';
        $this->table->check();
        $this->assertSame('https://www.example.com', $this->table->website);
    }

    /**
     * An http:// website is kept as it is.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function testCheckPreservesExistingHttpUrls(): void
    {
        $this->table->teachername = 'Test Teacher';
        $this->table->website     = 'http://www.example.com';
        $this->table->check();
        $this->assertSame('http://www.example.com', $this->table->website);
    }
}

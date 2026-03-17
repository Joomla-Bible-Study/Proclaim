<?php

/**
 * Unit tests for CwmsermonsModel private filter methods.
 *
 * Tests the $hasParam logic in addParamFilter, addTeacherFilter, and
 * addBookFilter to prevent regression of the empty-string module param bug
 * where [""] was treated as a valid filter, adding = 0 WHERE clauses.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmsermonsModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\QueryInterface;

/**
 * Test class for CwmsermonsModel filter methods
 *
 * @since  10.3.0
 */
class CwmsermonsFilterTest extends ProclaimTestCase
{
    /**
     * Helper: invoke a private method on CwmsermonsModel via reflection.
     *
     * @param   string  $methodName  Method to invoke
     * @param   array   $args        Arguments to pass
     *
     * @return  void
     */
    private function invokeFilter(string $methodName, array $args): void
    {
        $model  = new \ReflectionClass(CwmsermonsModel::class);
        $method = $model->getMethod($methodName);

        // Create a minimal instance without calling the constructor
        $instance = $model->newInstanceWithoutConstructor();

        $method->invokeArgs($instance, $args);
    }

    /** @var array Tracks where() calls on the mock query */
    private array $whereCalls = [];

    /**
     * Create a PHPUnit mock of QueryInterface that tracks where() calls.
     *
     * @return  QueryInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createMockQuery(): QueryInterface
    {
        $this->whereCalls = [];

        $mock = $this->createMock(QueryInterface::class);

        $mock->method('where')->willReturnCallback(function ($condition) use ($mock) {
            $this->whereCalls[] = $condition;

            return $mock;
        });

        $mock->method('whereIn')->willReturnCallback(function ($keyName, $keyValues) use ($mock) {
            $this->whereCalls[] = $keyName . ' IN (' . implode(',', $keyValues) . ')';

            return $mock;
        });

        $mock->method('select')->willReturnSelf();
        $mock->method('from')->willReturnSelf();

        return $mock;
    }

    /**
     * Create a mock database driver with quoteName() and getQuery().
     *
     * @return  object  Mock database object
     */
    private function createMockDb(): object
    {
        return new class {
            public function quoteName($name, $as = null): string|array
            {
                if (\is_array($name)) {
                    return array_map(static fn($n) => '`' . $n . '`', $name);
                }

                return '`' . $name . '`';
            }

            public function getQuery(bool $new = false): object
            {
                // Return a minimal object for subquery building in addTeacherFilter
                return new class {
                    public function select($columns): static
                    {
                        return $this;
                    }

                    public function from($tables, $subQueryAlias = null): static
                    {
                        return $this;
                    }

                    public function where($condition): static
                    {
                        return $this;
                    }

                    public function whereIn(string $keyName, array $keyValues, $dataType = 'int'): static
                    {
                        return $this;
                    }

                    public function __toString(): string
                    {
                        return 'EXISTS (SELECT 1)';
                    }
                };
            }

            public function quote($text, $escape = true): string
            {
                return "'" . $text . "'";
            }

            public function getNullDate(): string
            {
                return '0000-00-00 00:00:00';
            }
        };
    }

    // =========================================================================
    // addParamFilter tests
    // =========================================================================

    /**
     * @testdox addParamFilter: null params adds no WHERE clause
     */
    public function testAddParamFilterNullNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', null, 0]);

        $this->assertEmpty($this->whereCalls, 'null param + no user filter should add no WHERE');
    }

    /**
     * @testdox addParamFilter: ['-1'] sentinel adds no WHERE clause
     */
    public function testAddParamFilterSentinelNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['-1'], 0]);

        $this->assertEmpty($this->whereCalls, '["-1"] sentinel should add no WHERE');
    }

    /**
     * @testdox addParamFilter: [''] empty string adds no WHERE clause (module param bug)
     */
    public function testAddParamFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', [''], 0]);

        $this->assertEmpty($this->whereCalls, '[""] empty string should add no WHERE — this was the module bug');
    }

    /**
     * @testdox addParamFilter: [null] adds no WHERE clause
     */
    public function testAddParamFilterNullElementNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', [null], 0]);

        $this->assertEmpty($this->whereCalls, '[null] should add no WHERE');
    }

    /**
     * @testdox addParamFilter: single valid value adds = clause
     */
    public function testAddParamFilterSingleValue(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5'], 0]);

        $this->assertCount(1, $this->whereCalls, 'Single valid param should add one WHERE');
        $this->assertStringContainsString('= 5', $this->whereCalls[0]);
    }

    /**
     * @testdox addParamFilter: multiple values adds IN clause
     */
    public function testAddParamFilterMultipleValues(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5', '10'], 0]);

        $this->assertCount(1, $this->whereCalls);
        $this->assertStringContainsString('IN (5,10)', $this->whereCalls[0]);
    }

    /**
     * @testdox addParamFilter: user filter only adds = clause
     */
    public function testAddParamFilterUserFilterOnly(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', null, 7]);

        $this->assertCount(1, $this->whereCalls);
        $this->assertStringContainsString('= 7', $this->whereCalls[0]);
    }

    /**
     * @testdox addParamFilter: param + user filter adds both clauses
     */
    public function testAddParamFilterParamAndUserFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5', '10'], 5]);

        $this->assertCount(2, $this->whereCalls, 'Param + user filter should add two WHERE clauses');
    }

    /**
     * @testdox addParamFilter: empty string param + user filter only applies user filter
     */
    public function testAddParamFilterEmptyStringWithUserFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', [''], 5]);

        $this->assertCount(1, $this->whereCalls, 'Empty param + user filter should only apply user filter');
        $this->assertStringContainsString('= 5', $this->whereCalls[0]);
    }

    // =========================================================================
    // addTeacherFilter tests
    // =========================================================================

    /**
     * @testdox addTeacherFilter: null params adds no WHERE clause
     */
    public function testAddTeacherFilterNullNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, null, 0]);

        $this->assertEmpty($this->whereCalls);
    }

    /**
     * @testdox addTeacherFilter: [''] empty string adds no WHERE clause
     */
    public function testAddTeacherFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, [''], 0]);

        $this->assertEmpty($this->whereCalls, '[""] should not create EXISTS subquery');
    }

    /**
     * @testdox addTeacherFilter: valid teacher ID adds EXISTS clause
     */
    public function testAddTeacherFilterValidId(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, ['3'], 0]);

        $this->assertCount(1, $this->whereCalls);
        $this->assertStringContainsString('EXISTS', $this->whereCalls[0]);
    }

    // =========================================================================
    // addBookFilter tests
    // =========================================================================

    /**
     * @testdox addBookFilter: null params adds no WHERE clause
     */
    public function testAddBookFilterNullNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, null, 0]);

        $this->assertEmpty($this->whereCalls);
    }

    /**
     * @testdox addBookFilter: [''] empty string adds no WHERE clause
     */
    public function testAddBookFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, [''], 0]);

        $this->assertEmpty($this->whereCalls, '[""] should not filter by book');
    }

    /**
     * @testdox addBookFilter: ['-1'] sentinel adds no WHERE clause
     */
    public function testAddBookFilterSentinelNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, ['-1'], 0]);

        $this->assertEmpty($this->whereCalls);
    }
}

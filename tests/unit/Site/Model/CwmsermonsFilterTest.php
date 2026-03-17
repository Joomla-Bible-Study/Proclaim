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

    /**
     * Create a mock query object that tracks where() calls.
     *
     * @return  object  Mock with ->where() and ->whereCalls property
     */
    private function createMockQuery(): object
    {
        return new class implements QueryInterface {
            /** @var array Captured where() call arguments */
            public array $whereCalls = [];

            public function where($condition, $glue = 'AND'): static
            {
                $this->whereCalls[] = $condition;

                return $this;
            }

            public function whereIn(string $keyName, array $keyValues, $dataType = 'int'): static
            {
                $this->whereCalls[] = $keyName . ' IN (' . implode(',', $keyValues) . ')';

                return $this;
            }

            public function select($columns): static
            {
                return $this;
            }

            public function from($tables, $subQueryAlias = null): static
            {
                return $this;
            }

            public function __toString(): string
            {
                return '';
            }
        };
    }

    /**
     * Create a mock database object with quoteName().
     *
     * @return  object  Mock with ->quoteName() and ->getQuery()
     */
    private function createMockDb(): object
    {
        $mockQuery = $this->createMockQuery();

        return new class ($mockQuery) {
            private object $query;

            public function __construct(object $query)
            {
                $this->query = $query;
            }

            public function quoteName($name, $as = null): string|array
            {
                if (\is_array($name)) {
                    return array_map(static fn($n) => '`' . $n . '`', $name);
                }

                return '`' . $name . '`';
            }

            public function getQuery(bool $new = false): object
            {
                return $this->query;
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

        $this->assertEmpty($query->whereCalls, 'null param + no user filter should add no WHERE');
    }

    /**
     * @testdox addParamFilter: ['-1'] sentinel adds no WHERE clause
     */
    public function testAddParamFilterSentinelNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['-1'], 0]);

        $this->assertEmpty($query->whereCalls, '["-1"] sentinel should add no WHERE');
    }

    /**
     * @testdox addParamFilter: [''] empty string adds no WHERE clause (module param bug)
     */
    public function testAddParamFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', [''], 0]);

        $this->assertEmpty($query->whereCalls, '[""] empty string should add no WHERE — this was the module bug');
    }

    /**
     * @testdox addParamFilter: [null] adds no WHERE clause
     */
    public function testAddParamFilterNullElementNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', [null], 0]);

        $this->assertEmpty($query->whereCalls, '[null] should add no WHERE');
    }

    /**
     * @testdox addParamFilter: single valid value adds = clause
     */
    public function testAddParamFilterSingleValue(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5'], 0]);

        $this->assertCount(1, $query->whereCalls, 'Single valid param should add one WHERE');
        $this->assertStringContainsString('= 5', $query->whereCalls[0]);
    }

    /**
     * @testdox addParamFilter: multiple values adds IN clause
     */
    public function testAddParamFilterMultipleValues(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5', '10'], 0]);

        $this->assertCount(1, $query->whereCalls);
        $this->assertStringContainsString('IN (5,10)', $query->whereCalls[0]);
    }

    /**
     * @testdox addParamFilter: user filter only adds = clause
     */
    public function testAddParamFilterUserFilterOnly(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', null, 7]);

        $this->assertCount(1, $query->whereCalls);
        $this->assertStringContainsString('= 7', $query->whereCalls[0]);
    }

    /**
     * @testdox addParamFilter: param + user filter adds both clauses
     */
    public function testAddParamFilterParamAndUserFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5', '10'], 5]);

        $this->assertCount(2, $query->whereCalls, 'Param + user filter should add two WHERE clauses');
    }

    /**
     * @testdox addParamFilter: empty string param + user filter only applies user filter
     */
    public function testAddParamFilterEmptyStringWithUserFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', [''], 5]);

        $this->assertCount(1, $query->whereCalls, 'Empty param + user filter should only apply user filter');
        $this->assertStringContainsString('= 5', $query->whereCalls[0]);
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

        $this->assertEmpty($query->whereCalls);
    }

    /**
     * @testdox addTeacherFilter: [''] empty string adds no WHERE clause
     */
    public function testAddTeacherFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, [''], 0]);

        $this->assertEmpty($query->whereCalls, '[""] should not create EXISTS subquery');
    }

    /**
     * @testdox addTeacherFilter: valid teacher ID adds EXISTS clause
     */
    public function testAddTeacherFilterValidId(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, ['3'], 0]);

        $this->assertCount(1, $query->whereCalls);
        $this->assertStringContainsString('EXISTS', $query->whereCalls[0]);
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

        $this->assertEmpty($query->whereCalls);
    }

    /**
     * @testdox addBookFilter: [''] empty string adds no WHERE clause
     */
    public function testAddBookFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, [''], 0]);

        $this->assertEmpty($query->whereCalls, '[""] should not filter by book');
    }

    /**
     * @testdox addBookFilter: ['-1'] sentinel adds no WHERE clause
     */
    public function testAddBookFilterSentinelNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, ['-1'], 0]);

        $this->assertEmpty($query->whereCalls);
    }
}

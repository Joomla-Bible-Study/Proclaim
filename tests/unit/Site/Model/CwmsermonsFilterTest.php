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
use PHPUnit\Framework\Attributes\TestDox;

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
     *
     * @since   10.3.0
     * @throws  \ReflectionException
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
     * The conditions the stub query's where() and whereIn() received.
     *
     * @var    array
     * @since  10.3.0
     */
    private array $whereCalls = [];

    /**
     * Create a PHPUnit stub of QueryInterface that tracks where() calls.
     *
     * @return  QueryInterface&\PHPUnit\Framework\MockObject\Stub
     *
     * @since   10.3.0
     */
    private function createMockQuery(): QueryInterface
    {
        $this->whereCalls = [];

        $mock = $this->createStub(QueryInterface::class);

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
     *
     * @since   10.3.0
     */
    private function createMockDb(): object
    {
        return new class () {
            /**
             * Quote a name, or each name in a list, with backticks.
             *
             * @param   mixed  $name  The name, or a list of names
             * @param   mixed  $as    The alias, ignored here
             *
             * @return  string|array  The quoted name or names
             *
             * @since   10.3.0
             */
            public function quoteName($name, $as = null): string|array
            {
                if (\is_array($name)) {
                    return array_map(static fn ($n) => '`' . $n . '`', $name);
                }

                return '`' . $name . '`';
            }

            // The component builds queries with createQuery() (#1394); this
            // delegates so the double keeps satisfying both spellings.
            /**
             * Return a subquery double, as getQuery() does.
             *
             * @return  object  The subquery double
             *
             * @since   10.5.0
             */
            public function createQuery(): object
            {
                return $this->getQuery();
            }

            /**
             * A subquery double that records the table and conditions it is given, and
             * renders them.
             *
             * @param   bool  $new  Ignored here
             *
             * @return  object  The subquery double
             *
             * @since   10.3.0
             */
            public function getQuery(bool $new = false): object
            {
                // A subquery double for the EXISTS clauses in addTeacherFilter and
                // addBookFilter. It records what it is given and renders it, so a
                // test can assert on the conditions rather than on a placeholder.
                return new class () {
                    /**
                     * The table the subquery selects from.
                     *
                     * @var    string
                     * @since  10.5.8
                     */
                    private string $from = '';

                    /**
                     * The subquery's conditions, in the order given.
                     *
                     * @var    string[]
                     * @since  10.5.8
                     */
                    private array $conditions = [];

                    /**
                     * Accept the columns, which the double does not record.
                     *
                     * @param   mixed  $columns  The columns
                     *
                     * @return  static  The double
                     *
                     * @since   10.3.0
                     */
                    public function select($columns): static
                    {
                        return $this;
                    }

                    /**
                     * Record the table the subquery selects from.
                     *
                     * @param   mixed  $tables         The table, or a list of tables
                     * @param   mixed  $subQueryAlias  Ignored here
                     *
                     * @return  static  The double
                     *
                     * @since   10.3.0
                     */
                    public function from($tables, $subQueryAlias = null): static
                    {
                        $this->from = \is_array($tables) ? implode(', ', $tables) : (string) $tables;

                        return $this;
                    }

                    /**
                     * Record a condition, joining a list of them with AND.
                     *
                     * @param   mixed  $condition  The condition, or a list of conditions
                     *
                     * @return  static  The double
                     *
                     * @since   10.3.0
                     */
                    public function where($condition): static
                    {
                        $this->conditions[] = \is_array($condition)
                            ? implode(' AND ', $condition)
                            : (string) $condition;

                        return $this;
                    }

                    /**
                     * Record an IN condition.
                     *
                     * @param   string  $keyName    The column
                     * @param   array   $keyValues  The values
                     * @param   mixed   $dataType   Ignored here
                     *
                     * @return  static  The double
                     *
                     * @since   10.3.0
                     */
                    public function whereIn(string $keyName, array $keyValues, $dataType = 'int'): static
                    {
                        $this->conditions[] = $keyName . ' IN (' . implode(',', $keyValues) . ')';

                        return $this;
                    }

                    /**
                     * Render the subquery from what it recorded.
                     *
                     * @return  string  The SQL
                     *
                     * @since   10.3.0
                     */
                    public function __toString(): string
                    {
                        return 'SELECT 1 FROM ' . $this->from
                            . ($this->conditions ? ' WHERE ' . implode(' AND ', $this->conditions) : '');
                    }
                };
            }

            /**
             * Wrap text in single quotes, without escaping it.
             *
             * @param   mixed  $text    The text
             * @param   mixed  $escape  Ignored here
             *
             * @return  string  The quoted text
             *
             * @since   10.3.0
             */
            public function quote($text, $escape = true): string
            {
                return "'" . $text . "'";
            }

            /**
             * The null date MySQL uses.
             *
             * @return  string  The null date
             *
             * @since   10.3.0
             */
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
     * addParamFilter() adds no WHERE clause for null params.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addParamFilter: null params adds no WHERE clause')]
    public function testAddParamFilterNullNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', null, 0]);

        $this->assertEmpty($this->whereCalls, 'null param + no user filter should add no WHERE');
    }

    /**
     * addParamFilter() adds no WHERE clause for the ['-1'] sentinel.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox("addParamFilter: ['-1'] sentinel adds no WHERE clause")]
    public function testAddParamFilterSentinelNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['-1'], 0]);

        $this->assertEmpty($this->whereCalls, '["-1"] sentinel should add no WHERE');
    }

    /**
     * addParamFilter() adds no WHERE clause for [''], the shape an unset module param
     * sends.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox("addParamFilter: [''] empty string adds no WHERE clause (module param bug)")]
    public function testAddParamFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', [''], 0]);

        $this->assertEmpty($this->whereCalls, '[""] empty string should add no WHERE — this was the module bug');
    }

    /**
     * addParamFilter() adds no WHERE clause for [null].
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addParamFilter: [null] adds no WHERE clause')]
    public function testAddParamFilterNullElementNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', [null], 0]);

        $this->assertEmpty($this->whereCalls, '[null] should add no WHERE');
    }

    /**
     * addParamFilter() adds an = clause for a single value.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addParamFilter: single valid value adds = clause')]
    public function testAddParamFilterSingleValue(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5'], 0]);

        $this->assertCount(1, $this->whereCalls, 'Single valid param should add one WHERE');
        $this->assertStringContainsString('= 5', $this->whereCalls[0]);
    }

    /**
     * addParamFilter() adds an IN clause for several values.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addParamFilter: multiple values adds IN clause')]
    public function testAddParamFilterMultipleValues(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5', '10'], 0]);

        $this->assertCount(1, $this->whereCalls);
        $this->assertStringContainsString('IN (5,10)', $this->whereCalls[0]);
    }

    /**
     * addParamFilter() adds an = clause for a user filter alone.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addParamFilter: user filter only adds = clause')]
    public function testAddParamFilterUserFilterOnly(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', null, 7]);

        $this->assertCount(1, $this->whereCalls);
        $this->assertStringContainsString('= 7', $this->whereCalls[0]);
    }

    /**
     * addParamFilter() adds one clause for the params and another for the user filter.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addParamFilter: param + user filter adds both clauses')]
    public function testAddParamFilterParamAndUserFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addParamFilter', [$query, $db, 'study.location_id', ['5', '10'], 5]);

        $this->assertCount(2, $this->whereCalls, 'Param + user filter should add two WHERE clauses');
    }

    /**
     * addParamFilter() applies only the user filter when the param is an empty string.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addParamFilter: empty string param + user filter only applies user filter')]
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
     * addTeacherFilter() adds no WHERE clause for null params.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addTeacherFilter: null params adds no WHERE clause')]
    public function testAddTeacherFilterNullNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, null, 0]);

        $this->assertEmpty($this->whereCalls);
    }

    /**
     * addTeacherFilter() adds no WHERE clause for [''].
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox("addTeacherFilter: [''] empty string adds no WHERE clause")]
    public function testAddTeacherFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, [''], 0]);

        $this->assertEmpty($this->whereCalls, '[""] should not create EXISTS subquery');
    }

    /**
     * addTeacherFilter() adds an EXISTS clause for a valid teacher id.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addTeacherFilter: valid teacher ID adds EXISTS clause')]
    public function testAddTeacherFilterValidId(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, ['3'], 0]);

        $this->assertCount(1, $this->whereCalls);
        $this->assertStringContainsString('EXISTS', $this->whereCalls[0]);
    }

    /**
     * addTeacherFilter() adds no WHERE clause for the ['-1'] sentinel.
     *
     * @return  void
     *
     * @since   10.3.3
     * @throws  \ReflectionException
     */
    #[TestDox("addTeacherFilter: ['-1'] sentinel adds no WHERE clause")]
    public function testAddTeacherFilterSentinelNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, ['-1'], 0]);

        $this->assertEmpty($this->whereCalls, '["-1"] sentinel should add no EXISTS subquery');
    }

    /**
     * addTeacherFilter() adds no WHERE clause for [null].
     *
     * @return  void
     *
     * @since   10.3.3
     * @throws  \ReflectionException
     */
    #[TestDox('addTeacherFilter: [null] adds no WHERE clause')]
    public function testAddTeacherFilterNullElementNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, [null], 0]);

        $this->assertEmpty($this->whereCalls, '[null] should add no EXISTS subquery');
    }

    /**
     * addTeacherFilter() adds one EXISTS clause for several teacher ids.
     *
     * @return  void
     *
     * @since   10.3.3
     * @throws  \ReflectionException
     */
    #[TestDox('addTeacherFilter: multiple teacher IDs adds one EXISTS clause')]
    public function testAddTeacherFilterMultipleIds(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, ['3', '7'], 0]);

        $this->assertCount(1, $this->whereCalls, 'Multiple param teachers use a single IN-based EXISTS subquery');
        $this->assertStringContainsString('EXISTS', $this->whereCalls[0]);
    }

    /**
     * addTeacherFilter() adds one EXISTS clause for a user filter alone.
     *
     * @return  void
     *
     * @since   10.3.3
     * @throws  \ReflectionException
     */
    #[TestDox('addTeacherFilter: user filter only adds one EXISTS clause')]
    public function testAddTeacherFilterUserFilterOnly(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, null, 5]);

        $this->assertCount(1, $this->whereCalls);
        $this->assertStringContainsString('EXISTS', $this->whereCalls[0]);
    }

    /**
     * addTeacherFilter() adds two EXISTS clauses for params and a user filter.
     *
     * @return  void
     *
     * @since   10.3.3
     * @throws  \ReflectionException
     */
    #[TestDox('addTeacherFilter: param + user filter adds two EXISTS clauses')]
    public function testAddTeacherFilterParamAndUserFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addTeacherFilter', [$query, $db, ['3'], 5]);

        $this->assertCount(2, $this->whereCalls, 'Param + user teacher should add two EXISTS clauses');
    }

    // =========================================================================
    // addBookFilter tests
    // =========================================================================

    /**
     * addBookFilter() adds no WHERE clause for null params.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox('addBookFilter: null params adds no WHERE clause')]
    public function testAddBookFilterNullNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, null, 0]);

        $this->assertEmpty($this->whereCalls);
    }

    /**
     * addBookFilter() adds no WHERE clause for [''].
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox("addBookFilter: [''] empty string adds no WHERE clause")]
    public function testAddBookFilterEmptyStringNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, [''], 0]);

        $this->assertEmpty($this->whereCalls, '[""] should not filter by book');
    }

    /**
     * addBookFilter() adds no WHERE clause for the ['-1'] sentinel.
     *
     * @return  void
     *
     * @since   10.3.0
     * @throws  \ReflectionException
     */
    #[TestDox("addBookFilter: ['-1'] sentinel adds no WHERE clause")]
    public function testAddBookFilterSentinelNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, ['-1'], 0]);

        $this->assertEmpty($this->whereCalls);
    }

    /**
     * addBookFilter() adds no WHERE clause for [null].
     *
     * @return  void
     *
     * @since   10.3.3
     * @throws  \ReflectionException
     */
    #[TestDox('addBookFilter: [null] adds no WHERE clause')]
    public function testAddBookFilterNullElementNoFilter(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        $this->invokeFilter('addBookFilter', [$query, $db, [null], 0]);

        $this->assertEmpty($this->whereCalls, '[null] should not filter by book');
    }

    /**
     * addBookFilter() adds one EXISTS clause over the junction for several books.
     *
     * @return  void
     *
     * @since   10.3.3
     * @throws  \ReflectionException
     */
    #[TestDox('addBookFilter: multiple values adds one EXISTS clause over the junction')]
    public function testAddBookFilterMultipleValues(): void
    {
        $query = $this->createMockQuery();
        $db    = $this->createMockDb();

        // Multi-value params bypass the chapter-range path
        // (addBookChapterWhere), so no application bootstrap is required.
        $this->invokeFilter('addBookFilter', [$query, $db, ['5', '10'], 0]);

        $this->assertCount(1, $this->whereCalls, 'Multiple book numbers use a single clause');
        $this->assertStringContainsString('EXISTS', $this->whereCalls[0]);
        $this->assertStringContainsString('#__bsms_study_scriptures', $this->whereCalls[0]);
        $this->assertStringContainsString('IN (5,10)', $this->whereCalls[0]);
        $this->assertStringNotContainsString(
            'study.booknumber',
            $this->whereCalls[0],
            'The filter is still reading the flat column instead of the junction.'
        );
    }
}

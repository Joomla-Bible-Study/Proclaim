<?php

/**
 * Base test case for API data integration tests.
 *
 * Provides database access with transaction rollback for clean test isolation.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Api;

use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\DatabaseDriver;

/**
 * Base class for API integration tests that need a real database.
 *
 * Wraps each test in a transaction that is rolled back in tearDown(),
 * so no test data persists after the test completes.
 *
 * @since  10.3.0
 */
abstract class ApiDataTestCase extends ProclaimTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    protected ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = $GLOBALS['__proclaim_test_db'] ?? null;

        if ($this->db === null) {
            $this->markTestSkipped('Database connection not available');
        }

        $this->db->transactionStart();
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost
            }
        }

        parent::tearDown();
    }

    /**
     * Insert a test teacher and return the ID.
     *
     * @param   string  $name       Teacher name
     * @param   int     $published  Published state (default: 1)
     *
     * @return  int  The inserted teacher ID
     */
    protected function insertTeacher(string $name = 'Test Teacher', int $published = 1): int
    {
        $row = (object) [
            'teachername' => $name,
            'alias'       => strtolower(str_replace(' ', '-', $name)),
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'address'     => '',
        ];

        $this->db->insertObject('#__bsms_teachers', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a test series and return the ID.
     *
     * @param   string  $title      Series title
     * @param   int     $published  Published state (default: 1)
     *
     * @return  int  The inserted series ID
     */
    protected function insertSeries(string $title = 'Test Series', int $published = 1): int
    {
        $row = (object) [
            'series_text' => $title,
            'alias'       => strtolower(str_replace(' ', '-', $title)),
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];

        $this->db->insertObject('#__bsms_series', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a test sermon (study) and return the ID.
     *
     * @param   string  $title      Sermon title
     * @param   int     $published  Published state (default: 1)
     * @param   int     $seriesId   Series ID (default: 0)
     * @param   int     $teacherId  Teacher ID (default: 0)
     *
     * @return  int  The inserted sermon ID
     */
    protected function insertSermon(
        string $title = 'Test Sermon',
        int $published = 1,
        int $seriesId = 0,
        int $teacherId = 0,
    ): int {
        $row = (object) [
            'studytitle'  => $title,
            'alias'       => strtolower(str_replace(' ', '-', $title)),
            'studydate'   => '2026-01-15 10:00:00',
            'teacher_id'  => $teacherId,
            'series_id'   => $seriesId,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => $published,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
            'hits'        => 0,
            'checked_out' => 0,
            'asset_id'    => 0,
            'created_by'  => 0,
            'modified_by' => 0,
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Query items directly from a table with optional filters.
     *
     * @param   string  $table    Table name (with #__ prefix)
     * @param   array   $where    WHERE conditions as ['column = value', ...]
     * @param   string  $orderBy  ORDER BY clause
     *
     * @return  array  Array of result objects
     */
    protected function queryItems(string $table, array $where = [], string $orderBy = 'id ASC'): array
    {
        $query = $this->db->getQuery(true)
            ->select('*')
            ->from($this->db->quoteName($table))
            ->order($orderBy);

        foreach ($where as $condition) {
            $query->where($condition);
        }

        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }
}

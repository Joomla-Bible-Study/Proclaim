<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmepisodenumberHelper;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\DatabaseDriver;

/**
 * Real DB round-trip tests for #1505 (episode number auto-fill + duplicate
 * detection). Inserts its own series/message fixture in setUp() and rolls
 * back the transaction in tearDown() — earlier revisions of this test
 * hardcoded ids from a specific dev database (j5_dev) and failed on CI's
 * empty proclaim_test database; see PR #1506 review.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmepisodenumberHelperTest extends ProclaimTestCase
{
    private ?DatabaseDriver $db = null;

    private int $seriesId = 0;

    /** @var int[] Message ids sharing studynumber '1' */
    private array $tiedIds = [];

    private int $uniqueId = 0;

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

        $this->seriesId = $this->insertSeries();
        $this->tiedIds  = [
            $this->insertSermon('Episode 1a', $this->seriesId, '1'),
            $this->insertSermon('Episode 1b', $this->seriesId, '1'),
            $this->insertSermon('Episode 1c', $this->seriesId, '1'),
        ];
        $this->uniqueId = $this->insertSermon('Episode 4', $this->seriesId, '4');
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

    private function insertSeries(string $title = 'Episode Number Test Series'): int
    {
        $row = (object) [
            'series_text' => $title,
            'alias'       => strtolower(str_replace(' ', '-', $title)) . '-' . uniqid(),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];

        $this->db->insertObject('#__bsms_series', $row);

        return (int) $this->db->insertid();
    }

    private function insertSermon(string $title, int $seriesId, string $studynumber): int
    {
        $row = (object) [
            'studytitle'  => $title,
            'alias'       => strtolower(str_replace(' ', '-', $title)) . '-' . uniqid(),
            'studydate'   => '2026-01-15 10:00:00',
            'teacher_id'  => 0,
            'series_id'   => $seriesId,
            'studynumber' => $studynumber,
            'messagetype' => 1,
            'booknumber'  => 101,
            'published'   => 1,
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

    public function testNextNumberIgnoresTiesAndReturnsHighestPlusOne(): void
    {
        $this->assertSame(5, CwmepisodenumberHelper::nextNumber($this->db, $this->seriesId));
    }

    public function testNextNumberHonoursExcludeId(): void
    {
        // Excluding the uniquely-numbered message (4) drops the max back to 1 (the tied group).
        $this->assertSame(2, CwmepisodenumberHelper::nextNumber($this->db, $this->seriesId, $this->uniqueId));
    }

    public function testNextNumberForSeriesWithNoMessagesReturnsOne(): void
    {
        $this->assertSame(1, CwmepisodenumberHelper::nextNumber($this->db, -999));
    }

    public function testFindDuplicateReturnsAConflictingMessage(): void
    {
        $duplicate = CwmepisodenumberHelper::findDuplicate($this->db, $this->seriesId, '1');

        $this->assertNotNull($duplicate);
        $this->assertContains((int) $duplicate->id, $this->tiedIds);
    }

    public function testFindDuplicateStillFindsAnotherAfterExcludingOne(): void
    {
        $duplicate = CwmepisodenumberHelper::findDuplicate($this->db, $this->seriesId, '1', $this->tiedIds[0]);

        $this->assertNotNull($duplicate);
        $this->assertContains((int) $duplicate->id, [$this->tiedIds[1], $this->tiedIds[2]]);
    }

    public function testFindDuplicateReturnsNullWhenNoConflict(): void
    {
        $this->assertNull(CwmepisodenumberHelper::findDuplicate($this->db, $this->seriesId, '99'));
    }

    public function testFindAllDuplicatesIncludesFixtureSeries(): void
    {
        $rows = CwmepisodenumberHelper::findAllDuplicates($this->db);

        $match = array_filter(
            $rows,
            fn ($row) => (int) $row->series_id === $this->seriesId && $row->studynumber === '1'
        );

        $this->assertNotEmpty($match, 'findAllDuplicates() must surface the fixture series/studynumber 1 duplicate group');
    }
}

<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Tests\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmepisodeauditModel;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\Database\DatabaseDriver;

/**
 * Regression test for #1505: the episode-audit report model delegates to
 * CwmepisodenumberHelper::findAllDuplicates() rather than duplicating the
 * query — see CwmepisodenumberHelperTest for the query's own real-DB
 * coverage. Seeds its own fixture and rolls back afterward, independent of
 * whatever data (if any) the target database already has.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmepisodeauditModelTest extends ProclaimTestCase
{
    private ?DatabaseDriver $db = null;

    private int $seriesId = 0;

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

        $seriesRow = (object) [
            'series_text' => 'Episode Audit Model Test Series',
            'alias'       => 'episode-audit-model-test-series-' . uniqid(),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];
        $this->db->insertObject('#__bsms_series', $seriesRow);
        $this->seriesId = (int) $this->db->insertid();

        // Distinct numbers. Since #1579 the database rejects a second message
        // taking the same number in the same series, so a duplicate group can
        // no longer be seeded -- see the note on the test below.
        foreach (['Fixture Episode 1a' => '1', 'Fixture Episode 1b' => '2'] as $title => $number) {
            $row = (object) [
                'studytitle'  => $title,
                'alias'       => strtolower(str_replace(' ', '-', $title)) . '-' . uniqid(),
                'studydate'   => '2026-01-15 10:00:00',
                'teacher_id'  => 0,
                'series_id'   => $this->seriesId,
                'studynumber' => $number,
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
        }
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
     * The audit screen exists to surface duplicate episode numbers created
     * before #1579 added the unique constraint. A synthetic group can no longer
     * be seeded to test it against, because the database rejects the second
     * row -- so what is asserted here is that a series with distinct numbers is
     * not reported, which is the state every constrained database should be in.
     *
     * The positive case is covered structurally instead: the model's query
     * filters (series_id > 0, non-empty studynumber) match the constraint, so
     * anything it would report is by definition legacy data.
     */
    public function testGetDuplicatesDoesNotReportAUniqueSeries(): void
    {
        $model = new CwmepisodeauditModel();
        $rows  = $model->getDuplicates();

        $match = array_filter($rows, fn ($row) => (int) $row->series_id === $this->seriesId);

        $this->assertSame([], $match, 'A series with distinct episode numbers is not a duplicate group');
    }
}

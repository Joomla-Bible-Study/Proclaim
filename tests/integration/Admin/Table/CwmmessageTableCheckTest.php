<?php

/**
 * Integration tests for CwmmessageTable::check()
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Table\CwmmessageTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmmessageTable::class)]
class CwmmessageTableCheckTest extends IntegrationTestCase
{
    private CwmmessageTable $table;

    private ?DatabaseDriver $db = null;

    /** @var int[] Message ids holding a number in the fixture series (see seedDuplicateFixture()) */
    private array $tiedIds = [];

    private int $seriesId = 0;

    private int $uniqueId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmmessageTable::class);

        if (\defined('PROCLAIM_TEST_DB_AVAILABLE') && PROCLAIM_TEST_DB_AVAILABLE) {
            $this->db = $GLOBALS['__proclaim_test_db'] ?? null;
            $this->db?->transactionStart();
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
     * Insert a series plus a message at studynumber '1' and another at '4'
     * — a self-contained
     * fixture, independent of whatever data (if any) the target database
     * already has.
     */
    private function seedDuplicateFixture(): void
    {
        if ($this->db === null) {
            $this->markTestSkipped('Requires a live database connection.');
        }

        $seriesRow = (object) [
            'series_text' => 'CwmmessageTable Check Test Series',
            'alias'       => 'cwmmessagetable-check-test-series-' . uniqid(),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];
        $this->db->insertObject('#__bsms_series', $seriesRow);
        $this->seriesId = (int) $this->db->insertid();

        $insertSermon = function (string $title, string $studynumber): int {
            $row = (object) [
                'studytitle'  => $title,
                'alias'       => strtolower(str_replace(' ', '-', $title)) . '-' . uniqid(),
                'studydate'   => '2026-01-15 10:00:00',
                'teacher_id'  => 0,
                'series_id'   => $this->seriesId,
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
        };

        $this->tiedIds  = [
            // One holder per number. Since #1579 the database rejects a second
            // message taking the same number in the same series, so the tied
            // group this fixture used to build cannot be created. None of the
            // behaviours below need one -- they need a number to be *taken*.
            $insertSermon('Fixture Episode 1', '1'),
        ];
        $this->uniqueId = $insertSermon('Fixture Episode 4', '4');
    }

    public function testCheckPassesWithValidData(): void
    {
        $this->table->studytitle = 'John 3:16 — For God So Loved';
        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenStudytitleNull(): void
    {
        $this->table->studytitle = null;
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenStudytitleEmpty(): void
    {
        $this->table->studytitle = '';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckThrowsWhenStudytitleWhitespace(): void
    {
        $this->table->studytitle = '   ';
        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    /**
     * Regression tests for #1505: reject a newly-introduced duplicate episode
     * number within a series, but only when series_id/studynumber actually
     * changed on this save. Fixture (seedDuplicateFixture()): three messages
     * tied at studynumber '1' and one unique message at studynumber '4',
     * seeded fresh per test and rolled back afterward — not dependent on any
     * pre-existing data in the target database.
     */
    public function testCheckThrowsOnNewMessageWithDuplicateNumber(): void
    {
        $this->seedDuplicateFixture();

        $this->table->studytitle  = 'New message reusing an existing episode number';
        $this->table->series_id   = $this->seriesId;
        $this->table->studynumber = '1';

        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckPassesForUniqueNumberInSeries(): void
    {
        $this->seedDuplicateFixture();

        $this->table->studytitle  = 'New message with a genuinely unique episode number';
        $this->table->series_id   = $this->seriesId;
        $this->table->studynumber = '99';

        $this->assertTrue($this->table->check());
    }

    public function testCheckDoesNotThrowWhenResavingPreExistingDuplicateUnchanged(): void
    {
        $this->seedDuplicateFixture();

        // Re-saving a message with its own series_id/studynumber unchanged
        // (editing an unrelated field) must not be blocked by the duplicate
        // check, which is what the changed/unchanged comparison is for.
        $this->table->id          = $this->tiedIds[0];
        $this->table->studytitle  = 'Fixture Episode 1a (edited)';
        $this->table->series_id   = $this->seriesId;
        $this->table->studynumber = '1';

        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenChangingToANewlyConflictingNumber(): void
    {
        $this->seedDuplicateFixture();

        // The other message currently has studynumber '4'. Changing it to '1'
        // introduces a brand new conflict and must be rejected.
        $this->table->id          = $this->uniqueId;
        $this->table->studytitle  = 'Fixture Episode 4 (edited)';
        $this->table->series_id   = $this->seriesId;
        $this->table->studynumber = '1';

        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckIgnoresStudynumberWhenNoSeriesSelected(): void
    {
        $this->table->studytitle  = 'Standalone message';
        $this->table->series_id   = 0;
        $this->table->studynumber = '1';

        $this->assertTrue($this->table->check());
    }
}

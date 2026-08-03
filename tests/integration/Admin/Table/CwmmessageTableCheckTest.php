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
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CwmmessageTable::class)]
class CwmmessageTableCheckTest extends IntegrationTestCase
{
    private CwmmessageTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        $this->table = $this->createTableInstance(CwmmessageTable::class);
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
     * changed on this save. Fixture: series_id 42 on j5_dev has messages
     * 783/784/785 all sharing studynumber '1' (a known, pre-existing
     * duplicate — see #1505's issue body) and message 786 with studynumber
     * '4' (unique).
     */
    public function testCheckThrowsOnNewMessageWithDuplicateNumber(): void
    {
        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Requires a live database connection.');
        }

        $this->table->studytitle  = 'New message reusing an existing episode number';
        $this->table->series_id   = 42;
        $this->table->studynumber = '1';

        $this->expectException(\UnexpectedValueException::class);
        $this->table->check();
    }

    public function testCheckPassesForUniqueNumberInSeries(): void
    {
        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Requires a live database connection.');
        }

        $this->table->studytitle  = 'New message with a genuinely unique episode number';
        $this->table->series_id   = 42;
        $this->table->studynumber = '99';

        $this->assertTrue($this->table->check());
    }

    public function testCheckDoesNotThrowWhenResavingPreExistingDuplicateUnchanged(): void
    {
        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Requires a live database connection.');
        }

        // Message 783 already conflicts with 784/785 in the live fixture data.
        // Re-saving it with the same series_id/studynumber (e.g. editing an
        // unrelated field) must not be blocked by this new check.
        $this->table->id          = 783;
        $this->table->studytitle  = '"A Bad TRANSaction" Part I';
        $this->table->series_id   = 42;
        $this->table->studynumber = '1';

        $this->assertTrue($this->table->check());
    }

    public function testCheckThrowsWhenChangingToANewlyConflictingNumber(): void
    {
        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Requires a live database connection.');
        }

        // Message 786 currently has a unique studynumber (4). Changing it to
        // 1 introduces a brand new conflict with 783/784/785 and must be
        // rejected.
        $this->table->id          = 786;
        $this->table->studytitle  = '"The Process of TRANSformation" Part IV';
        $this->table->series_id   = 42;
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

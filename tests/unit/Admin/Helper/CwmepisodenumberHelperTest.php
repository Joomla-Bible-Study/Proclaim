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
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Real DB round-trip tests for #1505 (episode number auto-fill + duplicate
 * detection). Fixture: series_id 42 on j5_dev has messages 783/784/785 all
 * sharing studynumber '1' and message 786 with studynumber '4' — a known,
 * pre-existing duplicate group (see #1505's issue body for how it was found).
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmepisodenumberHelperTest extends ProclaimTestCase
{
    private const SERIES_ID = 42;

    private static function db(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    public function testNextNumberIgnoresTiesAndReturnsHighestPlusOne(): void
    {
        $this->assertSame(5, CwmepisodenumberHelper::nextNumber(self::db(), self::SERIES_ID));
    }

    public function testNextNumberHonoursExcludeId(): void
    {
        // Excluding message 786 (studynumber 4) drops the max back to 1 (the tied group).
        $this->assertSame(2, CwmepisodenumberHelper::nextNumber(self::db(), self::SERIES_ID, 786));
    }

    public function testNextNumberForSeriesWithNoMessagesReturnsOne(): void
    {
        $this->assertSame(1, CwmepisodenumberHelper::nextNumber(self::db(), -999));
    }

    public function testFindDuplicateReturnsAConflictingMessage(): void
    {
        $duplicate = CwmepisodenumberHelper::findDuplicate(self::db(), self::SERIES_ID, '1');

        $this->assertNotNull($duplicate);
        $this->assertContains((int) $duplicate->id, [783, 784, 785]);
    }

    public function testFindDuplicateStillFindsAnotherAfterExcludingOne(): void
    {
        $duplicate = CwmepisodenumberHelper::findDuplicate(self::db(), self::SERIES_ID, '1', 783);

        $this->assertNotNull($duplicate);
        $this->assertContains((int) $duplicate->id, [784, 785]);
    }

    public function testFindDuplicateReturnsNullWhenNoConflict(): void
    {
        $this->assertNull(CwmepisodenumberHelper::findDuplicate(self::db(), self::SERIES_ID, '99'));
    }

    public function testFindAllDuplicatesIncludesKnownFixtureSeries(): void
    {
        $rows = CwmepisodenumberHelper::findAllDuplicates(self::db());

        $match = array_filter(
            $rows,
            static fn ($row) => (int) $row->series_id === self::SERIES_ID && $row->studynumber === '1'
        );

        $this->assertNotEmpty($match, 'findAllDuplicates() must surface the known series 42 / studynumber 1 duplicate group');
    }
}

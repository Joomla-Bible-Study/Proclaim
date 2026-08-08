<?php

/**
 * Integration tests for episode-number uniqueness.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1579.
 *
 * nextNumber() read MAX(studynumber)+1 and findDuplicate() checked for a clash,
 * both as plain SELECTs with no lock and no unique index behind them. Two
 * administrators adding a message to the same series with the number left blank
 * both read the same maximum, both passed the duplicate check because neither
 * had stored yet, and both saved -- producing the duplicates the episode audit
 * tooling was built to find after the fact.
 *
 * The constraint deliberately does not bind everywhere. Episode numbers only
 * have to be unique inside a real series, and only once one has been given:
 * series_id 0 and -1 are the seriesless sentinels, and studynumber defaults to
 * '' on 685 of 827 rows in a development database. MySQL treats '' as a value,
 * so a plain unique index would have collided across all of them; mapping both
 * cases to NULL through a generated column leaves them unconstrained.
 *
 * @since  __DEPLOY_VERSION__
 */
class CwmepisodenumberRaceTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    private int $rowsAtStart = 0;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart(true);
        $this->rowsAtStart = $this->countStudies();
    }

    protected function tearDown(): void
    {
        $leaked = null;

        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
                $after = $this->countStudies();

                if ($after !== $this->rowsAtStart) {
                    $leaked = \sprintf(
                        'Test isolation broke: #__bsms_studies went from %d to %d rows.',
                        $this->rowsAtStart,
                        $after
                    );
                }
            } catch (\Throwable) {
                // Connection lost; nothing to roll back.
            }
        }

        parent::tearDown();

        if ($leaked !== null) {
            $this->fail($leaked);
        }
    }

    /**
     * @return  int  Total rows in the studies table.
     */
    private function countStudies(): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()->select('COUNT(*)')->from($this->db->quoteName('#__bsms_studies'))
        )->loadResult();
    }

    /**
     * Is the constraint present on this database?
     *
     * j5-dev carries un-applied migration DDL, so the schema cannot be assumed
     * to match the repository. Skipping with a clear reason beats asserting
     * against a table that has not been migrated.
     *
     * @return  bool
     */
    private function constraintPresent(): bool
    {
        $prefix = $this->db->getPrefix();

        return (int) $this->db->setQuery(
            'SELECT COUNT(*) FROM information_schema.STATISTICS'
            . ' WHERE TABLE_SCHEMA = DATABASE()'
            . ' AND TABLE_NAME = ' . $this->db->quote($prefix . 'bsms_studies')
            . ' AND INDEX_NAME = ' . $this->db->quote('uq_series_studynumber')
        )->loadResult() > 0;
    }

    /**
     * Insert a study directly.
     *
     * @param   int     $seriesId  Series id
     * @param   string  $number    Episode number
     *
     * @return  bool  True if the row was accepted
     */
    private function insert(int $seriesId, string $number): bool
    {
        $row = (object) [
            'published'   => 0,
            'studytitle'  => 'cwm1579 fixture',
            'language'    => '*',
            'series_id'   => $seriesId,
            'studynumber' => $number,
        ];

        try {
            $this->db->insertObject('#__bsms_studies', $row, 'id');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    #[TestDox('Two messages cannot take the same number in the same series')]
    public function testDuplicateInARealSeriesIsRejected(): void
    {
        if (!$this->constraintPresent()) {
            $this->markTestSkipped('uq_series_studynumber not present — run the 10.5.6 migration');
        }

        $series = 987654;

        $this->assertTrue($this->insert($series, '1'), 'The first message takes number 1');
        $this->assertFalse(
            $this->insert($series, '1'),
            'Both saves passed the pre-check and both wrote — see #1579'
        );
        $this->assertTrue($this->insert($series, '2'), 'A different number is unaffected');
    }

    /**
     * The constraint must not bind where episode numbers are meaningless, or a
     * database where most messages are unnumbered could not be written to at
     * all.
     */
    #[TestDox('Unnumbered and seriesless messages are left unconstrained')]
    public function testUnconstrainedCases(): void
    {
        if (!$this->constraintPresent()) {
            $this->markTestSkipped('uq_series_studynumber not present — run the 10.5.6 migration');
        }

        $this->assertTrue($this->insert(987655, ''), 'Unnumbered, real series');
        $this->assertTrue($this->insert(987655, ''), 'A second unnumbered message in the same series');
        $this->assertTrue($this->insert(0, ''), 'Seriesless sentinel 0');
        $this->assertTrue($this->insert(0, ''), 'Another seriesless message');
        $this->assertTrue($this->insert(-1, ''), 'Seriesless sentinel -1');
        $this->assertTrue($this->insert(0, '3'), 'Numbered but seriesless');
        $this->assertTrue($this->insert(0, '3'), 'The same number, still seriesless — no series, no constraint');
    }

    // -------------------------------------------------------------------------
    // Schema and migration shape
    // -------------------------------------------------------------------------

    #[TestDox('The install schema carries the constraint')]
    public function testInstallSchemaDeclaresTheConstraint(): void
    {
        $sql = (string) file_get_contents(JPATH_ROOT . '/admin/sql/install.mysql.utf8.sql');

        $this->assertStringContainsString('uq_series_studynumber', $sql, 'A fresh install must be constrained too');
        $this->assertStringContainsString(
            "IF(`series_id` > 0 AND `studynumber` <> '', `studynumber`, NULL)",
            $sql,
            'The constraint must exempt seriesless and unnumbered messages — see #1579'
        );
    }

    /**
     * ADD UNIQUE fails outright on any site with existing duplicates, and a
     * development database had eight such groups.
     */
    #[TestDox('The migration resolves duplicates before adding the constraint')]
    public function testMigrationResolvesDuplicatesFirst(): void
    {
        foreach (glob(JPATH_ROOT . '/admin/sql/updates/mysql/*.sql') ?: [] as $file) {
            $sql = (string) file_get_contents($file);

            if (!str_contains($sql, 'uq_series_studynumber')) {
                continue;
            }

            $blankAt = strpos($sql, "SET s.`studynumber` = ''");
            $addAt   = strpos($sql, 'ADD UNIQUE KEY `uq_series_studynumber`');

            $this->assertNotFalse($blankAt, 'Expected duplicates to be cleared in ' . basename($file));
            $this->assertNotFalse($addAt, 'Expected the index to be added in ' . basename($file));
            $this->assertLessThan($addAt, $blankAt, 'Duplicates must be cleared before the index is added');

            return;
        }

        $this->fail('No migration adds uq_series_studynumber');
    }

    #[TestDox('A losing race is reported as a message, not a fatal')]
    public function testRaceLoserIsReportedNotFatal(): void
    {
        $source = (string) file_get_contents(JPATH_ROOT . '/admin/src/Table/CwmmessageTable.php');

        $this->assertStringContainsString(
            'isDuplicateEpisodeNumber',
            $source,
            'store() must translate the constraint violation — see #1579'
        );
        $this->assertStringContainsString(
            'uq_series_studynumber',
            $source,
            'Match the index by name so unrelated duplicate-key errors are not mislabelled'
        );
    }
}

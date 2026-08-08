<?php

/**
 * Integration tests validating BibleStructure against real scripture data.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\BibleStructure;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1568.
 *
 * BibleStructure::VERSE_COUNTS held 51 entries for Genesis (which has 50
 * chapters), with chapters 18-31 carrying wrong verse counts -- Genesis 24 was
 * recorded as 13 verses rather than 67. getStructureForJs() feeds that table to
 * the scripture-autocomplete widget, so a valid reference like "Genesis 24:60"
 * was client-side rejected as out of range while the non-existent "Genesis 51"
 * was offered as valid.
 *
 * Rather than pinning the one corrected array, these tests validate the whole
 * table against the KJV text actually stored in #__bsms_bible_verses. That is
 * an independent source of truth: it catches the next corrupted book too, not
 * just the one that was reported. The bug was originally confirmed this way --
 * a full 66-book sweep showed Genesis as the only mismatch, 32 discrepancies
 * in total.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(BibleStructure::class)]
class BibleStructureTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    /**
     * Real verse counts keyed [bookOrdinal][chapter] => max verse.
     *
     * @var array<int, array<int, int>>
     */
    private array $realCounts = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);

        $rows = $this->db->setQuery(
            $this->db->createQuery()
                ->select([
                    $this->db->quoteName('book'),
                    $this->db->quoteName('chapter'),
                    'MAX(' . $this->db->quoteName('verse') . ') AS maxverse',
                ])
                ->from($this->db->quoteName('#__bsms_bible_verses'))
                ->where($this->db->quoteName('translation') . ' = ' . $this->db->quote('kjv'))
                ->group($this->db->quoteName('book') . ', ' . $this->db->quoteName('chapter'))
        )->loadObjectList() ?: [];

        foreach ($rows as $row) {
            $this->realCounts[(int) $row->book][(int) $row->chapter] = (int) $row->maxverse;
        }
    }

    /**
     * Skip only the tests that need installed scripture.
     *
     * CI installs the schema but seeds no verse text, so the three
     * data-comparison tests have nothing to check there. Deliberately NOT done
     * in setUp(): that would skip the structural and Genesis tests too, and
     * those carry the regression pin that has to run everywhere.
     *
     * @return  void
     */
    private function requireScriptureData(): void
    {
        if ($this->realCounts === []) {
            $this->markTestSkipped('No KJV verse data installed — nothing to validate the table against.');
        }
    }

    #[TestDox('Every book\'s chapter count matches the installed KJV text')]
    public function testChapterCountsMatchInstalledScripture(): void
    {
        $this->requireScriptureData();

        $table = BibleStructure::getStructureForJs();

        foreach ($this->realCounts as $bookOrdinal => $chapters) {
            $bookNumber = 100 + $bookOrdinal;

            $this->assertArrayHasKey($bookNumber, $table, "Book number {$bookNumber} is missing from the table");
            $this->assertCount(
                \count($chapters),
                $table[$bookNumber],
                "Book {$bookNumber} declares the wrong number of chapters — an extra entry makes the "
                . 'autocomplete offer a chapter that does not exist (Genesis 51, see #1568)'
            );
        }
    }

    #[TestDox('Every chapter\'s verse count matches the installed KJV text')]
    public function testVerseCountsMatchInstalledScripture(): void
    {
        $this->requireScriptureData();

        $table      = BibleStructure::getStructureForJs();
        $mismatches = [];

        foreach ($this->realCounts as $bookOrdinal => $chapters) {
            $bookNumber = 100 + $bookOrdinal;
            $declared   = $table[$bookNumber] ?? [];

            foreach ($chapters as $chapter => $realMax) {
                $stated = $declared[$chapter - 1] ?? null;

                if ($stated !== $realMax) {
                    $mismatches[] = \sprintf(
                        'book %d ch %d: table=%s real=%d',
                        $bookNumber,
                        $chapter,
                        var_export($stated, true),
                        $realMax
                    );
                }
            }
        }

        $this->assertSame(
            [],
            $mismatches,
            "Verse counts disagree with the installed scripture, so the autocomplete will reject valid\n"
            . "references or accept invalid ones:\n  " . implode("\n  ", \array_slice($mismatches, 0, 20))
        );
    }

    #[TestDox('Genesis specifically: 50 chapters, 1533 verses, 24:67')]
    public function testGenesisIsCorrect(): void
    {
        $genesis = BibleStructure::getStructureForJs()[101] ?? [];

        $this->assertCount(50, $genesis, 'Genesis has 50 chapters — the table held 51 (see #1568)');
        $this->assertSame(1533, array_sum($genesis), 'KJV Genesis totals 1533 verses');
        $this->assertSame(67, $genesis[23], 'Genesis 24 has 67 verses — it was recorded as 13');
        $this->assertSame(31, $genesis[0], 'Genesis 1 has 31 verses');
        $this->assertSame(26, $genesis[49], 'Genesis 50 has 26 verses');
    }

    #[TestDox('The table is structurally sound regardless of installed scripture')]
    public function testTableIsStructurallySound(): void
    {
        foreach (BibleStructure::getStructureForJs() as $bookNumber => $chapters) {
            $this->assertNotEmpty($chapters, "Book {$bookNumber} has no chapters");

            foreach ($chapters as $index => $verseCount) {
                $this->assertIsInt($verseCount, "Book {$bookNumber} chapter " . ($index + 1) . ' is not an integer');
                $this->assertGreaterThan(
                    0,
                    $verseCount,
                    "Book {$bookNumber} chapter " . ($index + 1) . ' claims a non-positive verse count'
                );
            }
        }
    }
}

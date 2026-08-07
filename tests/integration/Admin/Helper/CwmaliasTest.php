<?php

/**
 * Integration tests for Cwmalias::updateAlias().
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmalias;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1565.
 *
 * updateAlias() slugified each empty-alias row's title and wrote it back with
 * no uniqueness check -- not against other rows, nor against other rows in the
 * same batch. Two titles that normalise to the same slug (reused series names,
 * teacher name variants) both received the identical alias.
 *
 * Two distinct consequences, one per schema shape:
 *  - #__bsms_studies/_series/_message_type have no unique index on alias, so
 *    the duplicate persisted. site/src/Service/Router.php resolves by alias
 *    alone under "Remove IDs from URLs" (WHERE alias = :alias + loadResult()),
 *    so both records' URLs resolved to one id and a page silently rendered the
 *    other's content.
 *  - #__bsms_teachers carries UNIQUE KEY `idx_alias`, so the duplicate write
 *    threw instead -- and with no try/catch in the loop, the exception aborted
 *    the entire backfill part-way through.
 *
 * Exercised against the real database because the bug is definitionally about
 * database uniqueness: a mocked driver would happily accept the duplicate.
 * Each test runs inside a transaction that is rolled back.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(Cwmalias::class)]
class CwmaliasTest extends IntegrationTestCase
{
    private ?DatabaseDriver $db = null;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        parent::tearDown();
    }

    /**
     * Insert a series with a deliberately empty alias so the backfill picks it up.
     *
     * @param   string  $title  Series title
     *
     * @return  int  Inserted id
     */
    private function insertSeriesNeedingAlias(string $title): int
    {
        $row = (object) [
            'series_text' => $title,
            'alias'       => '',
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];

        $this->db->insertObject('#__bsms_series', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Insert a teacher with an empty alias. #__bsms_teachers is the table that
     * carries the unique index.
     *
     * @param   string  $name  Teacher name
     *
     * @return  int  Inserted id
     */
    private function insertTeacherNeedingAlias(string $name): int
    {
        $row = (object) [
            'teachername' => $name,
            'alias'       => '',
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'address'     => '',
        ];

        $this->db->insertObject('#__bsms_teachers', $row);

        return (int) $this->db->insertid();
    }

    /**
     * Read one row's alias back.
     *
     * @param   string  $table  Table name
     * @param   int     $id     Row id
     *
     * @return  string
     */
    private function aliasOf(string $table, int $id): string
    {
        return (string) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('alias'))
                ->from($this->db->quoteName($table))
                ->where($this->db->quoteName('id') . ' = ' . $id)
        )->loadResult();
    }

    #[TestDox('Two series whose titles slugify identically get distinct aliases')]
    public function testCollidingTitlesGetDistinctAliases(): void
    {
        // Same slug from different raw titles -- punctuation and case differ.
        $first  = $this->insertSeriesNeedingAlias('Faith & Works');
        $second = $this->insertSeriesNeedingAlias('Faith and Works');
        $third  = $this->insertSeriesNeedingAlias('faith-and-works');

        Cwmalias::updateAlias();

        $aliases = [
            $this->aliasOf('#__bsms_series', $first),
            $this->aliasOf('#__bsms_series', $second),
            $this->aliasOf('#__bsms_series', $third),
        ];

        foreach ($aliases as $alias) {
            $this->assertNotSame('', $alias, 'Every backfilled row must receive an alias');
        }

        $this->assertSame(
            $aliases,
            array_unique($aliases),
            'Colliding titles must not share an alias -- the SEF router resolves by alias alone, '
            . 'so duplicates make one record serve another\'s page (see #1565)'
        );
    }

    #[TestDox('The disambiguating suffix follows the -2, -3 convention')]
    public function testDuplicateAliasesUseNumericSuffixes(): void
    {
        $first  = $this->insertSeriesNeedingAlias('Romans Study');
        $second = $this->insertSeriesNeedingAlias('Romans Study');

        Cwmalias::updateAlias();

        $aliases = [
            $this->aliasOf('#__bsms_series', $first),
            $this->aliasOf('#__bsms_series', $second),
        ];
        sort($aliases);

        $this->assertSame(['romans-study', 'romans-study-2'], $aliases);
    }

    #[TestDox('A generated alias also avoids colliding with a pre-existing row')]
    public function testGeneratedAliasAvoidsAnAlreadyStoredAlias(): void
    {
        // A row that already holds the slug the new row's title would produce.
        $existing = (object) [
            'series_text' => 'Existing Series',
            'alias'       => 'gospel-of-john',
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];
        $this->db->insertObject('#__bsms_series', $existing);

        $new = $this->insertSeriesNeedingAlias('Gospel of John');

        Cwmalias::updateAlias();

        $this->assertSame(
            'gospel-of-john-2',
            $this->aliasOf('#__bsms_series', $new),
            'The backfill must not reuse an alias another row already holds'
        );
    }

    #[TestDox('A teacher whose slug collides with an existing alias does not abort the backfill')]
    public function testCollidingTeacherAliasDoesNotAbortTheBackfill(): void
    {
        // #__bsms_teachers carries UNIQUE KEY `idx_alias`, which also covers the
        // empty string -- so two alias-less teachers cannot coexist and the
        // collision necessarily happens against an already-aliased row.
        $existing = (object) [
            'teachername' => 'Pastor John Smith',
            'alias'       => 'pastor-john-smith',
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'address'     => '',
        ];
        $this->db->insertObject('#__bsms_teachers', $existing);

        // Same name, no alias yet: slugifies onto the row above. Pre-fix this
        // write hit the unique index, threw, and the uncaught exception ended
        // the entire backfill -- including any tables still queued behind it.
        $needsAlias = $this->insertTeacherNeedingAlias('Pastor John Smith');

        $count = Cwmalias::updateAlias();

        $this->assertGreaterThanOrEqual(1, $count, 'The backfill must complete rather than abort');

        $this->assertSame(
            'pastor-john-smith-2',
            $this->aliasOf('#__bsms_teachers', $needsAlias),
            'The colliding row must be disambiguated instead of failing the unique index'
        );
    }

    #[TestDox('A failure on one row does not abandon rows queued behind it')]
    public function testBackfillContinuesPastAnUnwritableRow(): void
    {
        // Teachers are processed after series in getObjects(), so a teacher-side
        // abort pre-fix would still have let series through; order the fixture so
        // the surviving assertion is meaningful either way.
        $seriesId = $this->insertSeriesNeedingAlias('Ordering Check Series');

        $existing = (object) [
            'teachername' => 'Colliding Teacher',
            'alias'       => 'colliding-teacher',
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'address'     => '',
        ];
        $this->db->insertObject('#__bsms_teachers', $existing);
        $teacherId = $this->insertTeacherNeedingAlias('Colliding Teacher');

        Cwmalias::updateAlias();

        $this->assertSame(
            'ordering-check-series',
            $this->aliasOf('#__bsms_series', $seriesId),
            'Every table must be backfilled even when another row would collide'
        );
        $this->assertNotSame(
            '',
            $this->aliasOf('#__bsms_teachers', $teacherId),
            'The colliding row itself must still receive an alias'
        );
    }

    #[TestDox('A title with no transliterable characters still yields a usable alias')]
    public function testUntransliterableTitleStillGetsAnAlias(): void
    {
        // OutputFilter::stringURLSafe() returns '' here. Writing that back
        // would leave the row looking un-aliased forever.
        $id = $this->insertSeriesNeedingAlias('!!! ??? ***');

        Cwmalias::updateAlias();

        $alias = $this->aliasOf('#__bsms_series', $id);

        $this->assertNotSame('', $alias, 'A row must never be left without an alias after a backfill');
        $this->assertStringContainsString((string) $id, $alias, 'The id-based fallback keeps it unique');
    }

    #[TestDox('Rows that already have an alias are left untouched')]
    public function testExistingAliasesAreNotRewritten(): void
    {
        $row = (object) [
            'series_text' => 'Already Aliased',
            'alias'       => 'a-deliberate-custom-alias',
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'ordering'    => 0,
        ];
        $this->db->insertObject('#__bsms_series', $row);
        $id = (int) $this->db->insertid();

        Cwmalias::updateAlias();

        $this->assertSame(
            'a-deliberate-custom-alias',
            $this->aliasOf('#__bsms_series', $id),
            'The backfill only fills empty aliases; a curated one must survive'
        );
    }
}

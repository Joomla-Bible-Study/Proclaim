<?php

/**
 * The WHERE clause behind the image counts.
 *
 * `addUserImageWhereClause()` opened with `extendWhere()`, which rewrites an
 * existing WHERE as a child of a new one — it starts at
 * `$this->where->setName()`, so on a query that has none it is a fatal on
 * null. All three callers build select/from and nothing else.
 *
 * ⚠️ It failed silently. The only caller reachable from the interface sits
 * behind a `catch (\Throwable)` that answers `total: 0`, so thumbnail
 * regeneration reported "nothing to do" on every site instead of reporting an
 * error — indistinguishable from a healthy result.
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Extension\ProclaimComponent;
use CWM\Component\Proclaim\Administrator\Helper\CwmImageMigration;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since 10.6.0
 */
class CwmImageMigrationQueryTest extends ProclaimTestCase
{
    /**
     * The reporting side must skip trashed records; the migration side must not.
     *
     * A trashed record can be restored, and if its legacy image path was never
     * migrated it comes back pointing at a file that has moved — so the tools
     * keep processing it. Only System Health passes the flag, because content
     * already thrown away is not work to report.
     *
     * ⚠️ Asserts both directions against the same fixture. A test that only
     * checked the exclusion would also pass if the query returned nothing at
     * all, which is the failure it is meant to distinguish from success.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Trashed records are excluded on request and included by default')]
    public function testTrashedRecordsAreExcludedOnlyWhenAsked(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $db->transactionStart();

        try {
            $db->setQuery(
                'INSERT INTO ' . $db->quoteName('#__bsms_studies')
                . ' (' . $db->quoteName('studytitle') . ', ' . $db->quoteName('alias') . ', '
                . $db->quoteName('thumbnailm') . ', ' . $db->quoteName('published') . ', '
                . $db->quoteName('language') . ')'
                . ' VALUES (' . $db->quote('Trashed fixture') . ', ' . $db->quote('trashed-fixture') . ', '
                . $db->quote('images/legacy/trashed-fixture.jpg') . ', '
                . ProclaimComponent::CONDITION_TRASHED . ', ' . $db->quote('*') . ')'
            )->execute();

            $id = (int) $db->insertid();

            $included = CwmImageMigration::getRecordsNeedingMigration('studies');
            $excluded = CwmImageMigration::getRecordsNeedingMigration('studies', true);

            $idsIn  = array_map(static fn ($r) => (int) $r->id, $included);
            $idsOut = array_map(static fn ($r) => (int) $r->id, $excluded);

            // Positive control: the fixture must be visible by default, or the
            // exclusion below proves nothing.
            $this->assertContains(
                $id,
                $idsIn,
                'The trashed fixture was not returned by default. The migration tools rely on that, and '
                . 'without it the exclusion assertion is vacuous.'
            );

            $this->assertNotContains(
                $id,
                $idsOut,
                'A trashed record was reported as needing migration. System Health would ask an administrator '
                . 'to fix content they have already thrown away.'
            );
        } finally {
            $db->transactionRollback();
        }
    }

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('The thumbnail regeneration count runs instead of fataling into a zero')]
    public function testThumbRegenerationCountsRuns(): void
    {
        $counts = CwmImageMigration::getThumbRegenerationCounts();

        foreach (['studies', 'teachers', 'series', 'total'] as $key) {
            $this->assertArrayHasKey($key, $counts);
            $this->assertIsInt($counts[$key]);
            $this->assertGreaterThanOrEqual(0, $counts[$key]);
        }

        $this->assertSame(
            $counts['studies'] + $counts['teachers'] + $counts['series'],
            $counts['total'],
            'The total does not add up, so the per-type queries are not all running.'
        );
    }

    /**
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('The WebP and relink counts run on a query with no prior WHERE')]
    public function testSiblingCountsRun(): void
    {
        foreach (['getWebPMigrationCounts', 'getRelinkCounts', 'getRecoveryCounts'] as $method) {
            $counts = CwmImageMigration::$method();

            $this->assertArrayHasKey('total', $counts, $method . '() did not return a total.');
            $this->assertIsInt($counts['total']);
        }
    }

    /**
     * ⚠️ The reason the fix brackets the two conditions into one string rather
     * than passing them as an array with an OR glue. `where()` applies its glue
     * to everything added afterwards, so an array-with-OR would silently OR a
     * later condition into this group instead of ANDing it — the escape this
     * codebase has already been bitten by.
     *
     * @return  void
     *
     * @since   10.6.0
     */
    #[TestDox('A condition added afterwards is ANDed, not swallowed into the OR')]
    public function testLaterConditionsAreNotOredIn(): void
    {
        $db     = Factory::getContainer()->get(DatabaseInterface::class);
        $method = new \ReflectionMethod(CwmImageMigration::class, 'addUserImageWhereClause');

        $query = $db->createQuery()->select('COUNT(*)')->from($db->quoteName('#__bsms_studies'));

        $method->invoke(null, $query, $db, 'image', 'thumbnailm');

        $query->where($db->quoteName('published') . ' = 1');

        $sql = (string) $query;

        $this->assertStringContainsString(' OR ', $sql, 'The two image conditions are no longer ORed together.');
        $this->assertMatchesRegularExpression(
            '/WHERE\s+\(.*\bOR\b.*\)\s+AND\s+.*published/is',
            $sql,
            'A later condition is not ANDed against the bracketed image group: ' . $sql
        );
    }
}

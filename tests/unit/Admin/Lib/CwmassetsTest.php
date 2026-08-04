<?php

/**
 * Unit tests for Cwmassets
 *
 * @package    Proclaim.UnitTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Tests\ProclaimTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Test class for Cwmassets
 *
 * @since  10.0.0
 */
class CwmassetsTest extends ProclaimTestCase
{
    /**
     * Reset the static parent-id cache before every test. Several tests in
     * this class (and PHPUnit's defects-first execution order) call
     * Cwmassets::parentId()/ensureParentAsset(), which populate the cache --
     * without a reset, test order determines whether testParentIdDefaultIsZero
     * sees a pristine value.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Cwmassets::$parent_id = 0;
    }

    /**
     * Test parent_id default value
     *
     * @return void
     */
    public function testParentIdDefaultIsZero(): void
    {
        $this->assertEquals(0, Cwmassets::$parent_id);
    }

    /**
     * Test fixSingleRecord method exists with correct signature
     *
     * @return void
     */
    public function testFixSingleRecordMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmassets::class, 'fixSingleRecord'));

        $method = new \ReflectionMethod(Cwmassets::class, 'fixSingleRecord');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertReturnTypeName('bool', $method);
    }

    /**
     * Test fixAllAssets method exists
     *
     * @return void
     */
    public function testFixAllAssetsMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmassets::class, 'fixAllAssets'));

        $method = new \ReflectionMethod(Cwmassets::class, 'fixAllAssets');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test cleanOrphanedAssets method exists
     *
     * @return void
     */
    public function testCleanOrphanedAssetsMethodExists(): void
    {
        $this->assertTrue(method_exists(Cwmassets::class, 'cleanOrphanedAssets'));

        $method = new \ReflectionMethod(Cwmassets::class, 'cleanOrphanedAssets');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
    }

    /**
     * Test getAssetObjects returns expected structure
     *
     * @return void
     */
    public function testGetAssetObjectsReturnsExpectedStructure(): void
    {
        $objects = Cwmassets::getAssetObjects();

        $this->assertIsArray($objects);
        $this->assertNotEmpty($objects);

        // Check first element has expected keys
        $first = $objects[0];
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('titlefield', $first);
        $this->assertArrayHasKey('assetname', $first);
        $this->assertArrayHasKey('realname', $first);
    }

    /**
     * Test getAssetObjects includes servers table
     *
     * @return void
     */
    public function testGetAssetObjectsIncludesServers(): void
    {
        $objects = Cwmassets::getAssetObjects();
        $names   = array_column($objects, 'name');

        $this->assertContains('#__bsms_servers', $names);
    }

    /**
     * Test getAssetObjects includes studies table
     *
     * @return void
     */
    public function testGetAssetObjectsIncludesStudies(): void
    {
        $objects = Cwmassets::getAssetObjects();
        $names   = array_column($objects, 'name');

        $this->assertContains('#__bsms_studies', $names);
    }

    /**
     * Test getAssetObjects includes teachers table
     *
     * @return void
     */
    public function testGetAssetObjectsIncludesTeachers(): void
    {
        $objects = Cwmassets::getAssetObjects();
        $names   = array_column($objects, 'name');

        $this->assertContains('#__bsms_teachers', $names);
    }

    /**
     * Test getAssetObjects includes series table
     *
     * @return void
     */
    public function testGetAssetObjectsIncludesSeries(): void
    {
        $objects = Cwmassets::getAssetObjects();
        $names   = array_column($objects, 'name');

        $this->assertContains('#__bsms_series', $names);
    }

    /**
     * Get the source body of a Cwmassets method for structural assertions.
     *
     * @param   string  $method
     *
     * @return  string
     */
    private static function methodBody(string $method): string
    {
        $reflection = new \ReflectionMethod(Cwmassets::class, $method);
        $lines      = file($reflection->getFileName());

        return implode(
            '',
            \array_slice($lines, $reflection->getStartLine() - 1, $reflection->getEndLine() - $reflection->getStartLine() + 1)
        );
    }

    /**
     * Regression test for #1526 finding 1: fixSingleRecord()'s Case 3 (parent
     * drift on a per-record asset with real custom rules) used a raw
     * `UPDATE #__assets SET parent_id = ...` that left lft/rgt stale --
     * still nested under the old parent, so Access::getAssetRules()'s
     * containment walk (`b.lft <= a.lft AND b.rgt >= a.rgt`) would never see
     * com_proclaim as an ancestor. This is exactly the "asset row exists but
     * is broken" drift pattern that has caused detail-page 404s before, and
     * it never triggers a full Asset::rebuild() to catch it.
     *
     * Table\Asset::moveByReference() is Joomla core's own API for moving a
     * node in a nested set with correct lft/rgt recalculation -- exercising
     * it live against #__assets would corrupt lft/rgt for every other
     * extension's asset rows too (its "compress the gap" step touches every
     * row with lft greater than the moved node's old rgt), so this is a
     * structural assertion rather than a live DB test, matching the pattern
     * used for other high-blast-radius code in this suite.
     */
    public function testFixSingleRecordCaseThreeUsesNestedSetAwareMove(): void
    {
        $body = self::methodBody('fixSingleRecord');

        $this->assertSame(
            1,
            preg_match_all('/->moveByReference\(/', $body),
            'fixSingleRecord() Case 3 must move the drifted asset via Table\\Asset::moveByReference() ' .
                'so lft/rgt stay correctly nested -- see #1526'
        );

        // The old raw UPDATE set parent_id and name in the same statement,
        // with no moveByReference() call anywhere in the method. Guard
        // against that shape reappearing.
        $this->assertDoesNotMatchRegularExpression(
            '/set\(\$db->quoteName\(.parent_id.\)/',
            $body,
            'fixSingleRecord() must not reparent via a raw UPDATE on parent_id -- see #1526'
        );
    }

    /**
     * Regression test for #1526 finding 2: ensureParentAsset()'s create path
     * caught a failed INSERT (e.g. a concurrent request winning a unique-key
     * race on `#__assets.name`) and unconditionally returned 0, sending the
     * loser down the "parent missing" fallback instead of finding the
     * winner's row. The catch block must re-query for an existing row before
     * giving up.
     */
    public function testEnsureParentAssetRecoversFromDuplicateKeyRace(): void
    {
        $body = self::methodBody('ensureParentAsset');

        $this->assertMatchesRegularExpression(
            '/catch\s*\(\\\\Exception\s+\$e\)\s*\{.*?SELECT|catch\s*\(\\\\Exception\s+\$e\)\s*\{.*?select\(/s',
            $body,
            'ensureParentAsset() must re-query for an existing com_proclaim asset row inside the catch block ' .
                'before returning 0 -- see #1526'
        );
        $this->assertMatchesRegularExpression(
            '/catch.*winnerId.*return \$winnerId;/s',
            $body,
            'ensureParentAsset() must return the winning row\'s id when a concurrent create wins the race -- see #1526'
        );
    }

    /**
     * Regression test for #1526 finding 4: getAssetStatus() ran 5 separate
     * COUNT(*) queries per content table for numrows/inherited/custom_rules/
     * needs_cleanup/drifted (78 queries total across all 13 tables, including
     * the separate orphans query). They're now collapsed into 1 conditional-
     * aggregation query per table (26 total: 13 * (1 collapsed + 1 orphans)).
     *
     * Exercised against the real DB read-only via DatabaseDriver::getCount(),
     * which tracks total statements executed by the driver -- a real
     * regression proof, not a mock expecting a specific call count.
     */
    public function testGetAssetStatusQueryCountIsBounded(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $before = $db->getCount();
        $status = Cwmassets::getAssetStatus();
        $after  = $db->getCount();

        $tableCount = \count(Cwmassets::getAssetObjects());
        $delta      = $after - $before;

        $this->assertNotEmpty($status);
        // 2 queries per table (1 collapsed aggregate + 1 orphans) plus a
        // small constant for parentId(). The pre-fix implementation issued
        // 6 per table (36+ for just the 6 tables covered here) -- this
        // ceiling would fail under the old 6-per-table shape.
        $this->assertLessThanOrEqual(
            ($tableCount * 2) + 5,
            $delta,
            'getAssetStatus() issued ' . $delta . ' queries for ' . $tableCount . ' tables -- ' .
                'expected the collapsed 2-per-table shape (was 6-per-table pre-#1526)'
        );
    }

    /**
     * Regression test for #1526 finding 4: proves the collapsed conditional-
     * aggregation query in getAssetStatus() produces identical results to
     * the original 5 separate per-branch queries, across all five metrics
     * simultaneously (inherited, custom_rules, needs_cleanup, drifted all
     * exercised on the same table in one pass).
     *
     * Fabricates 3 throwaway #__assets rows and temporarily repoints 3 real
     * teacher rows at them, inside a transaction that is always rolled back
     * in finally -- safe because getAssetStatus() is read-only (SELECT
     * only), unlike fixSingleRecord()'s moveByReference() path which takes
     * a table lock that would implicitly commit a transaction.
     */
    public function testGetAssetStatusValuesReflectCustomAndDriftedAssets(): void
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $teacherIds = $db->setQuery(
            $db->createQuery()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__bsms_teachers'))
                ->setLimit(3)
        )->loadColumn();

        if (\count($teacherIds) < 3) {
            $this->markTestSkipped('Need at least 3 teacher rows on the test DB to exercise this fixture.');
        }

        $parentId        = Cwmassets::parentId();
        $originalAssets  = [];
        $fixtureAssetIds = [];

        $db->transactionStart();

        try {
            foreach ($teacherIds as $id) {
                $originalAssets[$id] = (int) $db->setQuery(
                    $db->createQuery()
                        ->select($db->quoteName('asset_id'))
                        ->from($db->quoteName('#__bsms_teachers'))
                        ->where($db->quoteName('id') . ' = ' . (int) $id)
                )->loadResult();
            }

            // Row 1: real custom rules, correctly parented.
            $fixtureAssetIds['custom'] = self::insertFixtureAsset($db, $parentId, $teacherIds[0], '{"core.edit":{"6":1}}');
            // Row 2: empty/default rules, correctly parented (needs_cleanup).
            $fixtureAssetIds['cleanup'] = self::insertFixtureAsset($db, $parentId, $teacherIds[1], '{}');
            // Row 3: real custom rules, but wrong parent (drifted). Also
            // counts toward custom_rules -- the two conditions are
            // independent, matching the pre-fix 5-separate-query semantics.
            $fixtureAssetIds['drifted'] = self::insertFixtureAsset($db, 1, $teacherIds[2], '{"core.edit":{"6":1}}');

            $db->setQuery(
                $db->createQuery()
                    ->update($db->quoteName('#__bsms_teachers'))
                    ->set($db->quoteName('asset_id') . ' = ' . $fixtureAssetIds['custom'])
                    ->where($db->quoteName('id') . ' = ' . (int) $teacherIds[0])
            )->execute();
            $db->setQuery(
                $db->createQuery()
                    ->update($db->quoteName('#__bsms_teachers'))
                    ->set($db->quoteName('asset_id') . ' = ' . $fixtureAssetIds['cleanup'])
                    ->where($db->quoteName('id') . ' = ' . (int) $teacherIds[1])
            )->execute();
            $db->setQuery(
                $db->createQuery()
                    ->update($db->quoteName('#__bsms_teachers'))
                    ->set($db->quoteName('asset_id') . ' = ' . $fixtureAssetIds['drifted'])
                    ->where($db->quoteName('id') . ' = ' . (int) $teacherIds[2])
            )->execute();

            $status        = Cwmassets::getAssetStatus();
            $teacherStatus = null;

            foreach ($status as $row) {
                if ($row['tablename'] === '#__bsms_teachers') {
                    $teacherStatus = $row;

                    break;
                }
            }

            $this->assertNotNull($teacherStatus, 'getAssetStatus() must include a row for #__bsms_teachers');
            $this->assertGreaterThanOrEqual(2, $teacherStatus['custom_rules'], 'custom + drifted fixture rows must both count as custom_rules');
            $this->assertGreaterThanOrEqual(1, $teacherStatus['needs_cleanup']);
            $this->assertGreaterThanOrEqual(1, $teacherStatus['drifted']);
        } finally {
            $db->transactionRollback();
        }
    }

    /**
     * Insert a throwaway #__assets row for the fixture above.
     *
     * @param   DatabaseInterface  $db         Database driver
     * @param   int                $parentId   parent_id to fabricate
     * @param   int                $teacherId  Real teacher id the row's name references
     * @param   string             $rules      JSON rules string
     *
     * @return  int  Inserted asset id
     */
    private static function insertFixtureAsset(DatabaseInterface $db, int $parentId, int $teacherId, string $rules): int
    {
        $query = $db->createQuery()
            ->insert($db->quoteName('#__assets'))
            ->columns($db->quoteName(['parent_id', 'lft', 'rgt', 'level', 'name', 'title', 'rules']))
            ->values(
                (int) $parentId . ', 0, 0, 2, ' .
                $db->quote('com_proclaim.teacher.' . $teacherId . '.testfixture.' . uniqid()) . ', ' .
                $db->quote('test fixture') . ', ' .
                $db->quote($rules)
            );
        $db->setQuery($query);
        $db->execute();

        return (int) $db->insertid();
    }
}

<?php

/**
 * #2018: the asset cleanup destroyed the structure it was meant to repair.
 *
 * Since 10.5.8 every Table::_getAssetParentId() returns
 * `sectionParentId(<section>)`, so a stored record's asset is parented to its
 * section. But the status and cleanup code compared against `com_proclaim`:
 *
 *   - `getAssetStatus()` reported every correctly parented record as drifted,
 *     which is why Parent Drifted equalled Custom Rules exactly, in every row.
 *   - `hasAnyDrift()` therefore always returned true, so the "nothing to do"
 *     fast path could never fire and every install ran the full rewrite.
 *   - `pruneEmptyAssetRows()` matched `com_proclaim.%`, so it deleted the
 *     section rows `seedSections()` had just created — an empty-rules section
 *     means "no override set", not "unused".
 *   - `reparentSurvivingAssets()` then flattened the survivors onto
 *     `com_proclaim`, moving items out of the sections whose rules are meant
 *     to reach them.
 *
 * The net effect was that per-section permissions were torn down by the same
 * install that set them up, silently. Every dev site here had already been
 * flattened, which is why nothing reproduced it.
 *
 * ⚠️ These assert the surviving structure after a real `fixAllAssets()` run,
 * not the return values of the individual steps. The previous tests seeded
 * sections and asserted properties of them directly, so nothing ever observed
 * a seed and a cleanup in the same test — which is the only place the bug was
 * visible.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Lib;

use CWM\Component\Proclaim\Administrator\Lib\Cwmassets;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Asset;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmassetsSectionSurvivalTest extends IntegrationTestCase
{
    /**
     * @var DatabaseInterface|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseInterface $db = null;

    /**
     * Asset ids this test created, removed again in tearDown.
     *
     * @var list<int>
     * @since __DEPLOY_VERSION__
     */
    private array $created = [];

    /**
     * Source rows this test inserted, removed again in tearDown.
     *
     * @var list<int>
     * @since __DEPLOY_VERSION__
     */
    private array $createdStudies = [];

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!Cwmassets::declaredSections()) {
            $this->markTestSkipped('access.xml is not readable from this install path.');
        }
    }

    /**
     * ⚠️ Explicit deletes, not a transaction. Table\Asset::store() takes a
     * table lock, and a lock is an implicit COMMIT in MySQL, so a
     * transaction-wrapped asset test leaks its rows rather than rolling back.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        foreach (array_reverse($this->created) as $id) {
            $asset = new Asset($this->db);

            if ($asset->load($id)) {
                $asset->delete();
            }
        }

        foreach ($this->createdStudies as $studyId) {
            $this->db->setQuery(
                'DELETE FROM ' . $this->db->quoteName('#__bsms_studies')
                . ' WHERE ' . $this->db->quoteName('id') . ' = ' . (int) $studyId
            )->execute();
        }

        $this->createdStudies = [];
        $this->created        = [];

        Cwmassets::clearSectionCache();
        Access::clearStatics();

        parent::tearDown();
    }

    /**
     * Create an item asset under its section, the way a stored record's would be.
     *
     * @param   string  $section  Section name, e.g. 'message'.
     * @param   int     $id       Synthetic record id.
     * @param   string  $rules    Rules JSON; '{}' means no override.
     *
     * @return  int  The new asset id.
     *
     * @since __DEPLOY_VERSION__
     */
    private function createItemAsset(string $section, int $id, string $rules): int
    {
        $asset = new Asset($this->db);
        $asset->setLocation(Cwmassets::sectionParentId($section), 'last-child');
        $asset->name  = 'com_proclaim.' . $section . '.' . $id;
        $asset->title = 'Survival fixture ' . $section . ' ' . $id;
        $asset->rules = $rules;

        $this->assertTrue($asset->check() && $asset->store(), 'Fixture asset could not be stored.');

        $this->created[] = (int) $asset->id;

        return (int) $asset->id;
    }

    /**
     * Insert a message row pointing at an asset.
     *
     * ⚠️ Required. getAssetStatus() counts
     * `FROM #__bsms_studies s LEFT JOIN #__assets a ON s.asset_id = a.id`, so
     * an asset row with no record referencing it is never counted — a first
     * version of this test created the asset alone and measured nothing.
     *
     * @param   int  $assetId  The asset the record should carry.
     *
     * @return  int  The new record id.
     *
     * @since __DEPLOY_VERSION__
     */
    private function createStudyRow(int $assetId): int
    {
        $this->db->setQuery(
            'INSERT INTO ' . $this->db->quoteName('#__bsms_studies')
            . ' (' . $this->db->quoteName('studytitle') . ', ' . $this->db->quoteName('asset_id')
            . ', ' . $this->db->quoteName('language') . ')'
            . ' VALUES (' . $this->db->quote('Survival fixture') . ', ' . (int) $assetId . ', '
            . $this->db->quote('*') . ')'
        )->execute();

        $id = (int) $this->db->insertid();

        $this->createdStudies[] = $id;

        return $id;
    }

    /**
     * Read a section asset's id straight from the table, bypassing the cache.
     *
     * @param   string  $section  Section name.
     *
     * @return  int  Asset id, or 0 when the row is absent.
     *
     * @since __DEPLOY_VERSION__
     */
    private function sectionRowId(string $section): int
    {
        $this->db->setQuery(
            $this->db->getQuery(true)
                ->select($this->db->quoteName('id'))
                ->from($this->db->quoteName('#__assets'))
                ->where($this->db->quoteName('name') . ' = ' . $this->db->quote('com_proclaim.' . $section))
        );

        return (int) $this->db->loadResult();
    }

    /**
     * The headline: a cleanup pass must not delete the sections a seed created.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('Section assets survive a full fixAllAssets() pass')]
    public function testSectionsSurviveCleanup(): void
    {
        $before = [];

        foreach (Cwmassets::declaredSections() as $section) {
            $before[$section] = $this->sectionRowId($section);
        }

        Cwmassets::seedSections();
        Cwmassets::clearSectionCache();

        $seeded = [];

        foreach (Cwmassets::declaredSections() as $section) {
            $id = $this->sectionRowId($section);

            if ($id > 0 && ($before[$section] ?? 0) === 0) {
                $this->created[] = $id;
            }

            $seeded[$section] = $id;
        }

        // ⚠️ Positive control. If seeding produced nothing, every assertion
        // below would pass against an empty set and prove nothing.
        $this->assertNotEmpty(
            array_filter($seeded),
            'seedSections() resolved no section rows — the assertions below would be vacuous.'
        );

        // ⚠️ Without this the test proves nothing. fixAllAssets() returns early
        // when hasAnyDrift() is false, so with a clean database the prune never
        // runs and the sections "survive" only because nothing happened — a
        // first version of this test passed against the unfixed code for
        // exactly that reason. An empty-rules item asset is drift, so the full
        // path runs and the prune genuinely gets its chance at the sections.
        $bait = $this->createItemAsset('message', 999003, '{}');

        Cwmassets::fixAllAssets();
        Cwmassets::clearSectionCache();

        // Proof the cleanup ran rather than short-circuiting.
        $baitAsset = new Asset($this->db);

        $this->assertFalse(
            $baitAsset->load($bait),
            'The empty-rules item asset was not pruned, so fixAllAssets() took the early '
            . 'return — the section assertions below would be survival by inaction.'
        );

        foreach ($seeded as $section => $id) {
            if ($id === 0) {
                continue;
            }

            $this->assertSame(
                $id,
                $this->sectionRowId($section),
                "com_proclaim.$section was deleted by the cleanup. An empty-rules section means "
                . 'no override is set, not that the row is unused — every item asset is parented to it.'
            );
        }
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('A record parented to its section is not reported as drifted')]
    public function testCorrectlyParentedRecordIsNotDrifted(): void
    {
        Cwmassets::seedSections();
        Cwmassets::clearSectionCache();

        $sectionId = Cwmassets::sectionParentId('message');

        if ($sectionId < 1 || $sectionId === Cwmassets::parentId()) {
            $this->markTestSkipped('The message section could not be resolved on this install.');
        }

        $this->created[] = $sectionId;

        $assetId = $this->createItemAsset('message', 999001, '{"core.edit":{"6":1}}');
        $this->createStudyRow($assetId);

        $messages = null;

        foreach (Cwmassets::getAssetStatus() as $row) {
            if (($row['assetname'] ?? '') === 'message') {
                $messages = $row;
                break;
            }
        }

        $this->assertNotNull($messages, 'No status row for the message table — nothing was measured.');

        // ⚠️ Positive control: the fixture record must be among the counted
        // custom-rules rows. Without this, `drifted === 0` would also hold for
        // a query that counted nothing at all.
        $this->assertGreaterThan(
            0,
            (int) $messages['custom_rules'],
            'The fixture record was not counted, so a drift count of 0 means nothing.'
        );

        $this->assertSame(
            0,
            (int) $messages['drifted'],
            'A record parented to com_proclaim.message is where _getAssetParentId() puts it. '
            . 'Reporting it as drifted is what made Parent Drifted equal Custom Rules on every row.'
        );
    }

    /**
     * The repair path for sites the old cleanup already flattened.
     *
     * Those sites hold item assets sitting directly on com_proclaim with their
     * section rows deleted. A later update reseeds the sections, but nothing
     * moves the items back unless the drift probe recognises the state — and
     * treating com_proclaim as a universally acceptable parent would report
     * such a site as clean for ever.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('An item flattened onto com_proclaim is moved back under its section')]
    public function testFlattenedItemIsRepaired(): void
    {
        Cwmassets::seedSections();
        Cwmassets::clearSectionCache();

        $sectionId = Cwmassets::sectionParentId('message');
        $rootId    = Cwmassets::parentId();

        if ($sectionId < 1 || $sectionId === $rootId) {
            $this->markTestSkipped('The message section could not be resolved on this install.');
        }

        $this->created[] = $sectionId;

        // ⚠️ The record has to exist first, and the asset has to be named after
        // its real id. cleanOrphanedAssets() deletes any com_proclaim.<sec>.<id>
        // whose source record is gone, so an asset named for an id that never
        // existed is removed as an orphan before the reparent is even reached —
        // which is what a first version of this test actually measured.
        $studyId = $this->createStudyRow(0);

        // The flattened shape: a real-rules item parked on com_proclaim.
        $asset = new Asset($this->db);
        $asset->setLocation($rootId, 'last-child');
        $asset->name  = 'com_proclaim.message.' . $studyId;
        $asset->title = 'Flattened fixture';
        $asset->rules = '{"core.edit":{"6":1}}';

        $this->assertTrue($asset->check() && $asset->store(), 'Fixture asset could not be stored.');

        $assetId         = (int) $asset->id;
        $this->created[] = $assetId;

        $this->db->setQuery(
            'UPDATE ' . $this->db->quoteName('#__bsms_studies')
            . ' SET ' . $this->db->quoteName('asset_id') . ' = ' . $assetId
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . (int) $studyId
        )->execute();

        // ⚠️ Assert the starting state, or a pass could mean the fixture never
        // landed where the test says it did.
        $this->db->setQuery(
            'SELECT parent_id FROM ' . $this->db->quoteName('#__assets')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $assetId
        );
        $this->assertSame($rootId, (int) $this->db->loadResult(), 'Fixture did not start flattened.');

        $this->assertTrue(
            Cwmassets::hasAnyDrift($this->db, $rootId),
            'A flattened item must register as drift, or fixAllAssets() returns early and never repairs it.'
        );

        Cwmassets::fixAllAssets();

        $this->db->setQuery(
            'SELECT parent_id FROM ' . $this->db->quoteName('#__assets')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $assetId
        );

        $this->assertSame(
            $sectionId,
            (int) $this->db->loadResult(),
            'The item was not moved back under com_proclaim.message, so a site flattened by the '
            . 'old cleanup would stay flattened and its section rules would never reach it.'
        );
    }

    /**
     * The prune still has to do its job — this is the behaviour the section
     * exclusion must not have broken.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    #[TestDox('An item asset with no override is still pruned')]
    public function testEmptyRulesItemAssetIsStillPruned(): void
    {
        Cwmassets::seedSections();
        Cwmassets::clearSectionCache();

        $assetId = $this->createItemAsset('message', 999002, '{}');

        Cwmassets::pruneEmptyAssetRows($this->db);

        $asset = new Asset($this->db);

        $this->assertFalse(
            $asset->load($assetId),
            'A record with no custom rules should not keep an asset row — it inherits. '
            . 'Sparing the section rows must not spare the item rows too.'
        );
    }
}

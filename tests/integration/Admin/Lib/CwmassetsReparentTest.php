<?php

/**
 * Item assets must hang from their section, not the component (#1653).
 *
 * While they were parented to `com_proclaim`, a rule on `com_proclaim.teacher`
 * never reached `com_proclaim.teacher.79` — so the records an administrator had
 * singled out for per-record permissions were the ones that escaped the section
 * rule. Measured on a development site before the change: the section denied
 * core.edit and the item asset still answered ALLOW.
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
class CwmassetsReparentTest extends IntegrationTestCase
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
     * Section rules on the way in, so a deny written here is undone.
     *
     * @var array<int, string>
     * @since __DEPLOY_VERSION__
     */
    private array $savedRules = [];

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!\in_array('teacher', Cwmassets::declaredSections(), true)) {
            $this->markTestSkipped('access.xml is not readable from this install path.');
        }
    }

    /**
     * Remove only what this test created, and restore any rules it wrote.
     *
     * Not a transaction: nested-set writes take table locks, which are an
     * implicit COMMIT in MySQL.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        foreach ($this->savedRules as $id => $rules) {
            $this->db->setQuery(
                $this->db->createQuery()
                    ->update($this->db->quoteName('#__assets'))
                    ->set($this->db->quoteName('rules') . ' = ' . $this->db->quote($rules))
                    ->where($this->db->quoteName('id') . ' = ' . (int) $id)
            )->execute();
        }

        foreach (array_reverse($this->created) as $id) {
            $asset = new Asset($this->db);

            if ($asset->load($id)) {
                $asset->delete($id);
            }
        }

        Cwmassets::clearSectionCache();
        Access::clearStatics();

        parent::tearDown();
    }

    /**
     * Create an asset under a given parent, tracked for cleanup.
     *
     * @param   string  $name      Asset name
     * @param   int     $parentId  Parent asset id
     * @param   string  $rules     JSON rule set
     *
     * @return  int
     * @since __DEPLOY_VERSION__
     */
    private function makeAsset(string $name, int $parentId, string $rules = '{}'): int
    {
        $asset = new Asset($this->db);
        $asset->setLocation($parentId, 'last-child');
        $asset->name  = $name;
        $asset->title = $name;
        $asset->rules = $rules;

        $this->assertTrue($asset->check() && $asset->store(), "Could not create {$name}");
        $this->created[] = (int) $asset->id;

        return (int) $asset->id;
    }

    /**
     * The name of an asset's parent.
     *
     * @param   string  $name  Asset name
     *
     * @return  string
     * @since __DEPLOY_VERSION__
     */
    private function parentNameOf(string $name): string
    {
        return (string) $this->db->setQuery(
            'SELECT p.name FROM ' . $this->db->quoteName('#__assets', 'a')
            . ' INNER JOIN ' . $this->db->quoteName('#__assets', 'p') . ' ON p.id = a.parent_id'
            . ' WHERE a.name = ' . $this->db->quote($name)
        )->loadResult();
    }

    #[TestDox('a section rule reaches an item asset once it is reparented')]
    public function testSectionDenyReachesAReparentedItemAsset(): void
    {
        Cwmassets::seedSections();
        $sectionId = Cwmassets::sectionId('teacher');
        $component = Cwmassets::parentId();

        $this->assertGreaterThan(0, $sectionId);
        $this->assertNotSame($sectionId, $component, 'Section and component must be distinct assets.');

        // The old shape: an item asset on the component, carrying an explicit
        // allow. This is the record that used to escape its section.
        $group = (int) $this->db->setQuery(
            'SELECT id FROM ' . $this->db->quoteName('#__usergroups') . ' ORDER BY id LIMIT 1'
        )->loadResult();

        $itemId = $this->makeAsset(
            'com_proclaim.teacher.999998',
            $component,
            json_encode(['core.edit' => [(string) $group => 1]], \JSON_THROW_ON_ERROR)
        );

        $moved = Cwmassets::reparentItemAssets();
        Access::clearStatics();

        $this->assertGreaterThanOrEqual(1, $moved['moved'], 'Nothing was reparented.');
        $this->assertSame(
            'com_proclaim.teacher',
            $this->parentNameOf('com_proclaim.teacher.999998'),
            'The item asset is still on the component, so section rules cannot reach it.'
        );

        // Deny at the section. Rule::mergeIdentity() merges root-first and an
        // explicit deny survives every later merge, so the item's own allow
        // must not win.
        $this->savedRules[$sectionId] = (string) $this->db->setQuery(
            'SELECT rules FROM ' . $this->db->quoteName('#__assets') . ' WHERE id = ' . $sectionId
        )->loadResult();

        $this->db->setQuery(
            $this->db->createQuery()
                ->update($this->db->quoteName('#__assets'))
                ->set($this->db->quoteName('rules') . ' = ' . $this->db->quote(
                    json_encode(['core.edit' => [(string) $group => 0]], \JSON_THROW_ON_ERROR)
                ))
                ->where($this->db->quoteName('id') . ' = ' . $sectionId)
        )->execute();

        Access::clearStatics();

        $this->assertFalse(
            Access::checkGroup($group, 'core.edit', 'com_proclaim.teacher.999998'),
            'A deny on the section did not reach the item asset. The records an administrator '
            . 'singled out are exactly the ones escaping the section rule.'
        );
        $this->assertFalse(
            Access::checkGroup($group, 'core.edit', 'com_proclaim.teacher'),
            'Baseline: the section itself must deny.'
        );
    }

    #[TestDox('an asset for record 0 is reported rather than reparented')]
    public function testRecordZeroAssetIsSkipped(): void
    {
        Cwmassets::seedSections();
        $component = Cwmassets::parentId();

        $this->makeAsset('com_proclaim.teacher.0', $component);

        $result = Cwmassets::reparentItemAssets();

        $this->assertContains(
            'com_proclaim.teacher.0',
            $result['skipped'],
            'An asset naming record 0 belongs to no record and must be reported, not relocated '
            . 'under a section where it would look legitimate.'
        );
        $this->assertSame(
            'com_proclaim',
            $this->parentNameOf('com_proclaim.teacher.0'),
            'The record-0 asset was moved despite being reported as skipped.'
        );
    }

    #[TestDox('legacy asset names are corrected, and link-row assets removed')]
    public function testLegacyAssetNamesAreCorrected(): void
    {
        Cwmassets::seedSections();
        $component = Cwmassets::parentId();

        $this->makeAsset('com_proclaim.message_type.999996', $component);
        $this->makeAsset('com_proclaim.cwmadmin.999995', $component);
        $this->makeAsset('com_proclaim.studytopics.999994', $component);

        $result = Cwmassets::renameLegacyAssetNames();

        $this->assertGreaterThanOrEqual(2, $result['renamed'], 'The two renamable names were not corrected.');
        $this->assertGreaterThanOrEqual(1, $result['removed'], 'The link-row asset was not removed.');

        foreach (['com_proclaim.messagetype.999996', 'com_proclaim.admin.999995'] as $name) {
            $asset = new Asset($this->db);
            $this->assertTrue($asset->loadByName($name), "{$name} does not exist after the rename.");
        }

        foreach (['com_proclaim.message_type.999996', 'com_proclaim.cwmadmin.999995'] as $name) {
            $asset = new Asset($this->db);
            $this->assertFalse($asset->loadByName($name), "{$name} still exists; the old name was not replaced.");
        }

        $gone = new Asset($this->db);
        $this->assertFalse(
            $gone->loadByName('com_proclaim.studytopics.999994'),
            'A study-topic link row is not permissionable and its asset must be removed, not renamed.'
        );
    }

    #[TestDox('a rename that would collide drops the stale row instead of failing')]
    public function testRenameCollisionDropsTheStaleRow(): void
    {
        Cwmassets::seedSections();
        $component = Cwmassets::parentId();

        // Both names present: the old one is stale, the correct one wins.
        $this->makeAsset('com_proclaim.messagetype.999993', $component);
        $this->makeAsset('com_proclaim.message_type.999993', $component);

        $result = Cwmassets::renameLegacyAssetNames();

        $this->assertContains(
            'com_proclaim.message_type.999993',
            $result['collided'],
            'The collision was not reported. `name` is unique, so an unreported collision is a '
            . 'failed UPDATE that would abort the whole migration.'
        );

        $kept = new Asset($this->db);
        $this->assertTrue($kept->loadByName('com_proclaim.messagetype.999993'), 'The correct row was dropped.');

        $stale = new Asset($this->db);
        $this->assertFalse($stale->loadByName('com_proclaim.message_type.999993'), 'The stale row survived.');
    }

    #[TestDox('reparenting leaves the nested set valid')]
    public function testNestedSetSurvivesReparenting(): void
    {
        Cwmassets::seedSections();
        $component = Cwmassets::parentId();

        $spanBefore = (array) $this->db->setQuery(
            'SELECT lft, rgt FROM ' . $this->db->quoteName('#__assets') . ' WHERE id = ' . $component
        )->loadAssoc();

        $this->makeAsset('com_proclaim.serie.999997', $component);
        Cwmassets::reparentItemAssets();

        $spanAfter = (array) $this->db->setQuery(
            'SELECT lft, rgt FROM ' . $this->db->quoteName('#__assets') . ' WHERE id = ' . $component
        )->loadAssoc();

        $this->assertSame(
            (int) $spanBefore['rgt'] - (int) $spanBefore['lft'] + 2,
            (int) $spanAfter['rgt'] - (int) $spanAfter['lft'],
            'The component span changed by more than the one asset added; a move within a '
            . 'subtree must not alter the parent span.'
        );
        $this->assertSame(
            0,
            (int) $this->db->setQuery('SELECT COUNT(*) FROM ' . $this->db->quoteName('#__assets') . ' WHERE lft >= rgt')->loadResult(),
            'The nested set has rows where lft >= rgt.'
        );
        $this->assertSame(
            0,
            (int) $this->db->setQuery(
                'SELECT COUNT(*) FROM (SELECT lft FROM ' . $this->db->quoteName('#__assets')
                . ' GROUP BY lft HAVING COUNT(*) > 1) t'
            )->loadResult(),
            'The nested set has duplicate lft values.'
        );
    }
}

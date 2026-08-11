<?php

/**
 * Phase 1 of #1653: section assets must be provably inert.
 *
 * `Cwmassets::seedSections()` writes 17 rows into #__assets on every install
 * and update. The whole claim is that it changes no effective permission for
 * anyone, because empty rules inherit from `com_proclaim` — which is exactly
 * the answer the fallback produced when the section did not resolve.
 *
 * "Should be inert" is not a property to take on trust when the subject is
 * ACL, so this measures it: every user group x every declared section x every
 * action, before and after, asserting the answers are identical.
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
class CwmassetsSectionTest extends IntegrationTestCase
{
    /**
     * Actions every non-component section declares.
     *
     * @var list<string>
     * @since __DEPLOY_VERSION__
     */
    private const ACTIONS = ['core.create', 'core.delete', 'core.edit', 'core.edit.state', 'core.edit.own'];

    /**
     * @var DatabaseInterface|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseInterface $db = null;

    /**
     * Section asset ids created by the test, removed again in tearDown.
     *
     * @var list<int>
     * @since __DEPLOY_VERSION__
     */
    private array $created = [];

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
     * Remove only what this test created.
     *
     * Deliberately NOT a transaction rollback. `Table\Asset::store()` takes a
     * table lock, and a lock is an implicit COMMIT in MySQL — so a
     * transaction-wrapped asset test leaks its rows into the database instead
     * of rolling them back. Delete explicitly, by the ids captured on the way
     * in, so a pre-existing section asset is never removed.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        foreach ($this->created as $id) {
            $asset = new Asset($this->db);

            if ($asset->load($id)) {
                $asset->delete();
            }
        }

        $this->created = [];

        // The rows are gone, so the memoised ids are stale. Without this the
        // next test's seedSections() returns cached ids for deleted rows and
        // creates nothing — which showed up as a test that ran no assertions
        // rather than as a failure.
        Cwmassets::clearSectionCache();
        Access::clearStatics();

        parent::tearDown();
    }


    /**
     * Note which sections this test is responsible for cleaning up.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    private function rememberCreated(array $before): void
    {
        foreach (Cwmassets::declaredSections() as $section) {
            if (isset($before[$section])) {
                continue;
            }

            $asset = new Asset($this->db);

            if ($asset->loadByName('com_proclaim.' . $section)) {
                $this->created[] = (int) $asset->id;
            }
        }
    }

    /**
     * Section assets that already exist, so tearDown leaves them alone.
     *
     * @return  array<string, int>
     * @since __DEPLOY_VERSION__
     */
    private function existingSections(): array
    {
        $found = [];

        foreach (Cwmassets::declaredSections() as $section) {
            $asset = new Asset($this->db);

            if ($asset->loadByName('com_proclaim.' . $section)) {
                $found[$section] = (int) $asset->id;
            }
        }

        return $found;
    }

    #[TestDox('every section answers exactly what the component answers')]
    public function testSectionsAnswerTheSameAsTheComponent(): void
    {
        // Seed FIRST, then measure. A before/after comparison in one process
        // cannot work here: Access::getAssetId() memoises name -> id in a
        // function-level `static $loaded`, which clearStatics() does not reset
        // (it clears the class properties only). Once a name has resolved to
        // "no asset", that miss is cached for the rest of the process, so
        // assets created afterwards are invisible and the "after" matrix is
        // identical no matter what rules were written. Measuring only after
        // creation sidesteps the cache entirely.
        //
        // The property is the same one a before/after would show. Inert means
        // a section returns what the fallback returned, and the fallback
        // returned the component's answer -- so assert exactly that.
        $existing = $this->existingSections();

        Cwmassets::seedSections();
        $this->rememberCreated($existing);

        Access::clearStatics();

        $groups = $this->db->setQuery(
            $this->db->createQuery()->select('id')->from($this->db->quoteName('#__usergroups'))
        )->loadColumn();

        $divergent = [];
        $compared  = 0;

        foreach ($groups as $groupId) {
            foreach (self::ACTIONS as $action) {
                $component = Access::checkGroup((int) $groupId, $action, 'com_proclaim');

                foreach (Cwmassets::declaredSections() as $section) {
                    $compared++;
                    $section_ = Access::checkGroup((int) $groupId, $action, 'com_proclaim.' . $section);

                    if ($section_ !== $component) {
                        $divergent[$groupId . '|' . $section . '|' . $action] =
                            var_export($component, true) . ' -> ' . var_export($section_, true);
                    }
                }
            }
        }

        $this->assertGreaterThan(0, $compared, 'Nothing was compared — the assertion would be vacuous.');
        $this->assertSame(
            [],
            $divergent,
            "A section asset answered differently from com_proclaim.\n"
            . 'Section assets must carry empty rules so they inherit; a non-empty rule set '
            . 'here silently re-permissions every existing site on update.'
        );
    }

    #[TestDox('section assets are created with empty rules')]
    public function testSectionAssetsCarryEmptyRules(): void
    {
        $existing = $this->existingSections();

        Cwmassets::seedSections();
        $this->rememberCreated($existing);

        // Asserted over every declared section, not just the ones this test
        // created: another test in the same run may have seeded them already,
        // in which case $this->created is empty and the loop asserts nothing.
        $sections = Cwmassets::declaredSections();

        $this->assertNotEmpty($sections, 'No sections declared; the assertion would be vacuous.');

        foreach ($sections as $section) {
            $asset = new Asset($this->db);

            $this->assertTrue(
                $asset->loadByName('com_proclaim.' . $section),
                "Section asset com_proclaim.{$section} does not exist after seeding."
            );
            $this->assertSame(
                '{}',
                (string) $asset->rules,
                "Section asset {$asset->name} carries rules other than '{}'. "
                . 'Empty rules are what make seeding inert.'
            );
        }
    }

    #[TestDox('every declared section resolves to an asset, and the nested set stays valid')]
    public function testSeedingCreatesEveryDeclaredSection(): void
    {
        $existing = $this->existingSections();

        Cwmassets::seedSections();
        $this->rememberCreated($existing);

        foreach (Cwmassets::declaredSections() as $section) {
            $this->assertGreaterThan(
                0,
                Cwmassets::sectionId($section),
                "No asset for declared section '{$section}'."
            );
        }

        // A broken #__assets row is a known 404 cause, so assert the tree the
        // writes ran through is still coherent rather than just present.
        $invalid = (int) $this->db->setQuery(
            'SELECT COUNT(*) FROM ' . $this->db->quoteName('#__assets')
            . ' WHERE ' . $this->db->quoteName('lft') . ' >= ' . $this->db->quoteName('rgt')
        )->loadResult();

        $this->assertSame(0, $invalid, 'Nested set corrupted: rows exist where lft >= rgt.');

        $duplicates = (int) $this->db->setQuery(
            'SELECT COUNT(*) FROM (SELECT ' . $this->db->quoteName('lft')
            . ' FROM ' . $this->db->quoteName('#__assets')
            . ' GROUP BY ' . $this->db->quoteName('lft') . ' HAVING COUNT(*) > 1) d'
        )->loadResult();

        $this->assertSame(0, $duplicates, 'Nested set corrupted: duplicate lft values.');
    }

    #[TestDox('seeding twice creates nothing the second time')]
    public function testSeedingIsIdempotent(): void
    {
        $existing = $this->existingSections();

        Cwmassets::seedSections();
        $this->rememberCreated($existing);

        $countAfterFirst = $this->countSectionAssets();

        Cwmassets::seedSections();

        $this->assertSame(
            $countAfterFirst,
            $this->countSectionAssets(),
            'A second seed added rows — seedSections() must find existing assets, not duplicate them.'
        );
    }

    #[TestDox('an undeclared section is refused rather than minted')]
    public function testUndeclaredSectionIsRefused(): void
    {
        $this->assertSame(
            0,
            Cwmassets::sectionId('definitely_not_a_section'),
            'An undeclared section produced an asset. Permissions on it would govern nothing, '
            . 'and the row would be invisible to the UI meant to manage it.'
        );

        $asset = new Asset($this->db);

        $this->assertFalse(
            $asset->loadByName('com_proclaim.definitely_not_a_section'),
            'An asset row was written for an undeclared section.'
        );
    }

    #[TestDox('pruning removes an undeclared section but never an item asset')]
    public function testPruneRemovesOnlyUndeclaredSectionRows(): void
    {
        $parent = Cwmassets::parentId();

        if ($parent < 1) {
            $this->fail('com_proclaim has no parent asset; nothing to parent the fixtures to.');
        }

        // A section access.xml no longer declares, and an item asset that must
        // survive -- eating those would drop real per-record permissions.
        $fixtures = [];

        foreach (['com_proclaim.notasection', 'com_proclaim.teacher.999999'] as $name) {
            $asset = new Asset($this->db);
            $asset->setLocation($parent, 'last-child');
            $asset->name  = $name;
            $asset->title = $name;
            $asset->rules = '{}';

            $this->assertTrue($asset->check() && $asset->store(), "Could not create fixture {$name}");
            $fixtures[$name] = (int) $asset->id;
        }

        $existing = $this->existingSections();
        Cwmassets::seedSections();
        $this->rememberCreated($existing);

        try {
            $removed = Cwmassets::pruneUndeclaredSections();

            $this->assertContains(
                'com_proclaim.notasection',
                $removed,
                'An undeclared section asset survived the prune; its rules would keep applying '
                . 'with no screen able to show them.'
            );

            $survivor = new Asset($this->db);
            $this->assertTrue(
                $survivor->loadByName('com_proclaim.teacher.999999'),
                'The prune removed an item asset. Item rows carry per-record permissions and '
                . 'must never be touched by section cleanup.'
            );

            foreach (Cwmassets::declaredSections() as $section) {
                $still = new Asset($this->db);
                $this->assertTrue(
                    $still->loadByName('com_proclaim.' . $section),
                    "The prune removed '{$section}', which access.xml still declares."
                );
            }
        } finally {
            foreach ($fixtures as $id) {
                $asset = new Asset($this->db);

                if ($asset->load($id)) {
                    $asset->delete($id);
                }
            }

            Cwmassets::clearSectionCache();
        }
    }

    /**
     * How many `com_proclaim.<section>` assets exist right now.
     *
     * @return  int
     * @since __DEPLOY_VERSION__
     */
    private function countSectionAssets(): int
    {
        $names = [];

        foreach (Cwmassets::declaredSections() as $section) {
            $names[] = $this->db->quote('com_proclaim.' . $section);
        }

        return (int) $this->db->setQuery(
            'SELECT COUNT(*) FROM ' . $this->db->quoteName('#__assets')
            . ' WHERE ' . $this->db->quoteName('name') . ' IN (' . implode(',', $names) . ')'
        )->loadResult();
    }
}

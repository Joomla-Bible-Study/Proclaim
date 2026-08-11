<?php

/**
 * Section permissions have to actually govern something (#1653).
 *
 * Phase 1 created the `com_proclaim.<section>` assets and phase 1.5 converted
 * the batch-button guards. The toolbars kept asking `com_proclaim`, because
 * they go through `ContentHelper::getActions()` — which cannot ask a section
 * question at all:
 *
 *     if ($section && $id) { $assetName .= '.' . $section . '.' . (int) $id; }
 *
 * discards the section unless an item id comes with it, and the action list is
 * read from a hardcoded `/access/section[@name="component"]/` xpath either way.
 * So `getActions('com_proclaim', 'teacher')` returns exactly what
 * `getActions('com_proclaim')` returns, and a Permissions panel built on top of
 * it would have set values that governed nothing.
 *
 * `Cwmassets::sectionActions()` is the call that does ask. This measures both
 * halves: core's is inert, ours is not.
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
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
class CwmassetsSectionActionsTest extends IntegrationTestCase
{
    /**
     * The section used for every assertion here.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private const SECTION = 'teacher';

    /**
     * @var DatabaseInterface|null
     * @since __DEPLOY_VERSION__
     */
    private ?DatabaseInterface $db = null;

    /**
     * @var mixed
     * @since __DEPLOY_VERSION__
     */
    private mixed $savedIdentity = null;

    /**
     * Section asset id under test, and its rules on the way in.
     *
     * @var int
     * @since __DEPLOY_VERSION__
     */
    private int $assetId = 0;

    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private string $savedRules = '{}';

    /**
     * Whether this test created the section asset, and must remove it again.
     *
     * @var bool
     * @since __DEPLOY_VERSION__
     */
    private bool $created = false;

    /**
     * Groups the acting identity belongs to.
     *
     * @var list<int>
     * @since __DEPLOY_VERSION__
     */
    private array $groups = [];

    /**
     * `com_proclaim` asset id and its rules, when a test stages a grant on it.
     *
     * @var int
     * @since __DEPLOY_VERSION__
     */
    private int $componentAssetId = 0;

    /**
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private string $savedComponentRules = '';

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

        if (!\in_array(self::SECTION, Cwmassets::declaredSections(), true)) {
            $this->markTestSkipped('access.xml is not readable from this install path.');
        }

        $app                 = Factory::getApplication();
        $this->savedIdentity = $app->getIdentity();

        // sectionActions() resolves the caller's groups, so it needs a real
        // user — and one that is not a Super User, whose core.admin at root
        // short-circuits authorise() before any asset is consulted.
        $uid = $this->nonRootUserId();

        if ($uid === 0) {
            $this->markTestSkipped('No non-Super-User with a group is available');
        }

        $app->loadIdentity(User::getInstance($uid));
        $this->groups = array_map('intval', Access::getGroupsByUser($uid));

        $existing      = $this->sectionAssetExists();
        $this->assetId = Cwmassets::sectionId(self::SECTION);
        $this->created = !$existing && $this->assetId > 0;

        if ($this->assetId < 1) {
            $this->markTestSkipped('Section asset could not be resolved');
        }

        $asset = new Asset($this->db);
        $asset->load($this->assetId);
        $this->savedRules = (string) $asset->rules;
    }

    /**
     * Restore the asset exactly as found, and the identity with it.
     *
     * Not a transaction: `Table\Asset::store()` takes a table lock, which is an
     * implicit COMMIT in MySQL, so a rollback would leave the write behind.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        if ($this->assetId > 0) {
            if ($this->created) {
                $asset = new Asset($this->db);

                if ($asset->load($this->assetId)) {
                    $asset->delete($this->assetId);
                }

                Cwmassets::clearSectionCache();
            } else {
                $this->writeRules($this->savedRules);
            }
        }

        if ($this->componentAssetId > 0) {
            $restore = $this->db->createQuery()
                ->update($this->db->quoteName('#__assets'))
                ->set($this->db->quoteName('rules') . ' = :rules')
                ->where($this->db->quoteName('id') . ' = :id')
                ->bind(':rules', $this->savedComponentRules)
                ->bind(':id', $this->componentAssetId, \Joomla\Database\ParameterType::INTEGER);

            $this->db->setQuery($restore)->execute();
        }

        Access::clearStatics();

        if ($this->savedIdentity !== null) {
            Factory::getApplication()->loadIdentity($this->savedIdentity);
        }

        parent::tearDown();
    }

    /**
     * Id of a user who holds a group but is not a Super User.
     *
     * @return  int
     * @since __DEPLOY_VERSION__
     */
    private function nonRootUserId(): int
    {
        $query = $this->db->createQuery()
            ->select('DISTINCT ' . $this->db->quoteName('user_id'))
            ->from($this->db->quoteName('#__user_usergroup_map'));

        foreach ((array) $this->db->setQuery($query)->loadColumn() as $candidate) {
            if (!Access::check((int) $candidate, 'core.admin')) {
                return (int) $candidate;
            }
        }

        return 0;
    }

    /**
     * Whether the section asset was already present before this test ran.
     *
     * @return  bool
     * @since __DEPLOY_VERSION__
     */
    private function sectionAssetExists(): bool
    {
        $query = $this->db->createQuery()
            ->select('id')
            ->from($this->db->quoteName('#__assets'))
            ->where($this->db->quoteName('name') . ' = :name')
            ->bind(':name', $name);

        $name = 'com_proclaim.' . self::SECTION;

        return (int) $this->db->setQuery($query)->loadResult() > 0;
    }

    /**
     * Grant core.edit on `com_proclaim` to every group the acting user holds.
     *
     * Staged, not assumed: the assertion needs a component-level allow so that
     * a section-level deny has something to overturn. Restored in tearDown.
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    private function grantComponentEdit(): void
    {
        $query = $this->db->createQuery()
            ->select('id, rules')
            ->from($this->db->quoteName('#__assets'))
            ->where($this->db->quoteName('name') . ' = ' . $this->db->quote('com_proclaim'));

        $row = $this->db->setQuery($query)->loadObject();

        if ($row === null) {
            $this->fail('com_proclaim has no #__assets row; the component is not installed here.');
        }

        $this->componentAssetId    = (int) $row->id;
        $this->savedComponentRules = (string) $row->rules;

        $rules = json_decode($this->savedComponentRules, true, 512, \JSON_THROW_ON_ERROR) ?: [];

        foreach ($this->groups as $groupId) {
            $rules['core.edit'][(string) $groupId] = 1;
        }

        $encoded = json_encode($rules, \JSON_THROW_ON_ERROR);
        $id      = $this->componentAssetId;

        $update = $this->db->createQuery()
            ->update($this->db->quoteName('#__assets'))
            ->set($this->db->quoteName('rules') . ' = :rules')
            ->where($this->db->quoteName('id') . ' = :id')
            ->bind(':rules', $encoded)
            ->bind(':id', $id, \Joomla\Database\ParameterType::INTEGER);

        $this->db->setQuery($update)->execute();

        Access::clearStatics();
    }

    /**
     * Write rules straight onto the section asset and drop every ACL cache.
     *
     * @param   string  $rules  A JSON rule set
     *
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    private function writeRules(string $rules): void
    {
        $query = $this->db->createQuery()
            ->update($this->db->quoteName('#__assets'))
            ->set($this->db->quoteName('rules') . ' = :rules')
            ->where($this->db->quoteName('id') . ' = :id')
            ->bind(':rules', $rules)
            ->bind(':id', $this->assetId, \Joomla\Database\ParameterType::INTEGER);

        $this->db->setQuery($query)->execute();

        Access::clearStatics();
    }

    #[TestDox('sectionActions() asks the section asset, not the component')]
    public function testSectionActionsAsksTheSectionAsset(): void
    {
        $user  = Factory::getApplication()->getIdentity();
        $canDo = Cwmassets::sectionActions(self::SECTION);

        foreach (['core.create', 'core.delete', 'core.edit', 'core.edit.state', 'core.edit.own'] as $action) {
            $this->assertSame(
                $user->authorise($action, 'com_proclaim.' . self::SECTION),
                $canDo->get($action),
                "sectionActions() disagreed with authorise() on {$action}; it is not asking the section asset."
            );
        }
    }

    #[TestDox('a deny on the section changes sectionActions() but not the component answer')]
    public function testSectionDenyBitesOnlyTheSection(): void
    {
        // Grant on the component first rather than skipping when the site's
        // own rules happen not to allow it. A skip here would read as a pass
        // while proving nothing, and this is the one assertion that shows
        // section permissions govern anything at all.
        $this->grantComponentEdit();

        if (!Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_proclaim')) {
            $this->fail(
                'core.edit could not be granted on com_proclaim for the acting user. '
                . 'An explicit deny above the component (root, or a parent group) is overriding it, '
                . 'so this environment cannot express the baseline the assertion needs.'
            );
        }

        $this->assertTrue(
            Cwmassets::sectionActions(self::SECTION)->get('core.edit'),
            'Baseline: the section must inherit core.edit before the deny is written.'
        );

        $deny = json_encode(['core.edit' => array_fill_keys(array_map('strval', $this->groups), 0)]);
        $this->writeRules((string) $deny);

        $this->assertFalse(
            Cwmassets::sectionActions(self::SECTION)->get('core.edit'),
            'A deny written to the section asset did not reach sectionActions(). '
            . 'Section permissions govern nothing.'
        );

        // Asserted through authorise() rather than ContentHelper::getActions(),
        // which reads access.xml only from JPATH_ADMINISTRATOR and so returns
        // an all-null CanDo under test. Comparing against null passes without
        // measuring anything.
        $this->assertTrue(
            Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_proclaim'),
            'A section deny leaked into the component answer. Sections must not re-permission '
            . 'the component itself.'
        );
    }

    #[TestDox("core's getActions() cannot ask a section question, which is why this helper exists")]
    public function testCoreGetActionsIgnoresASectionWithoutAnId(): void
    {
        $this->grantComponentEdit();

        $deny = json_encode(['core.edit' => array_fill_keys(array_map('strval', $this->groups), 0)]);
        $this->writeRules((string) $deny);

        $user = Factory::getApplication()->getIdentity();

        // Characterises core, not Proclaim: the two-argument form resolves the
        // same asset as the bare one. Asserted on the asset answers core would
        // authorise against, because getActions() itself cannot find
        // access.xml here. If a future Joomla makes the two-argument form
        // section-aware, the section answer stops matching the component one
        // and this fails — at which point the helper, and the contract test
        // forbidding the form, can be revisited.
        $this->assertTrue(
            $user->authorise('core.edit', 'com_proclaim'),
            'Baseline: the component must still allow core.edit after the section deny.'
        );
        $this->assertFalse(
            $user->authorise('core.edit', 'com_proclaim.' . self::SECTION),
            'Baseline: the section must deny core.edit.'
        );
        $this->assertSame(
            ContentHelper::getActions('com_proclaim')->get('core.edit'),
            ContentHelper::getActions('com_proclaim', self::SECTION)->get('core.edit'),
            'ContentHelper::getActions() answered differently for a section with no item id. '
            . 'Core changed; revisit Cwmassets::sectionActions() and the contract test guarding '
            . 'the two-argument form.'
        );
    }

    #[TestDox('an undeclared section falls back to the component rather than answering nothing')]
    public function testUndeclaredSectionFallsBackToTheComponent(): void
    {
        $this->grantComponentEdit();

        $canDo = Cwmassets::sectionActions('no_such_section');

        $this->assertTrue(
            $canDo->get('core.edit'),
            'An undeclared section must answer exactly what the component answers, which is what '
            . 'every call site produced before section assets existed — not null, which is what a '
            . 'CanDo built from an unreadable access.xml returns.'
        );
        $this->assertSame(
            Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_proclaim'),
            $canDo->get('core.edit'),
            'The fallback must equal the component answer.'
        );
    }
}

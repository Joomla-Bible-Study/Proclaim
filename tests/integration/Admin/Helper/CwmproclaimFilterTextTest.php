<?php

/**
 * Integration tests for CwmproclaimHelper::filterText().
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1591.
 *
 * filterText() built its tag lists with `array_merge([], ...$tempTags)`, where
 * $tempTags is a flat array of strings. Spreading it passes each string as a
 * positional argument and array_merge() requires arrays, so the method raised a
 * TypeError as soon as any group had a tag configured -- every call, not an
 * edge case. It also overwrote rather than accumulated, contradicting its own
 * "Each list is cumulative" comment.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmproclaimHelper::class)]
class CwmproclaimFilterTextTest extends IntegrationTestCase
{
    /**
     * @var  mixed
     */
    private mixed $savedIdentity = null;

    /**
     * @var  mixed
     */
    private mixed $savedFilters = null;

    /**
     * @var  int
     */
    private int $groupId = 0;

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $app                 = Factory::getApplication();
        $this->savedIdentity = $app->getIdentity();

        // filterText() resolves the caller's groups, so it needs a real user.
        $db   = Factory::getContainer()->get(DatabaseDriver::class);
        $uid  = (int) $db->setQuery(
            'SELECT ' . $db->quoteName('user_id') . ' FROM ' . $db->quoteName('#__user_usergroup_map'),
            0,
            1
        )->loadResult();

        if ($uid === 0) {
            $this->markTestSkipped('No user with a group available');
        }

        $app->loadIdentity(User::getInstance($uid));

        $groups = Access::getGroupsByUser($uid);

        if ($groups === []) {
            $this->markTestSkipped('Test user belongs to no group');
        }

        $this->groupId = (int) $groups[0];

        // ComponentHelper::getParams() hands back a shared Registry, so the
        // filter config can be staged on it and restored afterwards.
        $params             = ComponentHelper::getParams('com_proclaim');
        $this->savedFilters = $params->get('filters');
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        ComponentHelper::getParams('com_proclaim')->set('filters', $this->savedFilters);
        Factory::getApplication()->loadIdentity($this->savedIdentity);

        parent::tearDown();
    }

    #[TestDox('A configured blacklist filters text instead of raising a TypeError')]
    public function testBlacklistFiltersWithoutCrashing(): void
    {
        $this->stageFilters('BL', 'script,iframe', 'onclick');

        $result = CwmproclaimHelper::filterText('<p>Keep</p><script>alert(1)</script>');

        $this->assertStringNotContainsString('<script', $result, 'A blacklisted tag must be stripped');
        $this->assertStringContainsString('Keep', $result, 'Non-blacklisted content must survive');
    }

    #[TestDox('A configured whitelist filters text instead of raising a TypeError')]
    public function testWhitelistFiltersWithoutCrashing(): void
    {
        $this->stageFilters('WL', 'p', 'class');

        $result = CwmproclaimHelper::filterText('<p>Keep</p><script>alert(1)</script>');

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringContainsString('Keep', $result);
    }

    #[TestDox('Tags from every one of the user\'s groups accumulate rather than overwrite')]
    public function testListsAccumulateAcrossGroups(): void
    {
        $groups = Access::getGroupsByUser((int) Factory::getApplication()->getIdentity()->id);

        if (\count($groups) < 2) {
            $this->markTestSkipped('Test user is in one group; cannot observe accumulation');
        }

        // Different tag per group. Overwriting would keep only the last one, so
        // a document carrying both proves the lists accumulate -- the behaviour
        // the method's own comment claims.
        $filters                     = (object) [];
        $filters->{(int) $groups[0]} = (object) ['filter_type' => 'BL', 'filter_tags' => 'script', 'filter_attributes' => ''];
        $filters->{(int) $groups[1]} = (object) ['filter_type' => 'BL', 'filter_tags' => 'iframe', 'filter_attributes' => ''];

        ComponentHelper::getParams('com_proclaim')->set('filters', $filters);

        $result = CwmproclaimHelper::filterText('<script>a</script><iframe src="x"></iframe><p>Keep</p>');

        $this->assertStringNotContainsString('<script', $result, 'Tag from the first group must be filtered');
        $this->assertStringNotContainsString('<iframe', $result, 'Tag from the second group must be filtered too');
        $this->assertStringContainsString('Keep', $result);
    }

    /**
     * @param   string  $type        Filter type (BL / WL).
     * @param   string  $tags        Comma-separated tags.
     * @param   string  $attributes  Comma-separated attributes.
     *
     * @return  void
     */
    private function stageFilters(string $type, string $tags, string $attributes): void
    {
        $filters                    = (object) [];
        $filters->{$this->groupId}  = (object) [
            'filter_type'       => $type,
            'filter_tags'       => $tags,
            'filter_attributes' => $attributes,
        ];

        ComponentHelper::getParams('com_proclaim')->set('filters', $filters);
    }
}

<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Plugin\System;

use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use CWM\Plugin\System\Proclaim\Extension\Proclaim;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The system plugin redirects old com_biblestudy item URLs, which carry an
 * old view name such as "sermon" before an <id>-<alias>. A menu item can carry
 * one of those names as its own alias, and a URL under it is not an old one.
 *
 * Old download and landing URLs redirect too. A current task URL under such a
 * menu item ends in the old view name as well, and is left alone.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyItemRedirectTest extends IntegrationTestCase
{
    private ?DatabaseInterface $db = null;

    private Proclaim $plugin;

    private string $menuRoute;

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);

        // The redirect decision reads only the database.
        $this->plugin = (new \ReflectionClass(Proclaim::class))->newInstanceWithoutConstructor();

        // A sermons menu item aliased "sermons", like /resources/sermons on a real site.
        $this->menuRoute = $this->seedMenuItem('sermons', 1);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have gone away.
            }
        }

        parent::tearDown();
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A message whose alias starts with digits, under a menu item aliased "sermons", is left to the router')]
    public function testDigitAliasUnderMenuItemIsLeftToTheRouter(): void
    {
        $this->assertNull($this->redirectPathFor('/' . $this->menuRoute . '/911-rescue'));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An <id>-<alias> URL under that menu item is left to the router, which keeps the menu path')]
    public function testIdAliasUnderMenuItemIsLeftToTheRouter(): void
    {
        $id = $this->seedStudy();

        $this->assertNull($this->redirectPathFor('/' . $this->menuRoute . '/' . $id . '-zz-old-alias'));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The menu route is recognised behind a base path, index.php and a language prefix')]
    public function testMenuRouteIsRecognisedBehindPrefixes(): void
    {
        $this->assertNull($this->redirectPathFor('/joomla/index.php/en/' . $this->menuRoute . '/911-rescue'));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old item URL under the menu item still redirects to the message\'s current alias')]
    public function testOldItemUrlRedirectsToTheCurrentAlias(): void
    {
        $alias = $this->uniqueAlias('current');
        $id    = $this->seedStudy($alias);

        $this->assertSame(
            '/' . $this->menuRoute . '/' . $alias,
            $this->redirectPathFor('/' . $this->menuRoute . '/sermon/' . $id . '-zz-old-alias/1')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An unpublished menu item does not stop an old item URL from redirecting')]
    public function testUnpublishedMenuItemDoesNotShieldAnOldUrl(): void
    {
        $route = $this->seedMenuItem('sermon', 0);
        $alias = $this->uniqueAlias('current');
        $id    = $this->seedStudy($alias);

        $this->assertSame(
            '/' . substr($route, 0, -\strlen('/sermon')) . '/' . $alias,
            $this->redirectPathFor('/' . $route . '/' . $id . '-zz-old-alias/1')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A current task URL under a menu item aliased "sermons" is left to the router')]
    public function testCurrentTaskUnderMenuItemIsLeftToTheRouter(): void
    {
        // The list's AJAX filter, and the capitalised form Cwmmedia writes for downloads.
        $this->assertNull($this->sefTargetFor('/' . $this->menuRoute, 'cwmsermons.filterAjax'));
        $this->assertNull($this->sefTargetFor('/' . $this->menuRoute, 'Cwmsermon.download', 838));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old download URL still redirects, naming its controller once')]
    public function testOldDownloadUrlRedirectsWithOneControllerPrefix(): void
    {
        $target = $this->sefTargetFor('/' . $this->menuRoute . '/sermon', 'download', 838);

        $this->assertIsString($target);
        $this->assertStringStartsWith('index.php?', $target);

        parse_str(substr($target, \strlen('index.php?')), $query);

        $this->assertSame('com_proclaim', $query['option']);
        $this->assertSame('cwmsermon.download', $query['task']);
        $this->assertSame('838', $query['mid']);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A task that is not an old one, or a media id with no task, is left to the router')]
    public function testOtherTaskUrlsAreLeftToTheRouter(): void
    {
        $this->assertNull($this->sefTargetFor('/' . $this->menuRoute . '/sermon', 'playHit'));
        $this->assertNull($this->sefTargetFor('/' . $this->menuRoute . '/sermon', '', 838));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('On a site in a subfolder, an old item URL redirects into that folder once')]
    public function testOldItemUrlInSubfolderNamesTheFolderOnce(): void
    {
        $alias = $this->uniqueAlias('current');
        $id    = $this->seedStudy($alias);

        $this->assertSame(
            '/joomla/' . $this->menuRoute . '/' . $alias,
            $this->sefTargetFor('/joomla/' . $this->menuRoute . '/sermon/' . $id . '-zz-old-alias/1')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('On a site in a subfolder, an old landing URL redirects into that folder once, filter applied')]
    public function testLandingUrlInSubfolderNamesTheFolderOnce(): void
    {
        $this->assertSame(
            '/joomla/' . $this->menuRoute . '?filter_teacher=115',
            $this->sefTargetFor('/joomla/' . $this->menuRoute . '/landing/teacher/115/1')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old landing URL under the home page redirects to the home page')]
    public function testLandingUrlUnderTheHomePageRedirectsHome(): void
    {
        $this->assertSame('/?filter_teacher=115', $this->sefTargetFor('/landing/teacher/115/1'));
        $this->assertSame('/', $this->sefTargetFor('/landing/sermons/1'));
    }

    /**
     * @param   string  $path  The request path
     *
     * @return  ?string  Where the plugin would redirect to, or null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function redirectPathFor(string $path): ?string
    {
        return (new \ReflectionMethod(Proclaim::class, 'legacyItemRedirectPath'))->invoke($this->plugin, $path);
    }

    /**
     * @param   string  $path  The request path
     * @param   string  $task  The request's task
     * @param   int     $mid   The request's media file id
     *
     * @return  ?string  The URL the plugin would redirect an old SEF URL to, or null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function sefTargetFor(string $path, string $task = '', int $mid = 0): ?string
    {
        return (new \ReflectionMethod(Proclaim::class, 'legacySefRedirectTarget'))->invoke($this->plugin, $path, $task, $mid);
    }

    /**
     * Seed a site menu item under a parent of its own, so the alias cannot
     * collide with a real item at the same level.
     *
     * @param   string  $alias      The menu item alias
     * @param   int     $published  The menu item state
     *
     * @return  string  The menu item's route
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedMenuItem(string $alias, int $published): string
    {
        $route = $this->uniqueAlias('menu') . '/' . $alias;
        $item  = (object) [
            'menutype'     => 'zz-legacy-test',
            'title'        => 'zz legacy redirect test',
            'alias'        => $alias,
            'path'         => $route,
            'link'         => 'index.php?option=com_proclaim&view=cwmsermons',
            'type'         => 'component',
            'published'    => $published,
            'parent_id'    => 987654,
            'level'        => 2,
            'component_id' => 0,
            'access'       => 1,
            'img'          => '',
            'params'       => '{}',
            'lft'          => 0,
            'rgt'          => 0,
            'language'     => '*',
            'client_id'    => 0,
        ];

        $this->db->insertObject('#__menu', $item);

        return $route;
    }

    /**
     * @param   ?string  $alias  The message alias
     *
     * @return  int  The new message id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedStudy(?string $alias = null): int
    {
        $study = (object) [
            'studytitle' => 'zz legacy redirect study',
            'alias'      => $alias ?? $this->uniqueAlias('study'),
            'studydate'  => '2026-01-15 10:00:00',
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
            'ordering'   => 0,
            'params'     => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $study);

        return (int) $this->db->insertid();
    }
}

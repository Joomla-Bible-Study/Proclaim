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
 * The system plugin redirects old com_biblestudy URLs. It works out what an old
 * URL refers to, as an internal URL, and the router builds the address.
 *
 * An old item URL carries an old view name such as "sermon" before the record
 * id. A menu item can carry one of those names as its own alias, and a URL under
 * it is not an old one. A current task URL under such a menu item ends in the
 * old view name as well, and is left alone.
 *
 * @since  __DEPLOY_VERSION__
 */
class LegacyItemRedirectTest extends IntegrationTestCase
{
    private ?DatabaseInterface $db = null;

    private Proclaim $plugin;

    private string $menuRoute;

    private int $menuId;

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
        [$this->menuRoute, $this->menuId] = $this->seedMenuItem('sermons', 1);
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
        // The alias starts with another message's id, which an old URL would point at.
        $other = $this->seedStudy();
        $this->seedStudy($other . '-rescue');

        $this->assertNull($this->targetFor('/' . $this->menuRoute . '/' . $other . '-rescue'));
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

        $this->assertNull($this->targetFor('/' . $this->menuRoute . '/' . $id . '-zz-old-alias'));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('The menu route is recognised behind a base path, index.php and a language prefix')]
    public function testMenuRouteIsRecognisedBehindPrefixes(): void
    {
        $id = $this->seedStudy();

        $this->assertNull($this->targetFor('/joomla/index.php/en/' . $this->menuRoute . '/' . $id . '-rescue'));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old item URL refers to the message, with no menu item: the router picks one as for any message link')]
    public function testOldItemUrlRefersToTheMessage(): void
    {
        $id = $this->seedStudy();

        $this->assertSame(
            $this->recordQuery('cwmsermon', $id),
            $this->queryFor('/' . $this->menuRoute . '/sermon/' . $id . '-zz-old-alias/1')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old item URL with a bare id, with or without a template id, refers to the message')]
    public function testBareIdOldItemUrlRefersToTheMessage(): void
    {
        $id = $this->seedStudy();

        $this->assertSame($this->recordQuery('cwmsermon', $id), $this->queryFor('/' . $this->menuRoute . '/sermon/' . $id));
        $this->assertSame($this->recordQuery('cwmsermon', $id), $this->queryFor('/' . $this->menuRoute . '/sermon/' . $id . '/1'));
    }

    /**
     * The router adds the site's folder to the address it builds, so the folder
     * is not part of what an old URL refers to.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old item URL means the same at the site root, in a subfolder and behind index.php')]
    public function testOldItemUrlMeansTheSameWhereverItSits(): void
    {
        $id       = $this->seedStudy();
        $expected = $this->recordQuery('cwmsermon', $id);

        $this->assertSame($expected, $this->queryFor('/sermon/' . $id . '-zz-old-alias/1'));
        $this->assertSame($expected, $this->queryFor('/joomla/sermon/' . $id . '-zz-old-alias/1'));
        $this->assertSame($expected, $this->queryFor('/index.php/sermon/' . $id));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Old teacher and series URLs refer to the teacher and the series')]
    public function testOldTeacherAndSeriesUrlsReferToTheirRecords(): void
    {
        $teacher = $this->seedTeacher();
        $series  = $this->seedSeries();

        $this->assertSame($this->recordQuery('cwmteacher', $teacher), $this->queryFor('/teacher/' . $teacher . '-zz-old-alias/1'));
        $this->assertSame($this->recordQuery('cwmteacher', $teacher), $this->queryFor('/teacher/' . $teacher));
        $this->assertSame($this->recordQuery('cwmseriesdisplay', $series), $this->queryFor('/seriesdisplay/' . $series . '-zz-old-alias'));
        $this->assertSame(
            $this->recordQuery('cwmseriespodcastdisplay', $series),
            $this->queryFor('/seriespodcastlist/' . $series . '-zz-old-alias/1')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('On a list view a bare number may be a template id, so only <id>-<alias> is read as a record')]
    public function testListViewNeedsAnAlias(): void
    {
        $id     = $this->seedStudy();
        $prefix = '/' . $this->uniqueAlias('elsewhere');

        // The alias form redirects from the same prefix, so the menu-route guard is not what stops a bare id.
        $this->assertSame($this->recordQuery('cwmsermon', $id), $this->queryFor($prefix . '/sermons/' . $id . '-zz-old-alias'));
        $this->assertSame($this->recordQuery('cwmsermon', $id), $this->queryFor($prefix . '/latest/' . $id . '-zz-old-alias'));
        $this->assertNull($this->targetFor($prefix . '/sermons/' . $id));
        $this->assertNull($this->targetFor($prefix . '/latest/' . $id));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A missing, unpublished or trashed record is left to the router; an archived one is redirected')]
    public function testOnlyRecordsTheRouterServesAreRedirected(): void
    {
        $unpublished = $this->seedStudy(null, 0);
        $trashed     = $this->seedStudy(null, -2);
        $archived    = $this->seedStudy(null, 2);

        $this->assertNull($this->targetFor('/sermon/2147483647-zz-old-alias/1'));
        $this->assertNull($this->targetFor('/sermon/' . $unpublished . '-zz-old-alias/1'));
        $this->assertNull($this->targetFor('/sermon/' . $trashed));
        $this->assertSame($this->recordQuery('cwmsermon', $archived), $this->queryFor('/sermon/' . $archived));
    }

    /**
     * A redirect to an address that is read as an old URL again would point at
     * itself.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Addresses the router builds are not read as old URLs')]
    public function testRouterBuiltAddressesAreLeftAlone(): void
    {
        $alias   = $this->uniqueAlias('current');
        $id      = $this->seedStudy($alias);
        $teacher = $this->seedTeacher();

        // Under a message list, with ids in URLs and without
        $this->assertNull($this->targetFor('/' . $this->menuRoute . '/' . $alias));
        $this->assertNull($this->targetFor('/' . $this->menuRoute . '/' . $id . '-' . $alias));

        // With no menu item for the view
        $this->assertNull($this->targetFor('/component/proclaim/cwmsermons/' . $id . '-' . $alias));
        $this->assertNull($this->targetFor('/component/proclaim/cwmsermons/' . $alias));
        $this->assertNull($this->targetFor('/component/proclaim/cwmteachers/' . $teacher . '-zz-teacher'));

        // The filtered list an old landing URL redirects to
        $this->assertNull($this->targetFor('/' . $this->menuRoute));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An unpublished menu item does not stop an old item URL from redirecting')]
    public function testUnpublishedMenuItemDoesNotShieldAnOldUrl(): void
    {
        [$route] = $this->seedMenuItem('sermon', 0);
        $id      = $this->seedStudy();

        $this->assertSame($this->recordQuery('cwmsermon', $id), $this->queryFor('/' . $route . '/' . $id . '-zz-old-alias/1'));
        $this->assertSame($this->recordQuery('cwmsermon', $id), $this->queryFor('/' . $route . '/' . $id));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A bare number under a published menu item aliased "sermon" is left to the router')]
    public function testBareNumberUnderMenuItemIsLeftToTheRouter(): void
    {
        [$route] = $this->seedMenuItem('sermon', 1);
        $id      = $this->seedStudy();

        $this->assertNull($this->targetFor('/' . $route . '/' . $id));
        $this->assertNull($this->targetFor('/' . $route . '/' . $id . '/1'));
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
        $this->assertNull($this->targetFor('/' . $this->menuRoute, 'cwmsermons.filterAjax'));
        $this->assertNull($this->targetFor('/' . $this->menuRoute, 'Cwmsermon.download', 838));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old download URL still redirects, naming its controller once')]
    public function testOldDownloadUrlRedirectsWithOneControllerPrefix(): void
    {
        $query = $this->queryFor('/' . $this->menuRoute . '/sermon', 'download', 838);

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
        $this->assertNull($this->targetFor('/' . $this->menuRoute . '/sermon', 'playHit'));
        $this->assertNull($this->targetFor('/' . $this->menuRoute . '/sermon', '', 838));
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old landing URL under a message list keeps that list, filter applied')]
    public function testLandingUrlKeepsItsMessageList(): void
    {
        $this->assertSame(
            ['option' => 'com_proclaim', 'view' => 'cwmsermons', 'filter_teacher' => '115', 'Itemid' => (string) $this->menuId],
            $this->queryFor('/joomla/' . $this->menuRoute . '/landing/teacher/115/1')
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An old landing URL anywhere else leaves the choice of message list to the router')]
    public function testLandingUrlElsewhereLeavesTheListToTheRouter(): void
    {
        [$article] = $this->seedMenuItem('about', 1, 'index.php?option=com_content&view=article&id=1');
        [$landing] = $this->seedMenuItem('landing-page', 1, 'index.php?option=com_proclaim&view=cwmlandingpage');
        [$hidden]  = $this->seedMenuItem('old-sermons', 0);
        $filtered  = ['option' => 'com_proclaim', 'view' => 'cwmsermons', 'filter_teacher' => '115'];

        $this->assertSame($filtered, $this->queryFor('/' . $article . '/landing/teacher/115/1'));
        $this->assertSame($filtered, $this->queryFor('/' . $landing . '/landing/teacher/115/1'));
        $this->assertSame($filtered, $this->queryFor('/' . $hidden . '/landing/teacher/115/1'));
        $this->assertSame($filtered, $this->queryFor('/landing/teacher/115/1'));
        $this->assertSame(['option' => 'com_proclaim', 'view' => 'cwmsermons'], $this->queryFor('/landing/sermons/1'));
    }

    /**
     * @param   string  $path  The request path
     * @param   string  $task  The request's task
     * @param   int     $mid   The request's media file id
     *
     * @return  ?string  The internal URL the plugin would redirect an old URL to, or null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function targetFor(string $path, string $task = '', int $mid = 0): ?string
    {
        return (new \ReflectionMethod(Proclaim::class, 'legacySefRedirectTarget'))->invoke($this->plugin, $path, $task, $mid);
    }

    /**
     * @param   string  $path  The request path
     * @param   string  $task  The request's task
     * @param   int     $mid   The request's media file id
     *
     * @return  array<string, string>  The query of the internal URL the plugin would redirect to
     *
     * @since   __DEPLOY_VERSION__
     */
    private function queryFor(string $path, string $task = '', int $mid = 0): array
    {
        $target = $this->targetFor($path, $task, $mid);

        $this->assertIsString($target, $path . ' should redirect');
        $this->assertStringStartsWith('index.php?', $target);

        parse_str(substr($target, \strlen('index.php?')), $query);

        return $query;
    }

    /**
     * @param   string  $view  The record's view
     * @param   int     $id    The record id
     *
     * @return  array<string, string>  The query of a record's internal URL, with no menu item
     *
     * @since   __DEPLOY_VERSION__
     */
    private function recordQuery(string $view, int $id): array
    {
        return ['option' => 'com_proclaim', 'view' => $view, 'id' => (string) $id];
    }

    /**
     * Seed a site menu item under a parent of its own, so the alias cannot
     * collide with a real item at the same level.
     *
     * @param   string  $alias      The menu item alias
     * @param   int     $published  The menu item state
     * @param   string  $link       The menu item link
     *
     * @return  array{0: string, 1: int}  The menu item's route and id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedMenuItem(string $alias, int $published, string $link = 'index.php?option=com_proclaim&view=cwmsermons'): array
    {
        $route = $this->uniqueAlias('menu') . '/' . $alias;
        $item  = (object) [
            'menutype'     => 'zz-legacy-test',
            'title'        => 'zz legacy redirect test',
            'alias'        => $alias,
            'path'         => $route,
            'link'         => $link,
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

        return [$route, (int) $this->db->insertid()];
    }

    /**
     * @param   ?string  $alias      The message alias
     * @param   int      $published  The message state
     *
     * @return  int  The new message id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedStudy(?string $alias = null, int $published = 1): int
    {
        $study = (object) [
            'studytitle' => 'zz legacy redirect study',
            'alias'      => $alias ?? $this->uniqueAlias('study'),
            'studydate'  => '2026-01-15 10:00:00',
            'published'  => $published,
            'access'     => 1,
            'language'   => '*',
            'ordering'   => 0,
            'params'     => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $study);

        return (int) $this->db->insertid();
    }

    /**
     * @return  int  The new teacher id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedTeacher(): int
    {
        // Some databases still hold address as NOT NULL with no default
        $teacher = (object) [
            'teachername' => 'zz legacy redirect teacher',
            'alias'       => $this->uniqueAlias('teacher'),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
            'address'     => '',
        ];

        $this->db->insertObject('#__bsms_teachers', $teacher);

        return (int) $this->db->insertid();
    }

    /**
     * @return  int  The new series id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedSeries(): int
    {
        $series = (object) [
            'series_text' => 'zz legacy redirect series',
            'alias'       => $this->uniqueAlias('series'),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
        ];

        $this->db->insertObject('#__bsms_series', $series);

        return (int) $this->db->insertid();
    }
}

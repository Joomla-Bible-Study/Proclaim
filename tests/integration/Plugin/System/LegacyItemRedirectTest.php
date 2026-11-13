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
     * @param   string  $label  A readable part of the alias
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function uniqueAlias(string $label): string
    {
        return 'zz-legacy-' . $label . '-' . str_replace('.', '-', uniqid('', true));
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

<?php

/**
 * Integration tests for the update site the installer registers.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwminstallModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The `setupdateurl` finish step used to delete the component's update site
 * bindings and insert a replacement pointing at ARS stream id=2 — the stream
 * that stops at 9.2.8 because 9.x to 10.x is a migration rather than an
 * in-place update. It ran on every pass of the installer, including the
 * "no DB changes" one, so a site was left on a stream that could never offer
 * it a 10.x release, and the package postflight's retirement of component-owned
 * sites was undone moments after it happened.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwminstallModel::class)]
class CwminstallUpdateSiteTest extends IntegrationTestCase
{
    /**
     * The stream carrying current releases.
     */
    private const CURRENT_STREAM_ID = 'id=1';

    /**
     * The 9.x stream. Nothing 10.x is ever announced on it.
     */
    private const LEGACY_STREAM = 'https://www.christianwebministries.org/index.php'
        . '?option=com_ars&view=update&task=stream&id=2&format=xml';

    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var  int
     */
    private int $componentId = 0;

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);

        // true: nested transactions. Anything that commits underneath would
        // otherwise take the rollback with it and leave the dev database
        // holding whatever this test seeded.
        $this->db->transactionStart(true);

        $this->componentId = (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('extension_id'))
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_proclaim'))
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('component'))
        )->loadResult();

        if ($this->componentId === 0) {
            $this->markTestSkipped('com_proclaim is not registered in the test database');
        }

        // Start from a known shape rather than whatever this dev database has
        // accumulated: no update site claims Proclaim until a test makes one.
        $this->clearProclaimUpdateSites();
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        parent::tearDown();
    }

    #[TestDox('A component-only site is given the stream that carries current releases')]
    public function testComponentOnlySiteGetsTheCurrentStream(): void
    {
        $this->seedUpdateSite('Proclaim Package', self::LEGACY_STREAM, $this->componentId);

        $this->runStep();

        $sites = $this->sitesFor($this->componentId);

        $this->assertCount(
            1,
            $sites,
            'A site with no package must keep exactly one update site of its own'
        );

        $location = $sites[0]['location'];

        $this->assertStringContainsString(
            self::CURRENT_STREAM_ID,
            $location,
            'The component-only site must be pointed at the stream carrying 10.x releases'
        );
        $this->assertStringNotContainsString(
            'id=2',
            $location,
            'The 9.x stream can never offer a 10.x release'
        );
        $this->assertStringNotContainsString(
            '&amp;',
            $location,
            'An HTML-escaped URL reaches ARS as a parameter named "amp;view" and returns nothing'
        );
    }

    #[TestDox('When the package owns updates the component is left without a duplicate site')]
    public function testPackageOwnedUpdatesLeaveNoComponentSite(): void
    {
        $packageId   = $this->seedPackageExtension();
        $packageSite = $this->seedUpdateSite(
            'CWM Proclaim Package',
            'https://www.christianwebministries.org/index.php?option=com_ars&view=update&task=stream&format=xml&id=1',
            $packageId
        );
        $this->seedUpdateSite('Proclaim Package', self::LEGACY_STREAM, $this->componentId);

        $this->runStep();

        $this->assertSame(
            [],
            $this->sitesFor($this->componentId),
            'The package announces updates, so a second component-owned site only polls twice'
        );
        $this->assertCount(
            1,
            $this->sitesFor($packageId),
            'The package keeps its own update site'
        );
        $this->assertSame(
            1,
            $this->siteExists($packageSite),
            'The package update site row must survive untouched'
        );
    }

    #[TestDox('An update site shared with another extension keeps serving that extension')]
    public function testSharedUpdateSiteSurvives(): void
    {
        $siteId = $this->seedUpdateSite('Shared', self::LEGACY_STREAM, $this->componentId);

        // A second extension claiming the same row. Deleting the row wholesale
        // would take this extension's update channel with it.
        $other = (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('extension_id'))
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('com_content'))
        )->loadResult();

        $this->assertGreaterThan(0, $other, 'com_content must exist to stand in as the other extension');

        $binding                 = new \stdClass();
        $binding->update_site_id = $siteId;
        $binding->extension_id   = $other;
        $this->db->insertObject('#__update_sites_extensions', $binding);

        $this->runStep();

        $this->assertSame(
            1,
            $this->siteExists($siteId),
            'A shared update site row must survive'
        );
        $this->assertSame(
            [],
            array_filter($this->sitesFor($this->componentId), static fn ($s) => (int) $s['id'] === $siteId),
            'Proclaim releases its own claim on the shared row'
        );
        $this->assertSame(
            1,
            (int) $this->db->setQuery(
                $this->db->createQuery()
                    ->select('COUNT(*)')
                    ->from($this->db->quoteName('#__update_sites_extensions'))
                    ->where($this->db->quoteName('update_site_id') . ' = ' . $siteId)
                    ->where($this->db->quoteName('extension_id') . ' = ' . $other)
            )->loadResult(),
            'The other extension keeps its binding'
        );
    }

    /**
     * Run the finish step under test.
     *
     * @return  void
     */
    private function runStep(): void
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var CwminstallModel $model */
        $model = $factory->createModel('Cwminstall', 'Administrator', ['ignore_request' => true]);

        $this->assertInstanceOf(CwminstallModel::class, $model);

        $method = new \ReflectionMethod($model, 'setComponentUpdateSite');
        $method->invoke($model);
    }

    /**
     * @param   string  $name         Update site name
     * @param   string  $location     Stream URL
     * @param   int     $extensionId  Extension to bind it to
     *
     * @return  int  The new update site id
     */
    private function seedUpdateSite(string $name, string $location, int $extensionId): int
    {
        $site           = new \stdClass();
        $site->name     = $name;
        $site->type     = 'extension';
        $site->location = $location;
        $site->enabled  = 1;
        $this->db->insertObject('#__update_sites', $site);

        $siteId = (int) $this->db->insertid();

        $binding                 = new \stdClass();
        $binding->update_site_id = $siteId;
        $binding->extension_id   = $extensionId;
        $this->db->insertObject('#__update_sites_extensions', $binding);

        return $siteId;
    }

    /**
     * A pkg_proclaim row, created only if the test database has none.
     *
     * @return  int  Extension id
     */
    private function seedPackageExtension(): int
    {
        $existing = (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('extension_id'))
                ->from($this->db->quoteName('#__extensions'))
                ->where($this->db->quoteName('element') . ' = ' . $this->db->quote('pkg_proclaim'))
                ->where($this->db->quoteName('type') . ' = ' . $this->db->quote('package'))
        )->loadResult();

        if ($existing > 0) {
            return $existing;
        }

        $row                 = new \stdClass();
        $row->name           = 'pkg_proclaim';
        $row->type           = 'package';
        $row->element        = 'pkg_proclaim';
        $row->folder         = '';
        $row->client_id      = 0;
        $row->enabled        = 1;
        $row->access         = 1;
        $row->protected      = 0;
        $row->manifest_cache = '';
        $row->params         = '{}';
        $row->custom_data    = '';
        $this->db->insertObject('#__extensions', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   int  $extensionId  Extension to look up
     *
     * @return  array<int, array{id: int, location: string}>
     */
    private function sitesFor(int $extensionId): array
    {
        $rows = $this->db->setQuery(
            $this->db->createQuery()
                ->select([$this->db->quoteName('u.update_site_id', 'id'), $this->db->quoteName('u.location')])
                ->from($this->db->quoteName('#__update_sites', 'u'))
                ->join(
                    'INNER',
                    $this->db->quoteName('#__update_sites_extensions', 'x'),
                    $this->db->quoteName('x.update_site_id') . ' = ' . $this->db->quoteName('u.update_site_id')
                )
                ->where($this->db->quoteName('x.extension_id') . ' = ' . $extensionId)
        )->loadAssocList() ?: [];

        return array_values($rows);
    }

    /**
     * @param   int  $siteId  Update site id
     *
     * @return  int  1 when the row is present
     */
    private function siteExists(int $siteId): int
    {
        return (int) $this->db->setQuery(
            $this->db->createQuery()
                ->select('COUNT(*)')
                ->from($this->db->quoteName('#__update_sites'))
                ->where($this->db->quoteName('update_site_id') . ' = ' . $siteId)
        )->loadResult();
    }

    /**
     * Drop every update site claiming Proclaim, so each test starts level.
     *
     * @return  void
     */
    private function clearProclaimUpdateSites(): void
    {
        $ids = array_map('intval', $this->db->setQuery(
            $this->db->createQuery()
                ->select($this->db->quoteName('x.update_site_id'))
                ->from($this->db->quoteName('#__update_sites_extensions', 'x'))
                ->join(
                    'INNER',
                    $this->db->quoteName('#__extensions', 'e'),
                    $this->db->quoteName('e.extension_id') . ' = ' . $this->db->quoteName('x.extension_id')
                )
                ->where($this->db->quoteName('e.element') . ' IN (' . $this->db->quote('com_proclaim')
                    . ', ' . $this->db->quote('pkg_proclaim') . ')')
        )->loadColumn() ?: []);

        if ($ids === []) {
            return;
        }

        $list = implode(',', $ids);

        foreach (['#__updates', '#__update_sites_extensions', '#__update_sites'] as $table) {
            $this->db->setQuery(
                $this->db->createQuery()
                    ->delete($this->db->quoteName($table))
                    ->where($this->db->quoteName('update_site_id') . ' IN (' . $list . ')')
            )->execute();
        }
    }
}

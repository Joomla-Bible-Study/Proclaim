<?php

/**
 * Integration tests for the legacy-server save guard.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * CwmmediafileModel::save() must refuse pointing a mediafile at a legacy-type
 * server unless that is already the record's own stored server -- the
 * runway from CwmserverMigrationHelper that keeps already-migrated legacy
 * servers working indefinitely while discouraging new use, applied
 * everywhere save() is reachable (admin form and API alike), not just the
 * new-server type picker that already hid the type from creation.
 *
 * @since  10.7.3
 */
#[CoversClass(CwmmediafileModel::class)]
class CwmmediafileLegacyServerGuardTest extends IntegrationTestCase
{
    /**
     * The database. Each test runs inside a transaction, but an asset-tracked save
     * can commit it, so the rows a test creates are deleted by id as well.
     *
     * @var    DatabaseDriver|null
     * @since  10.7.3
     */
    private ?DatabaseDriver $db = null;

    /**
     * The identity before the test switched to a Super User.
     *
     * @var    mixed
     * @since  10.7.3
     */
    private mixed $savedIdentity = null;

    /**
     * The media files a test created, deleted afterwards.
     *
     * @var    int[]
     * @since  10.7.3
     */
    private array $createdMediaFiles = [];

    /**
     * The servers a test created, deleted afterwards.
     *
     * @var    int[]
     * @since  10.7.3
     */
    private array $createdServers = [];

    /**
     * PluginHelper's static plugin cache, which setUp primes empty and tearDown
     * resets.
     *
     * @var    \ReflectionProperty|null
     * @since  10.7.3
     */
    private ?\ReflectionProperty $pluginCacheProperty = null;

    /**
     * Skip without a test database or a Super User. Otherwise act as that Super
     * User, open a transaction, register the real Proclaim component, and keep
     * content plugins from booting.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $app                 = Factory::getApplication();
        $this->savedIdentity = $app->getIdentity();

        $db        = Factory::getContainer()->get(DatabaseDriver::class);
        $superUser = (int) $db->setQuery(
            'SELECT ' . $db->quoteName('user_id') . ' FROM ' . $db->quoteName('#__user_usergroup_map')
            . ' WHERE ' . $db->quoteName('group_id') . ' = 8',
            0,
            1
        )->loadResult();

        if ($superUser === 0) {
            $this->markTestSkipped('No Super User available to authorise the save');
        }

        $app->loadIdentity(User::getInstance($superUser));

        $this->db = $db;
        $this->db->transactionStart(true);

        $this->registerProclaimComponent();
        $this->suppressContentPlugins();
    }

    /**
     * AdminModel::save() fires PluginHelper::importPlugin('content') for the
     * onContentBeforeSave/onContentAfterSave events. PluginHelper::load()
     * then tries to boot every published content-group plugin row from the
     * real dev database (Contact, EmailCloak, Fields, Finder, ...) through
     * the same discovery path bootComponent() uses -- which this bare
     * harness cannot resolve for core plugins any more than it could for
     * Proclaim's own component. Rather than touch the dev database's
     * #__extensions rows (even inside a transaction), prime PluginHelper's
     * own static cache empty via reflection so load() returns before the
     * DB query runs at all. No plugin side effects are under test here.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    private function suppressContentPlugins(): void
    {
        $this->pluginCacheProperty = new \ReflectionProperty(PluginHelper::class, 'plugins');
        $this->pluginCacheProperty->setValue(null, []);
    }

    /**
     * save() calls Factory::getApplication()->bootComponent('com_proclaim')
     * internally (to load a Cwmserver table) -- but this bare PHPUnit
     * harness never discovers admin/services/provider.php by the normal
     * JPATH_ADMINISTRATOR lookup, so bootComponent() falls back to a bare
     * LegacyComponent with no MVCFactory and createTable() returns false.
     * ExtensionHelper::$extensions is the same public static cache
     * bootComponent()'s own resolution checks first -- priming it here with
     * a real, provider-built component makes every subsequent bootComponent
     * ('com_proclaim') call in this test, including the one inside save(),
     * return the genuine article instead.
     *
     * Always overwrites rather than checking "already set": PHPUnit runs the
     * whole suite in one process, and an earlier, unrelated test reaching
     * bootComponent('com_proclaim') naturally (getting the broken Legacy-
     * Component fallback) caches that bad value here first -- a guard that
     * trusted "already present" would keep it instead of fixing it.
     *
     * @return  void
     *
     * @since   10.7.3
     */
    private function registerProclaimComponent(): void
    {
        $container = Factory::getContainer()->createChild();

        /** @var ServiceProviderInterface $provider */
        $provider = require \dirname(__DIR__, 4) . '/admin/services/provider.php';
        $provider->register($container);

        ExtensionHelper::$extensions[ComponentInterface::class]['proclaim'] = $container->get(ComponentInterface::class);
    }

    /**
     * Delete the media files and servers the test created, roll back, restore the
     * identity, and reset PluginHelper's plugin cache.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                foreach ($this->createdMediaFiles as $id) {
                    $this->db->setQuery(
                        'DELETE FROM ' . $this->db->quoteName('#__bsms_mediafiles')
                        . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
                    )->execute();
                }

                foreach ($this->createdServers as $id) {
                    $this->db->setQuery(
                        'DELETE FROM ' . $this->db->quoteName('#__bsms_servers')
                        . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
                    )->execute();
                }
            } catch (\Throwable) {
                // Best effort; the assertions have already run.
            }

            $this->createdMediaFiles = [];
            $this->createdServers    = [];

            try {
                $this->db->transactionRollback(true);
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }
        }

        Factory::getApplication()->loadIdentity($this->savedIdentity);

        if ($this->pluginCacheProperty !== null) {
            $this->pluginCacheProperty->setValue(null, null);
            $this->pluginCacheProperty = null;
        }

        parent::tearDown();
    }

    /**
     * A new media file cannot be pointed at a legacy server.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    #[TestDox('A new media file cannot be pointed at a legacy server')]
    public function testNewRecordOnLegacyServerIsRefused(): void
    {
        $legacyId = $this->insertServer('legacy');

        $model = $this->createModel();

        $this->assertFalse($model->save($this->baseData($legacyId)));
        $this->assertNotEmpty($model->getError());
    }

    /**
     * Save as Copy of a legacy-attached record is refused the same way.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    #[TestDox('Save as Copy of a legacy-attached record is refused the same way')]
    public function testSaveAsCopyOfLegacyAttachedRecordIsRefused(): void
    {
        // Save as Copy always submits id=0 -- mechanically identical to the
        // plain new-record case above, but pinned by name so the UI action
        // is not a surprise to whoever next touches this.
        $legacyId = $this->insertServer('legacy');
        $this->insertMediaFile($legacyId);

        $model = $this->createModel();

        $this->assertFalse($model->save($this->baseData($legacyId, 0)));
    }

    /**
     * Reassigning an existing record from a modern server to a legacy one is refused.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    #[TestDox('Reassigning an existing record from a modern server to a legacy one is refused')]
    public function testReassigningToLegacyIsRefused(): void
    {
        $modernId = $this->insertServer('local');
        $legacyId = $this->insertServer('legacy');
        $mediaId  = $this->insertMediaFile($modernId);

        $model = $this->createModel();

        $this->assertFalse($model->save($this->baseData($legacyId, $mediaId)));
    }

    /**
     * An already-legacy-attached record re-saved unchanged keeps working.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    #[TestDox('An already-legacy-attached record re-saved unchanged keeps working')]
    public function testExistingLegacyRecordUnchangedIsAllowed(): void
    {
        $legacyId = $this->insertServer('legacy');
        $mediaId  = $this->insertMediaFile($legacyId);

        $model = $this->createModel();

        $this->assertTrue($model->save($this->baseData($legacyId, $mediaId)));
    }

    /**
     * An API PATCH carrying the row's own stored server_id, as the core backfill
     * produces, keeps working.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    #[TestDox('An API PATCH carrying the row\'s own stored server_id -- as the core backfill produces -- keeps working')]
    public function testBackfilledServerIdOnExistingLegacyRecordIsAllowed(): void
    {
        // The core ApiController backfills every unset column from the
        // stored row before save() ever runs, so from save()'s own
        // perspective a PATCH that omits server_id is indistinguishable
        // from this -- asserted under its own name because it is the real
        // call shape a PATCH produces, not a contrived one.
        $legacyId = $this->insertServer('legacy');
        $mediaId  = $this->insertMediaFile($legacyId);

        $model = $this->createModel();

        $this->assertTrue($model->save($this->baseData($legacyId, $mediaId)));
    }

    /**
     * Migrating an existing record from legacy to a modern server is allowed.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    #[TestDox('Migrating an existing record from legacy to a modern server is allowed')]
    public function testMigratingAwayFromLegacyIsAllowed(): void
    {
        $legacyId = $this->insertServer('legacy');
        $modernId = $this->insertServer('local');
        $mediaId  = $this->insertMediaFile($legacyId);

        $model = $this->createModel();

        $this->assertTrue($model->save($this->baseData($modernId, $mediaId)));
    }

    /**
     * A trusted internal caller can opt in to create legacy-server media directly.
     *
     * @return  void
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    #[TestDox('A trusted internal caller can opt in to create legacy-server media directly')]
    public function testAllowLegacyServerParameterBypassesTheGuard(): void
    {
        // The demo-content importer deliberately seeds legacy-server media
        // (the only addon verified side-effect-free on save) -- this is its
        // escape hatch. $allowLegacyServer is a method parameter, never a
        // $data key, so nothing reachable from form or API input can set it.
        $legacyId = $this->insertServer('legacy');

        $model = $this->createModel();

        $this->assertTrue($model->save($this->baseData($legacyId), true));

        // This save() creates a brand-new row via CwmmediafileTable::store(),
        // which is asset-tracked -- Joomla's asset machinery takes a table
        // lock, and MySQL implicitly commits the whole transaction (every
        // savepoint included) the moment that happens. The transactionStart
        // (true)/rollback(true) pair in setUp()/tearDown() cannot undo this
        // row; only tracking its id here and deleting it explicitly can. See
        // the sibling tests' use of insertMediaFile() (which tracks on
        // insert) for the same reasoning -- this is the one test that
        // creates its row through save() instead, and had been missing it.
        $newId = (int) $model->getState($model->getName() . '.id');
        $this->assertGreaterThan(0, $newId, 'save() must report the new record id');
        $this->createdMediaFiles[] = $newId;
    }

    /**
     * Insert a published server of a given type.
     *
     * @param   string  $type  The server's addon type.
     *
     * @return  int  The new server id.
     *
     * @since   10.7.3
     */
    private function insertServer(string $type): int
    {
        $db = $this->db;
        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__bsms_servers')
            . ' (' . $db->quoteName('server_name') . ', ' . $db->quoteName('published') . ', '
            . $db->quoteName('type') . ', ' . $db->quoteName('params') . ', ' . $db->quoteName('media') . ')'
            . ' VALUES (' . $db->quote('Legacy guard fixture') . ', 1, '
            . $db->quote($type) . ", '', '')"
        )->execute();

        $id                       = (int) $db->insertid();
        $this->createdServers[]   = $id;

        return $id;
    }

    /**
     * Insert a published media file on a server.
     *
     * @param   int  $serverId  The server to attach the fixture row to.
     *
     * @return  int  The new media file id.
     *
     * @since   10.7.3
     */
    private function insertMediaFile(int $serverId): int
    {
        $db = $this->db;
        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__bsms_mediafiles')
            . ' (' . $db->quoteName('server_id') . ', ' . $db->quoteName('published') . ', '
            . $db->quoteName('metadata') . ', ' . $db->quoteName('language') . ')'
            . ' VALUES (' . $serverId . ", 1, '', " . $db->quote('*') . ')'
        )->execute();

        $id                        = (int) $db->insertid();
        $this->createdMediaFiles[] = $id;

        return $id;
    }

    /**
     * The data save() receives for a media file on a server.
     *
     * @param   int  $serverId  The server_id to submit.
     * @param   int  $id        0 for a new record, otherwise the existing record's id.
     *
     * @return  array  The data to save
     *
     * @since   10.7.3
     */
    private function baseData(int $serverId, int $id = 0): array
    {
        return [
            'id'         => $id,
            'study_id'   => 0,
            'server_id'  => $serverId,
            'podcast_id' => [],
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
            'params'     => ['filename' => 'https://example.test/fixture.mp4'],
        ];
    }

    /**
     * A CwmmediafileModel built through the MVC factory, ignoring the request.
     *
     * @return  CwmmediafileModel  The model under test
     *
     * @since   10.7.3
     * @throws  \Exception
     */
    private function createModel(): CwmmediafileModel
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var CwmmediafileModel $model */
        $model = $factory->createModel('Cwmmediafile', 'Administrator', ['ignore_request' => true]);

        $this->assertInstanceOf(CwmmediafileModel::class, $model);

        return $model;
    }
}

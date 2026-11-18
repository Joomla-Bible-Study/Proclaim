<?php

/**
 * A model asked for a record that does not exist returns false, not a TypeError.
 *
 * AdminModel::getItem() returns false for a missing record. The media file and
 * server models cached that in an `object`-typed property, so the assignment
 * itself threw and the API answered 500 where JsonApiView would have given 404.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since __DEPLOY_VERSION__
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use CWM\Component\Proclaim\Administrator\Model\CwmserverModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * @since __DEPLOY_VERSION__
 */
#[CoversClass(CwmmediafileModel::class)]
#[CoversClass(CwmserverModel::class)]
class CwmMissingItemTest extends IntegrationTestCase
{
    /**
     * @var  mixed  The request id before the test changed it
     * @since __DEPLOY_VERSION__
     */
    private mixed $savedId = null;

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

        $this->savedId = Factory::getApplication()->getInput()->get('id');
    }

    /**
     * @return  void
     * @since __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        Factory::getApplication()->getInput()->set('id', $this->savedId);

        parent::tearDown();
    }

    /**
     * An id no row in the table has.
     *
     * @param   string  $table  Table name
     *
     * @return  int
     * @since __DEPLOY_VERSION__
     */
    private function missingId(string $table): int
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        return (int) $db->setQuery('SELECT MAX(' . $db->quoteName('id') . ') FROM ' . $db->quoteName($table))->loadResult() + 1000;
    }

    /**
     * @param   string  $name  Model name
     *
     * @return  object
     * @since __DEPLOY_VERSION__
     */
    private function model(string $name): object
    {
        $container = Factory::getContainer();
        $factory   = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        return $factory->createModel($name, 'Administrator', ['ignore_request' => true]);
    }

    #[TestDox('the media file model returns false for a missing id')]
    public function testMediafileModelReturnsFalseForAMissingId(): void
    {
        // getItem() reads the id from the request, not from its argument.
        Factory::getApplication()->getInput()->set('id', $this->missingId('#__bsms_mediafiles'));

        /** @var CwmmediafileModel $model */
        $model = $this->model('Cwmmediafile');

        $this->assertFalse($model->getItem());
    }

    #[TestDox('the server model returns false for a missing id')]
    public function testServerModelReturnsFalseForAMissingId(): void
    {
        /** @var CwmserverModel $model */
        $model = $this->model('Cwmserver');

        $this->assertFalse($model->getItem($this->missingId('#__bsms_servers')));
    }

    #[TestDox('a media file pointing at a deleted server has no server form')]
    public function testMediafileWithADeletedServerHasNoServerForm(): void
    {
        // getMediaForm() boots com_proclaim to reach the server model; the harness
        // does not register the component's provider on its own.
        $container = Factory::getContainer()->createChild();

        /** @var ServiceProviderInterface $provider */
        $provider = require \dirname(__DIR__, 4) . '/admin/services/provider.php';
        $provider->register($container);
        ExtensionHelper::$extensions[ComponentInterface::class]['proclaim'] = $container->get(ComponentInterface::class);

        /** @var CwmmediafileModel $model */
        $model = $this->model('Cwmmediafile');

        // getMediaForm() reads the server id from the item getItem() cached.
        (new \ReflectionProperty(CwmmediafileModel::class, 'data'))
            ->setValue($model, (object) ['server_id' => $this->missingId('#__bsms_servers')]);

        $this->assertNull($model->getMediaForm());
    }
}

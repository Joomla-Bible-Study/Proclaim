<?php

/**
 * Integration tests for the media-file display batch action.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Field\MediaFileImagesField;
use CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Regression coverage for #1627.
 *
 * batchMediatype() wrote `(int) $value` into media_image, a param that holds an
 * image path. The batch widget had no options, which was the only thing keeping
 * that unreachable -- populating it would have given administrators a one-click
 * way to replace every selected file's image path with an integer.
 *
 * The batch now carries a whole display configuration, the same payload the
 * Image Tools screen applies, scoped to the selected rows.
 *
 * @since  __DEPLOY_VERSION__
 */
#[CoversClass(CwmmediafileModel::class)]
class CwmbatchMediatypeTest extends IntegrationTestCase
{
    /**
     * @var  DatabaseDriver|null
     */
    private ?DatabaseDriver $db = null;

    /**
     * @var  mixed
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * @var  int[]
     */
    private array $created = [];

    /**
     * @var  mixed
     */
    private mixed $savedIdentity = null;

    /**
     * @return  void
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        // batchMediatype() checks core.edit per row, and the console harness has
        // no identity -- without one every test would error before reaching the
        // behaviour under test.
        $app                 = Factory::getApplication();
        $this->savedIdentity = $app->getIdentity();

        // core.edit has to actually pass, so borrow a Super User rather than
        // inventing an id with no groups.
        $db        = Factory::getContainer()->get(DatabaseDriver::class);
        $superUser = (int) $db->setQuery(
            'SELECT ' . $db->quoteName('user_id') . ' FROM ' . $db->quoteName('#__user_usergroup_map')
            . ' WHERE ' . $db->quoteName('group_id') . ' = 8',
            0,
            1
        )->loadResult();

        if ($superUser === 0) {
            $this->markTestSkipped('No Super User available to authorise the batch');
        }

        $app->loadIdentity(User::getInstance($superUser));

        $this->db = Factory::getContainer()->get(DatabaseDriver::class);
        $this->db->transactionStart();
    }

    /**
     * @return  void
     */
    protected function tearDown(): void
    {
        if ($this->db !== null) {
            try {
                $this->db->transactionRollback();
            } catch (\Throwable) {
                // Connection may have been lost -- nothing to roll back.
            }

            try {
                foreach ($this->created as $id) {
                    $this->db->setQuery(
                        'DELETE FROM ' . $this->db->quoteName('#__bsms_mediafiles')
                        . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
                    )->execute();
                }
            } catch (\Throwable) {
                // Best effort; the assertions have already run.
            }

            $this->created = [];
        }

        Factory::getApplication()->loadIdentity($this->savedIdentity);
        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    #[TestDox('A batch writes the whole display configuration, not an integer into the image path')]
    public function testBatchAppliesFullDisplayConfiguration(): void
    {
        $target = $this->insertMediafile([
            'media_image'           => 'images/biblestudy/streamingvideo24.png',
            'media_use_button_icon' => '0',
        ]);

        $value = json_encode([
            'media_use_button_icon' => '1',
            'media_button_type'     => 'btn-primary',
            'media_button_text'     => 'Watch',
            'media_button_color'    => '#ffffff',
            'media_icon_type'       => '',
            'media_custom_icon'     => '',
            'media_image'           => '',
        ]);

        $this->runBatch($value, [$target]);

        $params = $this->loadParams($target);

        $this->assertSame('1', (string) $params->get('media_use_button_icon'));
        $this->assertSame('btn-primary', (string) $params->get('media_button_type'));
        $this->assertSame('Watch', (string) $params->get('media_button_text'));
        $this->assertSame('#ffffff', (string) $params->get('media_button_color'));

        // The bug: media_image previously became an integer.
        $this->assertSame('', (string) $params->get('media_image'), 'media_image must hold a path, never a number');
    }

    #[TestDox('A batch leaves media files outside the selection untouched')]
    public function testUnselectedRowsAreUntouched(): void
    {
        $original = [
            'media_image'           => 'images/biblestudy/untouched.png',
            'media_use_button_icon' => '0',
        ];

        $target     = $this->insertMediafile($original);
        $bystander  = $this->insertMediafile($original);
        $beforeJson = $this->loadRawParams($bystander);

        $value = json_encode([
            'media_use_button_icon' => '3',
            'media_button_type'     => '',
            'media_button_text'     => '',
            'media_button_color'    => '',
            'media_icon_type'       => 'fa-solid fa-video',
            'media_custom_icon'     => '',
            'media_image'           => '',
        ]);

        $this->runBatch($value, [$target]);

        $this->assertSame(
            '3',
            (string) $this->loadParams($target)->get('media_use_button_icon'),
            'The selected row should have been updated'
        );
        $this->assertSame(
            $beforeJson,
            $this->loadRawParams($bystander),
            'A media file outside the selection must be byte-identical afterwards'
        );
    }

    #[TestDox('A batch value carrying unknown keys writes only display params')]
    public function testUnknownKeysAreIgnored(): void
    {
        $target = $this->insertMediafile(['media_image' => 'images/biblestudy/keep.png']);

        // $value comes from the request, so a hand-edited payload can name
        // anything. Only the display keys may reach the params.
        $value = json_encode([
            'media_use_button_icon' => '3',
            'media_icon_type'       => 'fa-solid fa-video',
            'filename'              => 'https://evil.example/pwned.mp4',
            'player'                => '9',
            'injected'              => '<script>alert(1)</script>',
        ]);

        $this->runBatch($value, [$target]);

        $params = $this->loadParams($target);

        $this->assertSame('3', (string) $params->get('media_use_button_icon'));
        $this->assertNull($params->get('injected'), 'An unknown key must not be written');
        $this->assertNotSame(
            'https://evil.example/pwned.mp4',
            (string) $params->get('filename'),
            'A known-but-not-display param must not be overwritten'
        );
    }

    #[TestDox('A malformed batch value is refused rather than written')]
    public function testMalformedValueIsRefused(): void
    {
        $target     = $this->insertMediafile(['media_image' => 'images/biblestudy/intact.png']);
        $beforeJson = $this->loadRawParams($target);

        $model = $this->createModel();
        $ok    = $this->invokeBatch($model, 'not json at all', [$target]);

        $this->assertFalse($ok, 'A value that is not a display configuration must be refused');
        $this->assertSame($beforeJson, $this->loadRawParams($target), 'Nothing may be written on refusal');
    }

    #[TestDox('The batch widget offers the same configurations as the Image Tools field')]
    public function testWidgetOptionsMatchTheImageToolsField(): void
    {
        $this->insertMediafile([
            'media_image'           => 'images/biblestudy/widget-fixture.png',
            'media_use_button_icon' => '0',
        ]);

        $options = MediaFileImagesField::buildDisplayOptions(false);

        $this->assertNotEmpty($options, 'The widget must offer the configurations in use');

        foreach ($options as $option) {
            $decoded = json_decode($option->value, true);

            $this->assertIsArray($decoded, 'Every option value must be a display configuration');

            foreach (MediaFileImagesField::DISPLAY_KEYS as $key) {
                $this->assertArrayHasKey(
                    $key,
                    $decoded,
                    $key . ' must be carried, or applying the option changes something it did not name'
                );
            }
        }
    }

    /**
     * @param   string  $value  JSON display configuration.
     * @param   int[]   $pks    Selected media file IDs.
     *
     * @return  void
     */
    private function runBatch(string $value, array $pks): void
    {
        $this->assertTrue($this->invokeBatch($this->createModel(), $value, $pks));
    }

    /**
     * @param   CwmmediafileModel  $model  Model under test.
     * @param   string             $value  JSON display configuration.
     * @param   int[]              $pks    Selected media file IDs.
     *
     * @return  bool
     */
    private function invokeBatch(CwmmediafileModel $model, string $value, array $pks): bool
    {
        $contexts = [];

        foreach ($pks as $pk) {
            $contexts[$pk] = 'com_proclaim.mediafile.' . $pk;
        }

        $method = new \ReflectionMethod(CwmmediafileModel::class, 'batchMediatype');

        return (bool) $method->invoke($model, $value, $pks, $contexts);
    }

    /**
     * @return  CwmmediafileModel
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

    /**
     * @param   int  $id  Media file ID.
     *
     * @return  Registry
     */
    private function loadParams(int $id): Registry
    {
        $reg = new Registry();
        $reg->loadString($this->loadRawParams($id));

        return $reg;
    }

    /**
     * @param   int  $id  Media file ID.
     *
     * @return  string  The raw params JSON.
     */
    private function loadRawParams(int $id): string
    {
        return (string) $this->db->setQuery(
            'SELECT ' . $this->db->quoteName('params') . ' FROM ' . $this->db->quoteName('#__bsms_mediafiles')
            . ' WHERE ' . $this->db->quoteName('id') . ' = ' . $id
        )->loadResult();
    }

    /**
     * @param   array  $params  Params to seed.
     *
     * @return  int  Media file ID.
     */
    private function insertMediafile(array $params): int
    {
        $reg = new Registry();

        foreach ($params + ['filename' => 'https://example.test/fixture.mp4', 'player' => '1'] as $k => $v) {
            $reg->set($k, $v);
        }

        $row = (object) [
            'study_id'  => 0,
            'server_id' => 0,
            'published' => 1,
            'access'    => 1,
            'language'  => '*',
            'params'    => $reg->toString(),
            'mime_type' => 'video/mp4',
            'metadata'  => '',
        ];

        $this->db->insertObject('#__bsms_mediafiles', $row);

        $id              = (int) $this->db->insertid();
        $this->created[] = $id;

        return $id;
    }
}

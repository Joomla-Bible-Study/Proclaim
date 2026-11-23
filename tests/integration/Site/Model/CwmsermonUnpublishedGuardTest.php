<?php

/**
 * Asking for a sermon whose published state does not match the requested filter
 * is meant to queue "item not published" and return nothing. The model used
 * Text::_() without importing Text, so PHP looked for the class in the model's
 * own namespace and the visitor got a 500 instead of the message. php -l and the
 * suite both passed, because the name is only resolved when the branch runs.
 *
 * @package    Proclaim.IntegrationTest
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 *
 * @since 10.7.1
 */

namespace CWM\Component\Proclaim\Tests\Integration\Site\Model;

use CWM\Component\Proclaim\Site\Model\CwmsermonModel;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A sermon that fails the published filter queues a message and returns
 * nothing, rather than throwing.
 *
 * @since  10.7.1
 */
class CwmsermonUnpublishedGuardTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseInterface|null
     * @since  10.7.1
     */
    private ?DatabaseInterface $db = null;

    /**
     * Factory's language before the test replaced it to silence date warnings.
     *
     * @var    mixed
     * @since  10.7.1
     */
    private mixed $previousFactoryLanguage = null;

    /**
     * The identity before the test switched to a guest.
     *
     * @var    User|null
     * @since  10.7.1
     */
    private ?User $savedIdentity = null;

    /**
     * Skip without a test database. Otherwise silence date language warnings, act as a guest, and open a transaction.
     *
     * @return  void
     *
     * @since   10.7.1
     * @throws  \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        $this->previousFactoryLanguage = $this->silenceDateLanguageWarnings();

        // The access filter reads the current identity; the issue is about what
        // a guest sees, so stand one in and restore it afterwards.
        $app                 = Factory::getApplication();
        $this->savedIdentity = $app->getIdentity();
        $app->loadIdentity(new User());

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);
    }

    /**
     * Roll back, and restore the identity and language.
     *
     * @return  void
     *
     * @since   10.7.1
     * @throws  \Exception
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

        if ($this->savedIdentity !== null) {
            Factory::getApplication()->loadIdentity($this->savedIdentity);
        }

        Factory::$language = $this->previousFactoryLanguage;

        parent::tearDown();
    }

    /**
     * A sermon that fails the published filter queues a message instead of throwing.
     *
     * @return  void
     *
     * @since   10.7.1
     * @throws  \Exception
     */
    #[TestDox('A sermon that fails the published filter queues a message instead of throwing')]
    public function testMismatchedPublishedStateQueuesAMessage(): void
    {
        $studyId = $this->seedStudy();

        $model = $this->createModel();
        $model->setState('study.id', $studyId);
        // Only a numeric filter.published narrows the SQL, so leaving it unset
        // and setting the archived filter lets the row load and then fail the
        // PHP state check — the branch that used the unimported Text.
        $model->setState('filter.archived', 2);

        // ⚠️ enqueueMessage() ignores a message already in the queue, so a
        // count comparison is order-dependent — drain first and look for the
        // message itself.
        Factory::getApplication()->getMessageQueue(true);

        // Before the fix this threw Error: Class "...Site\Model\Text" not found.
        $item = $model->getItem();

        $this->assertNull($item, 'A sermon failing the published filter must not be returned');

        // Not matched by text: the model translates with the site language
        // loaded, a test does not, so the two renderings differ.
        $this->assertNotEmpty(
            Factory::getApplication()->getMessageQueue(),
            'The visitor should get a queued message rather than an exception'
        );
    }

    /**
     * Insert a published study.
     *
     * @return  int  Seeded study id.
     *
     * @since   10.7.1
     */
    private function seedStudy(): int
    {
        $row = (object) [
            'studytitle' => 'zz guard study ' . uniqid('', true),
            'alias'      => 'zz-guard-study-' . uniqid('', true),
            'studydate'  => '2026-01-15 10:00:00',
            'booknumber' => 101,
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
            'ordering'   => 0,
            'params'     => '{}',
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }

    /**
     * A site CwmsermonModel built through the MVC factory, ignoring the request.
     *
     * @return  CwmsermonModel  The model under test
     *
     * @since   10.7.1
     * @throws  \Exception
     */
    private function createModel(): CwmsermonModel
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var CwmsermonModel $model */
        $model = $factory->createModel('Cwmsermon', 'Site', ['ignore_request' => true]);

        $this->assertInstanceOf(CwmsermonModel::class, $model);

        return $model;
    }
}

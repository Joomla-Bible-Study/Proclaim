<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * An alias the message and series models build from a title takes the next
 * free -2, -3, ... form, and says so.
 *
 * prepareTable() is called directly: it only reads, so the test stays inside
 * a transaction that is rolled back, where a full save() would create an
 * asset and commit.
 *
 * @since  __DEPLOY_VERSION__
 */
class GeneratedAliasTest extends IntegrationTestCase
{
    private ?DatabaseInterface $db = null;

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

        Factory::getApplication()->getLanguage()->load('com_proclaim', \dirname(__DIR__, 4) . '/admin', 'en-GB', true);

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);
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
     * @return  array<string, array{string, string, string}>
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function models(): array
    {
        return [
            'message' => ['Cwmmessage', '#__bsms_studies', 'studytitle'],
            'series'  => ['Cwmserie', '#__bsms_series', 'series_text'],
        ];
    }

    /**
     * @param   string  $model       Model name
     * @param   string  $table       Its table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('models')]
    #[TestDox('A $_dataName alias built from a title another row already uses gets -2, with a warning')]
    public function testCollidingGeneratedAliasIsNumbered(string $model, string $table, string $titleField): void
    {
        $base = $this->uniqueAlias('generated');
        $this->seed($table, $titleField, $base);

        [$alias, $queued] = $this->prepare($model, $titleField, str_replace('-', ' ', $base));

        $this->assertSame($base . '-2', $alias);
        $this->assertCount(1, $queued);
        $this->assertSame('warning', $queued[0][0]);
        $this->assertStringContainsString(
            'already uses the alias ' . $base . ', so this one was saved as ' . $base . '-2',
            $queued[0][1]
        );
    }

    /**
     * @param   string  $model       Model name
     * @param   string  $table       Its table
     * @param   string  $titleField  Its title column
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('models')]
    #[TestDox('A $_dataName alias built from a free title is the plain slug, without a warning')]
    public function testFreeGeneratedAliasIsTheSlug(string $model, string $table, string $titleField): void
    {
        // A longer alias that only starts with the slug does not make it taken.
        $base = $this->uniqueAlias('generated-free');
        $this->seed($table, $titleField, $base . '-2');

        [$alias, $queued] = $this->prepare($model, $titleField, str_replace('-', ' ', $base));

        $this->assertSame($base, $alias);
        $this->assertSame([], $queued);
    }

    /**
     * Run a model's prepareTable() on a new row with no alias, under a stub
     * application that records what this one call queues.
     *
     * @param   string  $model       Model name
     * @param   string  $titleField  The title column
     * @param   string  $title       The new row's title
     *
     * @return  array{0: string, 1: list<array{0: string, 1: string}>}  The alias, and each queued type and message
     *
     * @since   __DEPLOY_VERSION__
     */
    private function prepare(string $model, string $titleField, string $title): array
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var AdminModel $instance */
        $instance = $factory->createModel($model, 'Administrator', ['ignore_request' => true]);
        $table    = $instance->getTable();

        $table->{$titleField} = $title;
        $table->alias         = '';

        $queued = [];
        $app    = $this->createStub(CMSApplicationInterface::class);
        $app->method('getIdentity')->willReturn(new User());
        $app->method('enqueueMessage')->willReturnCallback(
            static function (string $msg, string $type = 'info') use (&$queued): void {
                $queued[] = [$type, $msg];
            }
        );

        $this->withApplication(
            $app,
            static fn () => (new \ReflectionMethod($instance, 'prepareTable'))->invoke($instance, $table)
        );

        return [$table->alias, $queued];
    }

    /**
     * @param   string  $table       The table
     * @param   string  $titleField  Its title column
     * @param   string  $alias       The alias the row holds
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seed(string $table, string $titleField, string $alias): void
    {
        $row = (object) [$titleField => 'Zz existing', 'alias' => $alias, 'published' => 1, 'access' => 1, 'language' => '*'];

        $this->db->insertObject($table, $row);
    }
}

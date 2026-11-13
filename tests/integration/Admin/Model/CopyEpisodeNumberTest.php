<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Model;

use CWM\Component\Proclaim\Administrator\Helper\CwmepisodenumberHelper;
use CWM\Component\Proclaim\Administrator\Table\CwmmessageTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * A Save as Copy of a message numbered in a series saves as the next episode,
 * and an episode number the user typed is still checked.
 *
 * Runs the steps of the copy that only read: the number the copy keeps, then
 * prepareTable() and check() on the new row, inside a transaction that is
 * rolled back. A full save() would create an asset and commit.
 *
 * @since  __DEPLOY_VERSION__
 */
class CopyEpisodeNumberTest extends IntegrationTestCase
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

        // The refusal is a translated string; without the component's language
        // it would read as its bare key.
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
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A copy that keeps its series and episode number is numbered next in the series, and passes check()')]
    public function testUnchangedCopyIsNumberedNext(): void
    {
        $series = $this->seedSeries();
        $this->seedMessage($series, '4');
        $this->seedMessage($series, '7');

        $copy = $this->prepareCopy($series, CwmepisodenumberHelper::forCopy('7', $series, '7', $series));

        $this->assertSame('8', (string) $copy->studynumber);
        $this->assertTrue($copy->check());
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('An episode number the user typed that another message in the series holds is still refused')]
    public function testTypedNumberHeldElsewhereIsRefused(): void
    {
        $series = $this->seedSeries();
        $this->seedMessage($series, '4');
        $this->seedMessage($series, '7');

        $copy = $this->prepareCopy($series, CwmepisodenumberHelper::forCopy('4', $series, '7', $series));

        $this->assertSame('4', (string) $copy->studynumber);
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('is already used by');

        $copy->check();
    }

    /**
     * Run the message model's prepareTable() on a new row in a series, as a
     * copy is prepared, under a stub application.
     *
     * @param   int     $series  The copy's series
     * @param   string  $number  The episode number the copy keeps
     *
     * @return  CwmmessageTable
     *
     * @since   __DEPLOY_VERSION__
     */
    private function prepareCopy(int $series, string $number): CwmmessageTable
    {
        $container = Factory::getContainer();

        $factory = new MVCFactory('CWM\\Component\\Proclaim');
        $factory->setDatabase($container->get(DatabaseInterface::class));
        $factory->setDispatcher($container->get(DispatcherInterface::class));
        $factory->setFormFactory($container->get(FormFactoryInterface::class));

        /** @var AdminModel $model */
        $model = $factory->createModel('Cwmmessage', 'Administrator', ['ignore_request' => true]);

        /** @var CwmmessageTable $table */
        $table              = $model->getTable();
        $table->studytitle  = $this->uniqueAlias('copied-episode');
        $table->alias       = '';
        $table->series_id   = $series;
        $table->studynumber = $number;

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getIdentity')->willReturn(new User());

        $this->withApplication(
            $app,
            static fn () => (new \ReflectionMethod($model, 'prepareTable'))->invoke($model, $table)
        );

        return $table;
    }

    /**
     * @return  int  The new series id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedSeries(): int
    {
        $row = (object) [
            'series_text' => 'Zz Episode Series',
            'alias'       => $this->uniqueAlias('episode-series'),
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
        ];

        $this->db->insertObject('#__bsms_series', $row);

        return (int) $this->db->insertid();
    }

    /**
     * @param   int     $series  The message's series
     * @param   string  $number  Its episode number
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seedMessage(int $series, string $number): void
    {
        $row = (object) [
            'studytitle'  => 'Zz Episode ' . $number,
            'alias'       => $this->uniqueAlias('episode-' . $number),
            'series_id'   => $series,
            'studynumber' => $number,
            'published'   => 1,
            'access'      => 1,
            'language'    => '*',
        ];

        $this->db->insertObject('#__bsms_studies', $row);
    }
}

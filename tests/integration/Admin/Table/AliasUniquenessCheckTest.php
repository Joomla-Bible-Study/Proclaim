<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Table;

use CWM\Component\Proclaim\Administrator\Helper\CwmlocationHelper;
use CWM\Component\Proclaim\Administrator\Table\CwmmessageTable;
use CWM\Component\Proclaim\Administrator\Table\CwmserieTable;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * check() on the message and series tables refuses an alias another row holds.
 *
 * The refusal names that row and links to it only in the administrator, for a
 * user whose admin list would already show it; anyone else is told only that
 * the alias is taken.
 *
 * @since  __DEPLOY_VERSION__
 */
class AliasUniquenessCheckTest extends IntegrationTestCase
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

        // The location filter caches per user id, and the stub users have none.
        CwmlocationHelper::resetCache();

        parent::tearDown();
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A new message with an alias another message holds is refused, naming and linking that message')]
    public function testMessageAliasHeldElsewhereIsRefused(): void
    {
        $alias = $this->uniqueAlias('check-message');
        $owner = $this->seed('#__bsms_studies', ['studytitle' => 'Zz Alias Owner', 'alias' => $alias, 'published' => 1]);

        $refusal = $this->refusal($this->messageTable('Zz Alias Newcomer', $alias), $this->application('administrator', true));

        $this->assertStringContainsString('Zz Alias Owner', $refusal);
        $this->assertStringContainsString('task=cwmmessage.edit&id=' . $owner, $refusal);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('When the holder is trashed, the refusal says so')]
    public function testTrashedHolderIsNamedAsTrashed(): void
    {
        $alias = $this->uniqueAlias('check-trashed');
        $owner = $this->seed('#__bsms_studies', ['studytitle' => 'Zz Trashed Owner', 'alias' => $alias, 'published' => -2]);

        $refusal = $this->refusal($this->messageTable('Zz Alias Newcomer', $alias), $this->application('administrator', true));

        $this->assertStringContainsString('in the trash', $refusal);
        $this->assertStringContainsString('task=cwmmessage.edit&id=' . $owner, $refusal);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A holder the user cannot see in the admin list is not named, only the alias is said to be taken')]
    public function testHolderHiddenFromTheUserIsNotNamed(): void
    {
        $alias = $this->uniqueAlias('check-hidden');
        $owner = $this->seed('#__bsms_studies', ['studytitle' => 'Zz Hidden Owner', 'alias' => $alias, 'published' => 1, 'access' => 3]);

        $refusal = $this->refusal($this->messageTable('Zz Alias Newcomer', $alias), $this->application('administrator', false));

        $this->assertStringContainsString('already uses the alias', $refusal);
        $this->assertStringNotContainsString('Zz Hidden Owner', $refusal);
        $this->assertStringNotContainsString('id=' . $owner, $refusal);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Outside the administrator the holder is not named, even to a super user')]
    public function testOutsideTheAdministratorTheHolderIsNotNamed(): void
    {
        $alias = $this->uniqueAlias('check-site');
        $this->seed('#__bsms_studies', ['studytitle' => 'Zz Site Owner', 'alias' => $alias, 'published' => -2]);

        $refusal = $this->refusal($this->messageTable('Zz Alias Newcomer', $alias), $this->application('site', true));

        $this->assertStringContainsString('in the trash', $refusal);
        $this->assertStringNotContainsString('Zz Site Owner', $refusal);
        $this->assertStringNotContainsString('task=', $refusal);
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('Re-saving a message with the alias it already has passes')]
    public function testOwnAliasPasses(): void
    {
        $alias = $this->uniqueAlias('check-own');
        $id    = $this->seed('#__bsms_studies', ['studytitle' => 'Zz Own Alias', 'alias' => $alias, 'published' => 1]);

        $message     = $this->messageTable('Zz Own Alias', $alias);
        $message->id = $id;

        $this->assertTrue($message->check());
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('A new series with an alias another series holds is refused, naming and linking that series')]
    public function testSeriesAliasHeldElsewhereIsRefused(): void
    {
        $alias = $this->uniqueAlias('check-series');
        $owner = $this->seed('#__bsms_series', ['series_text' => 'Zz Series Owner', 'alias' => $alias, 'published' => 1]);

        /** @var CwmserieTable $series */
        $series              = $this->createTableInstance(CwmserieTable::class);
        $series->series_text = 'Zz Series Newcomer';
        $series->alias       = $alias;

        $refusal = $this->refusal($series, $this->application('administrator', true));

        $this->assertStringContainsString('Zz Series Owner', $refusal);
        $this->assertStringContainsString('task=cwmserie.edit&id=' . $owner, $refusal);
    }

    /**
     * The message check() refuses the save with, under a given application.
     *
     * @param   Table                    $table  The table to check
     * @param   CMSApplicationInterface  $app    The application to check it under
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function refusal(Table $table, CMSApplicationInterface $app): string
    {
        try {
            $this->withApplication($app, static fn () => $table->check());
        } catch (\UnexpectedValueException $e) {
            return $e->getMessage();
        }

        $this->fail('check() accepted an alias another row holds');
    }

    /**
     * A stub application for a client, signed in as a super user or as a user
     * holding only the Public view level.
     *
     * @param   string  $client     The client it reports being
     * @param   bool    $superUser  Whether its user passes every permission check
     *
     * @return  CMSApplicationInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function application(string $client, bool $superUser): CMSApplicationInterface
    {
        $user = $this->createStub(User::class);
        $user->method('authorise')->willReturn($superUser);
        $user->method('getAuthorisedViewLevels')->willReturn([1]);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('isClient')->willReturnCallback(static fn (string $identifier): bool => $identifier === $client);
        $app->method('getIdentity')->willReturn($user);

        return $app;
    }

    /**
     * @param   string  $title  Message title
     * @param   string  $alias  Message alias
     *
     * @return  CwmmessageTable
     *
     * @since   __DEPLOY_VERSION__
     */
    private function messageTable(string $title, string $alias): CwmmessageTable
    {
        /** @var CwmmessageTable $table */
        $table             = $this->createTableInstance(CwmmessageTable::class);
        $table->studytitle = $title;
        $table->alias      = $alias;

        return $table;
    }

    /**
     * @param   string                $table   The table
     * @param   array<string, mixed>  $values  Column values; access and language default to public and all
     *
     * @return  int  The new row id
     *
     * @since   __DEPLOY_VERSION__
     */
    private function seed(string $table, array $values): int
    {
        $row = (object) ($values + ['access' => 1, 'language' => '*']);

        $this->db->insertObject($table, $row);

        return (int) $this->db->insertid();
    }
}

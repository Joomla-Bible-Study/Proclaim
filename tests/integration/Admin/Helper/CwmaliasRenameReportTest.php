<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\Cwmalias;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * What a repair that renamed messages and series tells the administrators: a
 * notice linking each renamed record, and one email to the Super Users who
 * receive system emails.
 *
 * The mailer is always a stand-in that records instead of sending.
 *
 * @since  10.7.4
 */
class CwmaliasRenameReportTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseInterface|null
     * @since  10.7.4
     */
    private ?DatabaseInterface $db = null;

    /**
     * Skip without a test database. Otherwise load the installer's language strings, which
     * the report uses, and open a transaction.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('PROCLAIM_TEST_DB_AVAILABLE') || !PROCLAIM_TEST_DB_AVAILABLE) {
            $this->markTestSkipped('Database not available for integration tests');
        }

        // The report's strings live with the installer's; without them it
        // would read as bare keys.
        Factory::getApplication()->getLanguage()->load('com_proclaim.sys', \dirname(__DIR__, 4) . '/admin', 'en-GB', true);

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
        $this->db->transactionStart(true);
    }

    /**
     * Roll back the transaction setUp() opened.
     *
     * @return  void
     *
     * @since   10.7.4
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
     * sharedRenames() keeps the message renamed from a shared alias, with its id, title,
     * date and both aliases. It drops the one that had no alias, and the empty series list.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[TestDox('Only records that shared an alias are reported, each with its title and a message\'s date')]
    public function testOnlySharedRenamesAreReported(): void
    {
        $shared = $this->seedMessage('Zz Christmas Program', '2023-12-23 10:00:00');
        $empty  = $this->seedMessage('Zz Untitled', '2024-01-07 10:00:00');

        $renames = Cwmalias::sharedRenames($this->db, [
            '#__bsms_studies' => [
                ['id' => $shared, 'from' => 'zz-christmas-program', 'to' => 'zz-christmas-program-2'],
                ['id' => $empty, 'from' => '', 'to' => 'zz-untitled'],
            ],
            '#__bsms_series' => [],
        ]);

        $this->assertSame(['#__bsms_studies'], array_keys($renames));
        $this->assertCount(1, $renames['#__bsms_studies']);

        $record = $renames['#__bsms_studies'][0];

        $this->assertSame(
            [$shared, 'Zz Christmas Program', '2023-12-23', 'zz-christmas-program', 'zz-christmas-program-2'],
            [$record->id, $record->title, $record->date, $record->from, $record->to]
        );
    }

    /**
     * announceRenames() queues one notice. It links the message's edit form, with the escaped
     * title and the date as the link text, gives both aliases, and suggests a clearer title
     * and alias.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Exception
     */
    #[TestDox('The notice links each renamed record to its edit form, its title escaped')]
    public function testNoticeLinksEachRecord(): void
    {
        $id      = $this->seedMessage('Zz Easter <Sunrise> & Song', '2024-03-31 07:00:00');
        $renames = Cwmalias::sharedRenames($this->db, [
            '#__bsms_studies' => [['id' => $id, 'from' => 'zz-easter', 'to' => 'zz-easter-2']],
        ]);

        $queued   = [];
        $language = Factory::getApplication()->getLanguage();
        $app      = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);
        $app->method('enqueueMessage')->willReturnCallback(
            static function (string $msg, string $type = 'info') use (&$queued): void {
                $queued[] = [$type, $msg];
            }
        );

        $this->withApplication($app, static fn () => Cwmalias::announceRenames($renames));

        $this->assertCount(1, $queued);
        $this->assertSame('notice', $queued[0][0]);
        $this->assertStringContainsString(
            '<a href="index.php?option=com_proclaim&task=cwmmessage.edit&id=' . $id . '">'
            . 'Zz Easter &lt;Sunrise&gt; &amp; Song, 2024-03-31</a> (zz-easter → zz-easter-2)',
            $queued[0][1]
        );
        $this->assertStringContainsString('clearer title and alias', $queued[0][1]);
    }

    /**
     * emailRenames() sends one email, in Bcc only, that includes the Super User who receives
     * system emails but not the one who opted out or the blocked one, and returns the Bcc
     * count. The body lists the record without HTML escaping, and both SMTP timeouts are 5
     * seconds.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Throwable
     */
    #[TestDox('One email goes in Bcc to the Super Users who receive system emails, naming each record, with short SMTP timeouts')]
    public function testEmailGoesToSuperUsersWhoReceiveSystemEmails(): void
    {
        $id = $this->seedMessage('Zz Christmas Program', '2023-12-23 10:00:00');
        $this->seedSuperUser('zz-wanted@example.test', 1, 0);
        $this->seedSuperUser('zz-opted-out@example.test', 0, 0);
        $this->seedSuperUser('zz-blocked@example.test', 1, 1);

        $mail = $this->recordingMailer(true);
        $sent = Cwmalias::emailRenames($this->db, Cwmalias::sharedRenames($this->db, [
            '#__bsms_studies' => [['id' => $id, 'from' => 'zz-christmas-program', 'to' => 'zz-christmas-program-2']],
        ]), $this->mailerFactoryFor($mail));

        $bcc = array_column($mail->getBccAddresses(), 0);

        $this->assertContains('zz-wanted@example.test', $bcc);
        $this->assertNotContains('zz-opted-out@example.test', $bcc);
        $this->assertNotContains('zz-blocked@example.test', $bcc);
        $this->assertSame(\count($bcc), $sent);
        $this->assertSame([], $mail->getToAddresses());
        $this->assertSame(1, $mail->sends);
        $this->assertStringContainsString('messages and series with new aliases', $mail->Subject);
        $this->assertStringContainsString(
            '- Zz Christmas Program, 2023-12-23 (#' . $id . '): zz-christmas-program → zz-christmas-program-2',
            $mail->Body
        );
        $this->assertStringNotContainsString('&amp;', $mail->Body);
        $this->assertSame(5, $mail->Timeout);
        $this->assertSame(5, $mail->getSMTPInstance()->Timelimit);
    }

    /**
     * With Live Site empty the email holds no link; with it set, the email links the
     * message's administrator edit form at that address. Live Site is restored after.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Throwable
     */
    #[TestDox('From the command line the email links each record only when the site\'s Live Site is configured')]
    public function testEmailLinksNeedAKnownSiteAddress(): void
    {
        $id = $this->seedMessage('Zz Christmas Program', '2023-12-23 10:00:00');
        $this->seedSuperUser('zz-wanted@example.test', 1, 0);

        $renames = Cwmalias::sharedRenames($this->db, [
            '#__bsms_studies' => [['id' => $id, 'from' => 'zz-christmas-program', 'to' => 'zz-christmas-program-2']],
        ]);
        $config  = Factory::getContainer()->get('config');
        $saved   = $config->get('live_site', '');

        try {
            $config->set('live_site', '');
            Uri::reset();
            $unknown = $this->recordingMailer(true);
            Cwmalias::emailRenames($this->db, $renames, $this->mailerFactoryFor($unknown));

            $config->set('live_site', 'https://example.test');
            Uri::reset();
            $known = $this->recordingMailer(true);
            Cwmalias::emailRenames($this->db, $renames, $this->mailerFactoryFor($known));
        } finally {
            $config->set('live_site', $saved);
            Uri::reset();
        }

        $this->assertStringNotContainsString('://', $unknown->Body);
        $this->assertStringContainsString(
            'https://example.test/administrator/index.php?option=com_proclaim&task=cwmmessage.edit&id=' . $id,
            $known->Body
        );
    }

    /**
     * With no renames, emailRenames() returns 0 without creating a mailer.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Throwable
     */
    #[TestDox('With nothing renamed, no mailer is even created')]
    public function testNothingRenamedSendsNothing(): void
    {
        $factory = $this->createMock(MailerFactoryInterface::class);
        $factory->expects($this->never())->method('createMailer');

        $this->assertSame(0, Cwmalias::emailRenames($this->db, [], $factory));
    }

    /**
     * emailRenames() lets the RuntimeException of a mail server that can't be reached
     * through to its caller.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Throwable
     */
    #[TestDox('A mail server that fails reaches the caller, for it to log where it logs')]
    public function testFailedSendReachesTheCaller(): void
    {
        $id = $this->seedMessage('Zz Christmas Program', '2023-12-23 10:00:00');
        $this->seedSuperUser('zz-wanted@example.test', 1, 0);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not connect');

        Cwmalias::emailRenames($this->db, Cwmalias::sharedRenames($this->db, [
            '#__bsms_studies' => [['id' => $id, 'from' => 'zz-christmas-program', 'to' => 'zz-christmas-program-2']],
        ]), $this->mailerFactoryFor($this->recordingMailer(false)));
    }

    /**
     * reportRenames() tries the email once and returns normally when the mail server can't
     * be reached.
     *
     * @return  void
     *
     * @since   10.7.4
     */
    #[TestDox('A report after a restore or Fix survives a failing mail server, so the repair it describes stands')]
    public function testReportSurvivesAFailedSend(): void
    {
        $id = $this->seedMessage('Zz Christmas Program', '2023-12-23 10:00:00');
        $this->seedSuperUser('zz-wanted@example.test', 1, 0);

        $mail = $this->recordingMailer(false);

        Cwmalias::reportRenames($this->db, [
            '#__bsms_studies' => [['id' => $id, 'from' => 'zz-christmas-program', 'to' => 'zz-christmas-program-2']],
        ], $this->mailerFactoryFor($mail));

        $this->assertSame(1, $mail->sends);
    }

    /**
     * Insert a published message with the given title and date, and a unique alias.
     *
     * @param   string  $title  The message title
     * @param   string  $date   Its date
     *
     * @return  int  The new message id
     *
     * @since   10.7.4
     */
    private function seedMessage(string $title, string $date): int
    {
        $row = (object) [
            'studytitle' => $title,
            'alias'      => $this->uniqueAlias('report'),
            'studydate'  => $date,
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }

    /**
     * A user in the Super Users group, which holds core.admin on the root asset.
     *
     * @param   string  $email      The user's email
     * @param   int     $sendEmail  Whether they receive system emails
     * @param   int     $block      Whether they are blocked
     *
     * @return  void
     *
     * @since   10.7.4
     */
    private function seedSuperUser(string $email, int $sendEmail, int $block): void
    {
        $user = (object) [
            'name'         => 'Zz ' . $email,
            'username'     => $email,
            'email'        => $email,
            'password'     => '',
            'block'        => $block,
            'sendEmail'    => $sendEmail,
            'registerDate' => '2026-01-01 00:00:00',
            'params'       => '',
        ];

        $this->db->insertObject('#__users', $user);

        $map = (object) ['user_id' => (int) $this->db->insertid(), 'group_id' => 8];
        $this->db->insertObject('#__user_usergroup_map', $map);
    }
}

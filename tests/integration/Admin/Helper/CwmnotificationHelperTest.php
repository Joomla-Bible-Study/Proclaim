<?php

/**
 * @package    Proclaim.Tests
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Tests\Integration\Admin\Helper;

use CWM\Component\Proclaim\Administrator\Helper\CwmnotificationHelper;
use CWM\Component\Proclaim\Tests\Integration\IntegrationTestCase;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * The email that a comment held for approval sends its moderator.
 *
 * The mailer is a stand-in that records instead of sending.
 *
 * @since  10.7.4
 */
class CwmnotificationHelperTest extends IntegrationTestCase
{
    /**
     * The database, inside a transaction each test rolls back.
     *
     * @var    DatabaseInterface|null
     * @since  10.7.4
     */
    private ?DatabaseInterface $db = null;

    /**
     * Skip without a test database. Otherwise load the component's language strings, which
     * the email uses, and open a transaction.
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

        // The subject and body are translated strings; without the component's
        // language they would read as bare keys.
        Factory::getApplication()->getLanguage()->load('com_proclaim', \dirname(__DIR__, 4) . '/admin', 'en-GB', true);

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
     * notifyCommentPending() sends one email to the moderation address and returns true. The
     * subject names the message, and the body holds the commenter, the comment and a link to
     * the comments list.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Exception
     */
    #[TestDox('A comment held for approval emails the moderation address, naming the message and the comment')]
    public function testModerationEmailGoesToTheModerator(): void
    {
        $id   = $this->seedMessage('Zz Moderated Message');
        $mail = $this->recordingMailer();

        $sent = CwmnotificationHelper::notifyCommentPending(
            $id,
            'Zz Visitor',
            'A thoughtful comment.',
            new Registry(['comment_notify_email' => 'moderator@example.test']),
            $this->mailerFactoryFor($mail)
        );

        $this->assertTrue($sent);
        $this->assertSame(1, $mail->sends);
        $this->assertSame(['moderator@example.test'], array_column($mail->getToAddresses(), 0));
        $this->assertStringContainsString('Zz Moderated Message', $mail->Subject);
        $this->assertStringContainsString('Zz Visitor', $mail->Body);
        $this->assertStringContainsString('A thoughtful comment.', $mail->Body);
        $this->assertStringContainsString('option=com_proclaim&view=cwmcomments', $mail->Body);
    }

    /**
     * With no moderation address set, notifyCommentPending() emails the site's From address.
     * The From address is restored after.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Exception
     */
    #[TestDox('With no moderation address set, the email goes to the site\'s From address')]
    public function testModerationEmailFallsBackToTheSiteAddress(): void
    {
        $id    = $this->seedMessage('Zz Moderated Message');
        $mail  = $this->recordingMailer();
        $app   = Factory::getApplication();
        $saved = $app->get('mailfrom');

        try {
            $app->set('mailfrom', 'site@example.test');

            CwmnotificationHelper::notifyCommentPending(
                $id,
                'Zz Visitor',
                'A comment.',
                new Registry([]),
                $this->mailerFactoryFor($mail)
            );
        } finally {
            $app->set('mailfrom', $saved);
        }

        $this->assertSame(['site@example.test'], array_column($mail->getToAddresses(), 0));
    }

    /**
     * notifyCommentPending() tries the email once and returns false, rather than throwing,
     * when the mail server can't be reached.
     *
     * @return  void
     *
     * @since   10.7.4
     * @throws  \Exception
     */
    #[TestDox('A mail server that fails returns false rather than throwing, so the comment still saves')]
    public function testFailedSendReturnsFalse(): void
    {
        $id   = $this->seedMessage('Zz Moderated Message');
        $mail = $this->recordingMailer(false);

        $this->assertFalse(CwmnotificationHelper::notifyCommentPending(
            $id,
            'Zz Visitor',
            'A comment.',
            new Registry(['comment_notify_email' => 'moderator@example.test']),
            $this->mailerFactoryFor($mail)
        ));
        $this->assertSame(1, $mail->sends);
    }

    /**
     * Insert a published message with the given title and a unique alias.
     *
     * @param   string  $title  The message title
     *
     * @return  int  The new message id
     *
     * @since   10.7.4
     */
    private function seedMessage(string $title): int
    {
        $row = (object) [
            'studytitle' => $title,
            'alias'      => $this->uniqueAlias('moderated'),
            'studydate'  => '2026-11-08 10:00:00',
            'published'  => 1,
            'access'     => 1,
            'language'   => '*',
        ];

        $this->db->insertObject('#__bsms_studies', $row);

        return (int) $this->db->insertid();
    }
}

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
 * @since  __DEPLOY_VERSION__
 */
class CwmnotificationHelperTest extends IntegrationTestCase
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

        // The subject and body are translated strings; without the component's
        // language they would read as bare keys.
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
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @param   string  $title  The message title
     *
     * @return  int  The new message id
     *
     * @since   __DEPLOY_VERSION__
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

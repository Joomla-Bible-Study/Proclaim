<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Comment Notification Helper
 *
 * Handles sending email notifications when comments need moderation.
 *
 * @package  Proclaim.Admin
 * @since    10.2.0
 */
class CwmnotificationHelper
{
    /**
     * Send notification email when a comment is submitted and held for approval
     *
     * @param   int       $studyId      The study ID the comment is for
     * @param   string    $authorName   The comment author's name
     * @param   string    $commentText  The comment text
     * @param   Registry  $params       Template parameters containing notification settings
     *
     * @return  bool  True on success, false on failure
     *
     * @throws \Exception
     * @since   10.2.0
     */
    public static function notifyCommentPending(
        int $studyId,
        string $authorName,
        string $commentText,
        Registry $params
    ): bool {
        try {
            $app = Factory::getApplication();

            // Get moderation notification email from params or fall back to site admin email
            $recipientEmail = $params->get('comment_notify_email', '');
            if (empty($recipientEmail)) {
                $recipientEmail = $app->get('mailfrom');
            }

            if (empty($recipientEmail)) {
                return false;
            }

            // Get study details for the email
            $studyDetails = self::getStudyDetails($studyId);
            if (!$studyDetails) {
                return false;
            }

            // Prepare email content
            $adminUrl = Uri::root() . 'administrator/index.php?option=com_proclaim&view=cwmcomments';

            // Truncate comment text for excerpt
            $commentExcerpt = mb_strlen($commentText) > 200
                ? mb_substr(strip_tags($commentText), 0, 200) . '...'
                : strip_tags($commentText);

            // Build subject
            $subject = Text::sprintf('JBS_CMT_NOTIFICATION_SUBJECT', $studyDetails->studytitle);

            // Build body
            $body = Text::sprintf(
                'JBS_CMT_NOTIFICATION_BODY',
                $studyDetails->studytitle,
                $authorName,
                $commentExcerpt,
                $adminUrl
            );

            // Get mailer instance
            $mailer = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerInterface::class);
            $mailer->addRecipient($recipientEmail);
            $mailer->setSubject($subject);
            $mailer->setBody($body);

            return $mailer->Send();
        } catch (\Exception $e) {
            // Log error but don't break the comment submission
            Factory::getApplication()->enqueueMessage(
                Text::_('JBS_CMT_NOTIFICATION_FAILED'),
                'warning'
            );
            return false;
        }
    }

    /**
     * Get study details for notification email
     *
     * @param   int  $studyId  The study ID
     *
     * @return  object|null  Study details object or null on failure
     *
     * @since   10.2.0
     */
    protected static function getStudyDetails(int $studyId): ?object
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName(['s.studytitle', 's.studydate', 'b.bookname']))
            ->from($db->quoteName('#__bsms_studies', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__bsms_books', 'b') . ' ON '
                . $db->quoteName('b.booknumber') . ' = ' . $db->quoteName('s.booknumber')
            )
            ->where($db->quoteName('s.id') . ' = ' . (int) $studyId);
        $db->setQuery($query);

        return $db->loadObject();
    }
}

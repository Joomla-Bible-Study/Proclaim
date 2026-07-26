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

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;

/**
 * Helper for integrating with Joomla's com_actionlogs.
 *
 * Provides a simple static log() method that records actions in Joomla's
 * built-in Action Logs view.
 *
 * @since  10.1.0
 */
class CwmactionlogHelper
{
    /**
     * Log an action to Joomla's action logs.
     *
     * @param   string  $messageKey  Language key for the log message (e.g. 'COM_PROCLAIM_ACTION_LOG_MESSAGE_SAVED')
     * @param   string  $title       Title of the item being acted upon
     * @param   string  $type        Entity type for linking (e.g. 'message', 'teacher')
     * @param   int     $id          ID of the item
     * @param   array   $extra       Additional data to include in the log message
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public static function log(string $messageKey, string $title, string $type, int $id, array $extra = []): void
    {
        // Check if com_actionlogs is enabled
        if (!ComponentHelper::isEnabled('com_actionlogs')) {
            return;
        }

        try {
            $app  = Factory::getApplication();
            $user = $app->getIdentity();

            if (!$user || !$user->id) {
                return;
            }

            $message = array_merge([
                'action'      => $type,
                'type'        => 'COM_PROCLAIM_ACTION_LOG_TYPE_' . strtoupper($type),
                'id'          => $id,
                'title'       => $title,
                'itemlink'    => 'index.php?option=com_proclaim&task=cwm' . $type . '.edit&id=' . $id,
                'userid'      => $user->id,
                'username'    => $user->username,
                'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
                'origin'      => Text::_(self::originLabel($app)),
            ], $extra);

            /** @var ActionlogModel $model */
            $model = $app->bootComponent('com_actionlogs')
                ->getMVCFactory()
                ->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);

            $model->addLog([$message], $messageKey, 'com_proclaim', $user->id);
        } catch (\Exception $e) {
            // Silently fail — action logging should never break the main workflow
        }
    }

    /**
     * Language key naming where an action came from.
     *
     * Derived centrally from the running application rather than passed in by
     * callers. Two reasons: a caller cannot mislabel its own origin, and there is
     * exactly one place to extend when a new entry point appears. The action log
     * has no origin column, so this is carried in the message text via {origin}.
     *
     * @param   CMSApplicationInterface  $app  The running application.
     *
     * @return  string  A COM_PROCLAIM_ACTION_LOG_ORIGIN_* language key.
     *
     * @since   __DEPLOY_VERSION__
     */
    private static function originLabel(CMSApplicationInterface $app): string
    {
        if ($app->isClient('api')) {
            return 'COM_PROCLAIM_ACTION_LOG_ORIGIN_API';
        }

        if ($app->isClient('site')) {
            return 'COM_PROCLAIM_ACTION_LOG_ORIGIN_SITE';
        }

        if ($app->isClient('cli') || $app->isClient('console')) {
            return 'COM_PROCLAIM_ACTION_LOG_ORIGIN_CLI';
        }

        return 'COM_PROCLAIM_ACTION_LOG_ORIGIN_ADMIN';
    }
}

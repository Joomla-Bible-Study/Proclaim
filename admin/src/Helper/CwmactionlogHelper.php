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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
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
}

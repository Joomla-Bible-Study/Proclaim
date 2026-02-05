<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// Always load Proclaim API if it exists.
$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (!\defined('CWM_LOADED')) {
    require_once $api;
}

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Checks if Simple Mode is currently enabled.
 *
 * Shows the post-install message when Simple Mode is ON,
 * so users are aware of the setting and can turn it off
 * if they need advanced features.
 *
 * @return  bool
 *
 * @since   10.1.0
 */
function admin_postinstall_simplemode_condition(): bool
{
    try {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('params')
            ->from('#__bsms_admin')
            ->where($db->quoteName('id') . ' = 1');
        $db->setQuery($query);

        $params = $db->loadResult();

        if (empty($params)) {
            return false;
        }

        $registry = new Registry($params);

        return (int) $registry->get('simple_mode', 0) === 1;
    } catch (\Exception $e) {
        return false;
    }
}

/**
 * Redirect to the Admin settings to configure Simple Mode.
 *
 * @return  void
 *
 * @throws \Exception
 * @since   10.1.0
 */
function admin_postinstall_simplemode_action(): void
{
    $url = 'index.php?option=com_proclaim&view=cwmadmin';
    Factory::getApplication()->redirect($url);
}
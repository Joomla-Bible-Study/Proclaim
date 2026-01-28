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
$api = '../components/com_proclaim/api.php';

if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
    require_once $api;
}

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Checks if the archived messages feature needs to be configured.
 *
 * This check returns true if templates don't have the archive defaults set,
 * meaning that the message concerning it should be displayed.
 *
 * @return  bool
 *
 * @since   10.2.0
 */
function admin_postinstall_archivedmessages_condition(): bool
{
    try {
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('params')
            ->from('#__bsms_templates')
            ->setLimit(1);
        $db->setQuery($query);

        $params = $db->loadResult();

        if (empty($params)) {
            return true;
        }

        $registry = new Registry($params);

        // Check if the archive defaults have been set
        // If default_show_archived is null, the feature hasn't been configured
        return $registry->get('default_show_archived') === null;
    } catch (\Exception $e) {
        // If we can't check, show the message anyway
        return true;
    }
}

/**
 * Redirect to the Templates view to configure archive settings.
 *
 * @return  void
 *
 * @throws \Exception
 * @since   10.2.0
 */
function admin_postinstall_archivedmessages_action(): void
{
    $url = 'index.php?option=com_proclaim&view=cwmtemplates';
    Factory::getApplication()->redirect($url);
}

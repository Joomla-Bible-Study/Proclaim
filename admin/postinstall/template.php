<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Checks if the template is set up right.
 *
 * This check returns true Templates is not setup yet, meaning
 * that the message concerning it should be displayed.
 *
 * @return  bool
 *
 * @since   3.2
 */
function admin_postinstall_template_condition(): bool
{
    // Always load Proclaim API if it exists.
    $api = '../components/com_proclaim/api.php';

    if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
        require_once $api;
    }

    $results = null;

    $db    = Factory::getContainer()->get('DatabaseDriver');
    $qurey = $db->getQuery(true);
    $qurey->select('*')->from('#__bsms_templates');
    $db->setQuery($qurey);

    try {
        $tables = $db->loadObjectList();

        foreach ($tables as $table) {
            $registry = new Registry();
            $registry->loadString($table->params);

            if ($registry->get('playerresposive', false)) {
                $results = false;
            } else {
                $results = true;
            }
        }
    } catch (Exception $e) {
        $results = null;
    }

    return $results;
}

/**
 * Redirect the view to the Templates view
 *
 * @return  void
 *
 * @throws Exception
 * @since  3.2
 */
function admin_postinstall_template_action(): void
{
    // Always load Proclaim API if it exists.
    $api = '../components/com_proclaim/api.php';

    if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
        require_once $api;
    }

    $url = 'index.php?option=com_proclaim&view=templates';
    Factory::getApplication()->redirect($url);
}

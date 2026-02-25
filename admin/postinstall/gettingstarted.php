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

/**
 * Condition for the Getting Started tour message.
 *
 * @return  bool  Always true to show the message until dismissed
 *
 * @since 10.1.0
 */
function admin_postinstall_gettingstarted_condition(): bool
{
    return true;
}

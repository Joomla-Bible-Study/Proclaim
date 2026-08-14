<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Controller for the cPanel
 *
 * Pure display controller -- CwmcpanelModel extends BaseModel, not
 * FormModel, and no task=cwmcpanel.* routing exists (only view=cwmcpanel
 * display links). Matches DisplayController's pattern for display-only
 * controllers.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmcpanelController extends BaseController
{
}

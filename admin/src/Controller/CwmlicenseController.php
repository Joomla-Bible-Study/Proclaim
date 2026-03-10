<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\Cwmparams;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Controller for the license acceptance screen.
 *
 * @since  10.1.0
 */
class CwmlicenseController extends BaseController
{
    /**
     * Accept the license terms.
     *
     * Validates CSRF, stores the license_accepted flag in component params,
     * and redirects to the control panel.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function accept(): void
    {
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        Cwmparams::setCompParams(['license_accepted' => '1']);

        $app = Factory::getApplication();
        $app->enqueueMessage(Text::_('JBS_LICENSE_ACCEPTED_MSG'));
        $app->redirect(Route::_('index.php?option=com_proclaim&view=cwmcpanel', false));
    }
}

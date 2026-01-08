<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for Server
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserverController extends FormController
{
    /**
     * Method to add a new record.
     *
     * @return  bool  True if the record can be added, an error object if not.
     *
     * @throws \Exception
     * @since   12.2
     */
    public function add(): bool
    {
        $app = Factory::getApplication();

        if (parent::add()) {
            $app->setUserState('com_proclaim.edit.cwmserver.server_name', null);
            $app->setUserState('com_proclaim.edit.cwmserver.type', null);

            return true;
        }

        return false;
    }

    /**
     * Resets the User state for the server type. Needed to allow the value from the DB to be used
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key
     *                           (sometimes required to avoid router collisions).
     *
     * @return  bool
     *
     * @throws \Exception
     * @since   9.0.0
     */
    public function edit($key = null, $urlVar = null): bool
    {
        $app    = Factory::getApplication();
        $result = parent::edit();

        if ($result) {
            $app->setUserState('com_proclaim.edit.cwmserver.server_name', null);
            $app->setUserState('com_proclaim.edit.cwmserver.type', null);
        }

        return $result;
    }

    /**
     * Sets the type of endpoint currently being configured.
     *
     * @return  void
     *
     * @throws \Exception
     * @since   9.0.0
     */
    public function setType(): void
    {
        $app   = Factory::getApplication();
        $input = $app->input;

        $data  = $input->get('jform', [], 'post');
        $sname = $data['server_name'] ?? '';
        $type  = json_decode(base64_decode($data['type'] ?? ''), true, 512, JSON_THROW_ON_ERROR);

        $recordId = $type['id'] ?? 0;

        // Save the endpoint in the session
        $app->setUserState('com_proclaim.edit.cwmserver.type', $type['name'] ?? '');
        $app->setUserState('com_proclaim.edit.cwmserver.server_name', $sname);

        $this->setRedirect(
            Route::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item .
                $this->getRedirectToItemAppend((int)$recordId),
                false
            )
        );
    }

    /**
     * Generic AJAX handler for addon actions
     *
     * This method provides a single entry point for all addon AJAX requests.
     * It routes requests to the appropriate addon based on the 'addon' parameter
     * and dispatches to the specified action.
     *
     * URL format: index.php?option=com_proclaim&task=cwmserver.addonAjax&addon=youtube&action=testApi
     *
     * @return  void
     *
     * @throws \Exception
     * @since   10.0.0
     */
    public function addonAjax(): void
    {
        $app       = Factory::getApplication();
        $addonType = $app->input->getString('addon', '');
        $action    = $app->input->getString('action', '');

        if (empty($addonType)) {
            CWMAddon::outputJson(['success' => false, 'error' => 'No addon type specified']);

            return;
        }

        if (empty($action)) {
            CWMAddon::outputJson(['success' => false, 'error' => 'No action specified']);

            return;
        }

        CWMAddon::handleAjaxRequest($addonType, $action);
    }
}

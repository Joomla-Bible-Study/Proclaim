<?php

/**
 * Part of Proclaim Package
 *
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Addons\CWMAddon;
use CWM\Component\Proclaim\Administrator\Table\CwmmediafileTable;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Controller For MediaFile
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmmediafileController extends FormController
{
    /**
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var string
     * @since 7.0
     */
    protected $view_list = 'cwmmediafiles';

    /**
     * The URL option for the component.
     *
     * @var    string
     * @since  7.0.0
     */
    protected $option = 'com_proclaim';

    /**
     * Method to add a new record.
     *
     * @return  bool  True, if the record can be added, a error object if not.
     *
     * @throws  \Exception
     * @since   7.0.0
     */
    public function add(): bool
    {
        $app = Factory::getApplication();

        if (parent::add()) {
            $app->setUserState('com_proclaim.edit.mediafile.createdate', null);
            $app->setUserState('com_proclaim.edit.mediafile.study_id', null);
            $app->setUserState('com_proclaim.edit.mediafile.server_id', null);

            return true;
        }

        return false;
    }

    /**
     * Resets the User state for the server type. Needed to allow the value from the DB to be used
     *
     * @param   int     $key     ?
     * @param   string  $urlVar  ?
     *
     * @return  bool
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function edit($key = null, $urlVar = null): bool
    {
        $app    = Factory::getApplication();
        $result = parent::edit();

        if ($result) {
            $app->setUserState('com_proclaim.edit.mediafile.createdate', null);
            $app->setUserState('com_proclaim.edit.mediafile.study_id', null);
            $app->setUserState('com_proclaim.edit.mediafile.server_id', null);
        }

        return true;
    }

    /**
     * Handles XHR requests (i.e. File uploads)
     *
     * @return void
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function xhr(): void
    {
        if (!Session::checkToken('get')) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmmediafiles', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }
        $input = Factory::getApplication()->getInput();

        $addonType = $input->get('type', 'Legacy', 'string');
        $handler   = $input->get('handler');

        // Load the addon
        $addon = CWMAddon::getInstance($addonType);

        if (method_exists($addon, $handler)) {
            echo json_encode($addon->$handler($input), JSON_THROW_ON_ERROR);

            $app = Factory::getApplication();
            $app->close();
        } else {
            throw new \RuntimeException(Text::sprintf('Handler: "' . $handler . '" does not exist!'), 404);
        }
    }

    /**
     * Method to run batch operations.
     *
     * @param   CwmmediafileModel  $model  The model.
     *
     * @return  bool     True if successful, false otherwise, and an internal error is set.
     *
     * @throws \Exception
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        $this->checkToken();

        if (!$model) {
            /** @var \CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel $model */
            $model = $this->getModel('Cwmmediafile', 'Administrator', []);
        }

        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmmediafiles' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($model);
    }

    /**
     * Method to cancel an edit.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  bool  True if access level checks pass, false otherwise.
     *
     * @throws \Exception
     * @since   7.0.0
     */
    public function cancel($key = null): bool
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmmediafiles', Text::_('JINVALID_TOKEN'), 'error');

            return false;
        }

        $app   = Factory::getApplication();
        $model = $this->getModel();
        /** @type CwmmediafileTable $table */
        $table   = $model->getTable();
        $checkin = property_exists($table, 'checked_out');

        if (empty($key)) {
            $key = (string)$table->getKeyName();
        }

        $recordId = $app->input->getInt($key);

        // Attempt to check in the current record.
        if ($recordId) {
            if ($checkin) {
                if ($model->checkin($recordId) === false) {
                    // Check-in failed, go back to the record and display a notice.
                    $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', ''), 'error');

                    $this->setRedirect(
                        Route::_(
                            'index.php?option=' . $this->option . '&view=' . $this->view_item
                            . $this->getRedirectToItemAppend($recordId, $key),
                            false
                        )
                    );

                    return false;
                }
            }
        }

        if ($this->getInput()->getCmd('return') && parent::cancel($key)) {
            $this->setRedirect(base64_decode($this->getInput()->getCmd('return')));

            return true;
        }

        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

        return false;
    }

    /**
     * Gets the URL arguments to append to an item redirect.
     *
     * @param   int     $recordId  The primary key ID for the item.
     * @param   string  $urlVar    The name of the URL variable for the ID.
     *
     * @return  string  The arguments to append to the redirect URL.
     *
     * @since   7.0.0
     */
    protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id'): string
    {
        $tmpl    = $this->getInput()->get('tmpl');
        $layout  = $this->getInput()->get('layout', 'edit', 'string');
        $return  = $this->getInput()->getCmd('return');
        $options = $this->getInput()->get('options');
        $append  = '';

        // Setup redirect info.
        if ($tmpl) {
            $append .= '&tmpl=' . $tmpl;
        }

        if ($layout) {
            $append .= '&layout=' . $layout;
        }

        if ($recordId) {
            $append .= '&' . $urlVar . '=' . $recordId;
        }

        if ($options) {
            $append .= '&options=' . $options;
        }

        if ($return) {
            $append .= '&return=' . $return;
        }

        return $append;
    }

    /**
     * Sets the server for this media record
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   9.0.0
     */
    public function setServer(): void
    {
        // Check for request forgeries.
        if (!Session::checkToken()) {
            $this->setRedirect('index.php?option=com_proclaim&view=cwmmediafiles', Text::_('JINVALID_TOKEN'), 'error');

            return;
        }

        $app   = Factory::getApplication();
        $input = $app->input;

        $data      = $input->get('jform', [], 'post', 'array');
        $cdate     = $data['createdate'];
        $study_id  = $data['study_id'];
        $server_id = $data['server_id'];

        // Save server in the session
        $app->setUserState('com_proclaim.edit.mediafile.createdate', $cdate);
        $app->setUserState('com_proclaim.edit.mediafile.study_id', $study_id);
        $app->setUserState('com_proclaim.edit.mediafile.server_id', $server_id);

        $redirect = $this->getRedirectToItemAppend($data['id']);
        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_item . $redirect, false)
        );
    }

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param   BaseModel  $model      The data model object.
     * @param   array      $validData  The validated data.
     *
     * @return    void
     *
     * @throws   \Exception
     * @since    3.1
     */
    protected function postSaveHook($model, $validData = []): void
    {
        $return = $this->getInput()->getCmd('return');
        $task   = $this->getInput()->get('task');

        if ($return && $task !== 'apply') {
            Factory::getApplication()->enqueueMessage(Text::_('JBS_MED_SAVE'), 'message');
            $this->setRedirect(base64_decode($return));
        }
    }
}

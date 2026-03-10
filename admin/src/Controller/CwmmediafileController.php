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
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

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
     * Method override to check if you can edit an existing record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  bool
     *
     * @throws \Exception
     * @since   10.1.0
     */
    protected function allowEdit($data = [], $key = 'id'): bool
    {
        $recordId = (int) ($data[$key] ?? 0);
        $user     = Factory::getApplication()->getIdentity();

        // Non-admin users must have access to the item's view level
        if (!$user->authorise('core.admin') && $recordId > 0) {
            $db    = Factory::getContainer()->get(DatabaseInterface::class);
            $query = $db->getQuery(true)
                ->select($db->quoteName('access'))
                ->from($db->quoteName('#__bsms_mediafiles'))
                ->where($db->quoteName('id') . ' = :rid')
                ->bind(':rid', $recordId, ParameterType::INTEGER);
            $db->setQuery($query);
            $access = (int) $db->loadResult();

            if ($access && !\in_array($access, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        return parent::allowEdit($data, $key);
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

        $recordId = $app->getInput()->getInt($key);

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

        if ($this->input->getCmd('return') && parent::cancel($key)) {
            $this->setRedirect(base64_decode($this->input->getCmd('return')));

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
        $tmpl    = $this->input->get('tmpl');
        $layout  = $this->input->get('layout', 'edit', 'string');
        $return  = $this->input->getCmd('return');
        $options = $this->input->get('options');
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
     * Return addon HTML fragments via AJAX for a given server_id.
     *
     * Called via GET with token validation. Returns JSON with generalHtml
     * and optionsHtml for the selected server's addon.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.1.0
     */
    public function getAddonHtml(): void
    {
        CWMAddon::prepareAjaxEnvironment();

        try {
            if (!Session::checkToken('get')) {
                CWMAddon::outputJson(['success' => false, 'error' => Text::_('JINVALID_TOKEN')]);
            }

            $app      = Factory::getApplication();
            $serverId = $app->getInput()->getInt('server_id', 0);

            if (empty($serverId)) {
                CWMAddon::outputJson(['success' => false, 'error' => 'No server_id provided']);
            }

            // Set server_id in user state so the model picks it up via populateState()
            $app->setUserState('com_proclaim.edit.mediafile.server_id', $serverId);

            /** @var \CWM\Component\Proclaim\Administrator\Model\CwmmediafileModel $model */
            $model = $this->getModel('Cwmmediafile', 'Administrator', []);

            // getItem() populates model->data including server_id from state
            $model->getItem();

            // getMediaForm() loads form paths, language, and returns the Joomla Form
            $mediaForm = $model->getMediaForm();

            if (empty($mediaForm)) {
                CWMAddon::outputJson(['success' => false, 'error' => 'Could not load media form']);
            }

            $serverType = $model->getState('type');
            $sParams    = $model->getState('s_params', []);

            // Wrap form with server params (same pattern as HtmlView::display)
            $wrappedForm = new class ($mediaForm, $sParams) {
                private $form;
                public array $s_params;

                public function __construct($form, array $s_params)
                {
                    $this->form     = $form;
                    $this->s_params = $s_params;
                }

                public function __call(string $name, array $args): mixed
                {
                    return $this->form->$name(...$args);
                }
            };

            // Bind server defaults for new items
            $mediaForm->bind(['params' => $sParams]);

            // Instantiate addon and render HTML
            $addon       = CWMAddon::getInstance($serverType);
            $generalHtml = $addon->renderGeneral($wrappedForm, true);
            $optionsHtml = $addon->renderOptionsFields($wrappedForm, true);

            CWMAddon::outputJson([
                'success'     => true,
                'generalHtml' => $generalHtml,
                'optionsHtml' => $optionsHtml,
                'serverType'  => $serverType,
            ]);
        } catch (\Exception $e) {
            CWMAddon::outputJson(['success' => false, 'error' => $e->getMessage()]);
        }
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
        $input = $app->getInput();

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
        $return = $this->input->getCmd('return');
        $task   = $this->input->get('task');

        if ($return && $task !== 'apply') {
            Factory::getApplication()->enqueueMessage(Text::_('JBS_MED_SAVE'), 'message');
            $this->setRedirect(base64_decode($return));
        }
    }
}

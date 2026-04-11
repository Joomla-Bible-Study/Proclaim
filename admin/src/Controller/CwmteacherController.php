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

use CWM\Component\Proclaim\Administrator\Controller\Trait\ModalFormTrait;
use CWM\Component\Proclaim\Administrator\Controller\Trait\MultiCampusAccessTrait;
use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for Teacher
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmteacherController extends FormController
{
    use MultiCampusAccessTrait;
    use ModalFormTrait;

    /**
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var   string
     * @since 7.0
     */
    protected $view_list = 'cwmteachers';

    /**
     * The URL option for the component.
     *
     * @var    string
     * @since  7.0.0
     */
    protected $option = 'com_proclaim';

    /**
     * The database table for access level checks.
     *
     * @var    string
     * @since  10.3.0
     */
    protected string $accessTable = '#__bsms_teachers';

    /**
     * Method to cancel an edit — redirects to modalreturn when in modal layout.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  bool  True if access level checks pass, false otherwise.
     *
     * @since   10.2.0
     */
    #[\Override]
    public function cancel($key = null): bool
    {
        $result = parent::cancel($key);
        $this->handleModalCancel($result);

        return $result;
    }

    /**
     * Method to run batch operations.
     *
     * @param   BaseModel  $model  The model.
     *
     * @return  bool     True if successful, false otherwise and internal error is set.
     *
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmteachers' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($this->getModel());
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
        $denied = $this->checkRecordAccessLevel((int) ($data[$key] ?? 0));
        if ($denied === false) {
            return false;
        }

        return parent::allowEdit($data, $key);
    }

    /**
     * Method to run after a successful save.
     *
     * @param   BaseDatabaseModel  $model      The model.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = []): void
    {
        $id    = (int) $model->getState('cwmteacher.id');
        $isNew = empty($validData['id']);
        $key   = $isNew ? 'COM_PROCLAIM_ACTION_LOG_TEACHER_ADDED' : 'COM_PROCLAIM_ACTION_LOG_TEACHER_UPDATED';
        $title = trim(($validData['teachername'] ?? '') . ' ' . ($validData['title'] ?? ''));

        CwmactionlogHelper::log($key, $title, 'teacher', $id);

        if ($this->handleModalPostSave($id)) {
            return;
        }
    }

    /**
     * AJAX handler — returns the rendered Messages tab HTML for a teacher.
     *
     * Called by the lazy-load script when the Messages tab is first shown,
     * so the eager join-heavy query is only run when the user actually
     * needs it.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function loadMessages(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken('get')) {
            $app->setHeader('status', 403, true);
            echo '<div class="alert alert-danger">' . Text::_('JINVALID_TOKEN') . '</div>';
            $app->close();

            return;
        }

        $id = (int) $app->getInput()->getInt('id', 0);

        if ($id <= 0) {
            echo '<div class="alert alert-warning">' . Text::_('JBS_TCH_NO_MESSAGES') . '</div>';
            $app->close();

            return;
        }

        // Access-level gate mirrors the teacher edit view.
        if ($this->checkRecordAccessLevel($id) === false) {
            $app->setHeader('status', 403, true);
            echo '<div class="alert alert-danger">' . Text::_('JERROR_ALERTNOAUTHOR') . '</div>';
            $app->close();

            return;
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmteacherModel $model */
        $model = $this->getModel('Cwmteacher');

        // Initialise the model state so getMessages() resolves the right teacher.
        $model->getState();
        $model->setState('cwmteacher.id', $id);
        $model->getItem($id);

        $messages   = $model->getMessages();
        $totalCount = $model->getMessagesCount();

        ob_start();
        include JPATH_ADMINISTRATOR . '/components/com_proclaim/tmpl/cwmteacher/messages.php';
        $html = ob_get_clean();

        $app->setHeader('Content-Type', 'text/html; charset=utf-8', true);
        echo $html;
        $app->close();
    }

    /**
     * AJAX handler — returns the rendered Biography tab HTML for a teacher.
     *
     * The Biography tab contains two TinyMCE editors which are expensive to
     * initialise on every edit form load even when the user only wants to
     * edit a name or status. We render hidden placeholder inputs in the
     * server response so that the form save still receives the values when
     * the tab is never opened, then swap in the real editor markup the
     * first time the user activates the tab.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function loadBiography(): void
    {
        $app = Factory::getApplication();

        if (!Session::checkToken('get')) {
            $app->setHeader('status', 403, true);
            echo '<div class="alert alert-danger">' . Text::_('JINVALID_TOKEN') . '</div>';
            $app->close();

            return;
        }

        $id = (int) $app->getInput()->getInt('id', 0);

        if ($id < 0) {
            echo '<div class="alert alert-warning"></div>';
            $app->close();

            return;
        }

        if ($id > 0 && $this->checkRecordAccessLevel($id) === false) {
            $app->setHeader('status', 403, true);
            echo '<div class="alert alert-danger">' . Text::_('JERROR_ALERTNOAUTHOR') . '</div>';
            $app->close();

            return;
        }

        /** @var \CWM\Component\Proclaim\Administrator\Model\CwmteacherModel $model */
        $model = $this->getModel('Cwmteacher');
        $model->getState();
        $model->setState('cwmteacher.id', $id);

        $form = $model->getForm([], true);

        if (!$form) {
            echo '<div class="alert alert-danger">'
                . Text::_('JERROR_LOADING_FORM') . '</div>';
            $app->close();

            return;
        }

        if ($id > 0) {
            $item = $model->getItem($id);

            if ($item) {
                $form->bind($item);
            }
        }

        $fields = $form->getFieldset('biography');

        $html = '';

        foreach ($fields as $field) {
            $html .= $field->renderField();
        }

        $app->setHeader('Content-Type', 'text/html; charset=utf-8', true);
        echo $html;
        $app->close();
    }
}

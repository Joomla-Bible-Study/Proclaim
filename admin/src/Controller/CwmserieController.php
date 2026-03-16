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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Controller for a Serie
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmserieController extends FormController
{
    /**
     * Prevents Joomla's pluralization mechanism from altering the view name.
     *
     * @var   string
     * @since 7.0
     */
    protected $view_list = 'cwmseries';

    /**
     * Method to cancel an edit — redirects to modalreturn when in modal layout.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  bool  True if access level checks pass, false otherwise.
     *
     * @since   10.1.0
     */
    #[\Override]
    public function cancel($key = null): bool
    {
        $result = parent::cancel($key);

        // When editing in modal then redirect to modalreturn layout
        if ($result && $this->input->get('layout') === 'modal') {
            $id     = $this->input->get('id');
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item . $this->getRedirectToItemAppend($id)
                . '&layout=modalreturn&from-task=cancel';

            $this->setRedirect(Route::_($return, false));
        }

        return $result;
    }

    /**
     * Post-save hook — redirects to modalreturn when saving in modal layout.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    #[\Override]
    protected function postSaveHook(BaseDatabaseModel $model, $validData = []): void
    {
        if ($this->input->get('layout') !== 'modal') {
            return;
        }

        $id = $model->getState('cwmserie.id', '');

        if ($this->task === 'save') {
            // Save & Close in modal — redirect to modalreturn to send data back to parent
            $return = 'index.php?option=' . $this->option . '&view=' . $this->view_item
                . $this->getRedirectToItemAppend($id) . '&layout=modalreturn&from-task=save';
            $this->setRedirect(Route::_($return, false));
        } elseif ($this->task === 'apply') {
            // Apply in modal — stay on modal layout
            $return = 'index.php?option=' . $this->option . '&task=' . $this->view_item . '.edit'
                . $this->getRedirectToItemAppend($id) . '&layout=modal&tmpl=component';
            $this->setRedirect(Route::_($return, false));
        }
    }

    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  bool     True if successful, false otherwise and internal error is set.
     *
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmseries' . $this->getRedirectToListAppend(), false)
        );

        return parent::batch($this->getModel('Cwmserie', '', []));
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
     * @since   1.6
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
                ->from($db->quoteName('#__bsms_series'))
                ->where($db->quoteName('id') . ' = :rid')
                ->bind(':rid', $recordId, ParameterType::INTEGER);
            $db->setQuery($query);
            $access = (int) $db->loadResult();

            if ($access && !\in_array($access, $user->getAuthorisedViewLevels())) {
                return false;
            }
        }

        // Check general edit permission first.
        if ($user->authorise('core.edit', 'com_proclaim.serie.' . $recordId)) {
            return true;
        }

        // Since there is no asset tracking, revert to the component permissions.
        return parent::allowEdit($data, $key);
    }
}

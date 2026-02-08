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

use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;

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
    }
}

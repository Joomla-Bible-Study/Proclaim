<?php

/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 * */

declare(strict_types=1);

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Model\CwmpermissionsModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Section permissions controller.
 *
 * @since  10.5.8
 */
class CwmpermissionsController extends BaseController
{
    /**
     * Gate every task behind core.admin, as Joomla gates com_config's
     * Permissions tab. core.options is not enough.
     *
     * @param   string  $task  The task to execute.
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @since   10.5.8
     */
    #[\Override]
    public function execute($task): mixed
    {
        if (!Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_proclaim')) {
            $this->setRedirect(
                Route::_('index.php?option=com_proclaim&view=cwmcpanel', false),
                Text::_('JERROR_ALERTNOAUTHOR'),
                'warning'
            );

            return null;
        }

        return parent::execute($task);
    }

    /**
     * Save the posted grid and return to the same section.
     *
     * ⚠️ Always redirect: `Access::getAssetRules()` memoises for the request,
     * so a grid re-rendered inline shows the values from before the write.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.5.8
     */
    public function save(): void
    {
        $this->checkToken();

        /** @var CwmpermissionsModel $model */
        $model   = $this->getModel('Cwmpermissions');
        $section = $model->getSection();
        $posted  = $this->input->post->get('jform', [], 'array');

        $return = 'index.php?option=com_proclaim&view=cwmpermissions&section=' . $section;

        // ⚠️ Must go through the form: "Inherited" posts as an empty string and
        // only filter="rules" drops it. Raw POST records every inherited cell as
        // an explicit Denied. FormController does this for ordinary edit screens.
        $form = $model->getForm($posted, false);

        if (!$form) {
            $this->setRedirect(Route::_($return, false), Text::_('JBS_ADM_PERMISSIONS_NO_SECTIONS'), 'error');

            return;
        }

        $data = $model->validate($form, $posted);

        if ($data === false) {
            $errors  = $model->getErrors();
            $message = $errors === [] ? Text::_('JLIB_APPLICATION_ERROR_SAVE_FAILED') : (string) $errors[0];

            $this->setRedirect(Route::_($return, false), $message, 'error');

            return;
        }

        try {
            $saved = $model->save($data);
        } catch (\RuntimeException $e) {
            $this->setRedirect(Route::_($return, false), $e->getMessage(), 'error');

            return;
        }

        if (!$saved) {
            $this->setRedirect(Route::_($return, false), $model->getError(), 'error');

            return;
        }

        if ($this->getTask() === 'save') {
            $this->setRedirect(
                Route::_('index.php?option=com_proclaim&view=cwmadmin', false),
                Text::_('JBS_ADM_PERMISSIONS_SAVED')
            );

            return;
        }

        $this->setRedirect(Route::_($return, false), Text::_('JBS_ADM_PERMISSIONS_SAVED'));
    }

    /**
     * Save and stay on the current section.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.5.8
     */
    public function apply(): void
    {
        $this->save();
    }

    /**
     * Leave without saving.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.5.8
     */
    public function cancel(): void
    {
        $this->checkToken();

        $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmadmin', false));
    }
}

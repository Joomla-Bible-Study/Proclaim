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
 * @since  __DEPLOY_VERSION__
 */
class CwmpermissionsController extends BaseController
{
    /**
     * Gate every task behind core.admin.
     *
     * Changing who may do what is an administrator action, not a settings
     * action, which is how Joomla gates com_config's Permissions tab. Holding
     * core.options is not enough.
     *
     * @param   string  $task  The task to execute.
     *
     * @return  mixed
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
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
     * Always a redirect, never an inline re-render: `RulesField` reads through
     * `Access::getAssetRules()`, which memoises into `Access::$assetRules` for
     * the request, so a grid rendered after the write would show the values
     * from before it.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function save(): void
    {
        $this->checkToken();

        /** @var CwmpermissionsModel $model */
        $model   = $this->getModel('Cwmpermissions');
        $section = $model->getSection();
        $data    = $this->input->post->get('jform', [], 'array');

        $return = 'index.php?option=com_proclaim&view=cwmpermissions&section=' . $section;

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
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public function cancel(): void
    {
        $this->checkToken();

        $this->setRedirect(Route::_('index.php?option=com_proclaim&view=cwmadmin', false));
    }
}

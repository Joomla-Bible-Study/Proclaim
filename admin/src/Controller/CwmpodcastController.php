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

use CWM\Component\Proclaim\Administrator\Helper\CwmactionlogHelper;
use CWM\Component\Proclaim\Administrator\Model\CwmpodcastModel;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;

/**
 * Podcast form class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CwmpodcastController extends FormController
{
    /**
     * Method to run batch operations.
     *
     * @param   CwmpodcastModel  $model  The model.
     *
     * @return  bool     True if successful, false otherwise and internal error is set.
     *
     * @throws \Exception
     * @since   1.6
     */
    public function batch($model = null): bool
    {
        $this->checkToken();

        // Preset the redirect
        $this->setRedirect(
            Route::_('index.php?option=com_proclaim&view=cwmpodcasts' . $this->getRedirectToListAppend(), false)
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
        $id    = (int) $model->getState('cwmpodcast.id');
        $isNew = empty($validData['id']);
        $key   = $isNew ? 'COM_PROCLAIM_ACTION_LOG_PODCAST_ADDED' : 'COM_PROCLAIM_ACTION_LOG_PODCAST_UPDATED';
        $title = $validData['title'] ?? '';

        CwmactionlogHelper::log($key, $title, 'podcast', $id);
    }
}

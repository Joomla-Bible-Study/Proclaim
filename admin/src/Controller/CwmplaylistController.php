<?php

/**
 * Controller for a single Playlist
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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Playlist item controller class
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class CwmplaylistController extends FormController
{
    /**
     * The URL view list variable.
     *
     * @var    string
     * @since  10.3.3
     */
    protected $view_list = 'cwmplaylists';

    /**
     * Method to run after a successful save — records the action in Joomla's logs.
     *
     * @param   BaseDatabaseModel  $model      The model.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = []): void
    {
        $id    = (int) $model->getState('cwmplaylist.id');
        $isNew = empty($validData['id']);
        $key   = $isNew ? 'COM_PROCLAIM_ACTION_LOG_PLAYLIST_ADDED' : 'COM_PROCLAIM_ACTION_LOG_PLAYLIST_UPDATED';

        CwmactionlogHelper::log($key, $validData['title'] ?? '', 'playlist', $id);
    }
}

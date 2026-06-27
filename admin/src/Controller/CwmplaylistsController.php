<?php

/**
 * Controller for Playlists
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

use CWM\Component\Proclaim\Administrator\Helper\CwmplaylistSyncHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;

/**
 * Playlists list controller class
 *
 * @package  Proclaim.Admin
 * @since    10.3.3
 */
class CwmplaylistsController extends AdminController
{
    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  The array of possible config values. Optional.
     *
     * @return  BaseDatabaseModel
     *
     * @since   10.3.3
     */
    public function getModel($name = 'Cwmplaylist', $prefix = 'Administrator', $config = ['ignore_request' => true]): BaseDatabaseModel
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Bulk-import playlists from YouTube and reconcile their videos against the
     * existing media library.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   __DEPLOY_VERSION__
     */
    public function import(): void
    {
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.create', 'com_proclaim')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $serverId = $this->input->getInt('server_id', 0);
        $redirect = Route::_('index.php?option=com_proclaim&view=cwmplaylists', false);

        try {
            $stats = CwmplaylistSyncHelper::import($serverId);
        } catch (\Exception $e) {
            $this->setMessage(Text::sprintf('JBS_PLAYLIST_IMPORT_FAILED', $e->getMessage()), 'error');
            $this->setRedirect($redirect);

            return;
        }

        $this->setMessage(
            Text::sprintf(
                'JBS_PLAYLIST_IMPORT_RESULT',
                $stats['playlistsCreated'],
                $stats['playlistsUpdated'],
                $stats['itemsMatched'],
                $stats['itemsUnmatched']
            ),
            $stats['errors'] === [] ? 'message' : 'warning'
        );

        foreach ($stats['errors'] as $error) {
            $this->app->enqueueMessage($error, 'warning');
        }

        $this->setRedirect($redirect);
    }
}

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

use CWM\Component\Proclaim\Administrator\Controller\Trait\CwmActionlogListTrait;
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
    use CwmActionlogListTrait;

    /**
     * @var string
     * @since 10.3.3
     */
    protected string $actionlogType = 'playlist';

    /**
     * @var string
     * @since 10.3.3
     */
    protected string $actionlogTable = '#__bsms_playlists';

    /**
     * @var string
     * @since 10.3.3
     */
    protected string $actionlogTitleColumn = 'title';

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
     * @since   10.3.3
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

        $clean = $stats['errors'] === [] && $stats['conflicts'] === [];

        $this->setMessage(
            Text::sprintf(
                'JBS_PLAYLIST_IMPORT_RESULT',
                $stats['playlistsCreated'],
                $stats['playlistsUpdated'],
                $stats['itemsMatched'],
                $stats['itemsUnmatched']
            ),
            $clean ? 'message' : 'warning'
        );

        foreach ($stats['conflicts'] as $conflict) {
            $this->app->enqueueMessage($conflict, 'warning');
        }

        foreach ($stats['errors'] as $error) {
            $this->app->enqueueMessage($error, 'warning');
        }

        $this->setRedirect($redirect);
    }

    /**
     * Push locally-authoritative playlist titles back to the remote platform
     * (e.g. YouTube playlists.update). Only playlists opted in to write-back
     * (writeback_enabled = 1) whose local title diverged after a local edit are
     * pushed.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public function push(): void
    {
        $this->runPush(false);
    }

    /**
     * Preview a write-back push without writing to the remote platform — reports
     * which playlist titles would be pushed.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    public function pushpreview(): void
    {
        $this->runPush(true);
    }

    /**
     * Shared write-back runner for push()/pushpreview().
     *
     * @param   bool  $dryRun  Report would-push titles without writing to the remote.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   10.3.3
     */
    private function runPush(bool $dryRun): void
    {
        $this->checkToken();

        if (!$this->app->getIdentity()->authorise('core.edit', 'com_proclaim')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $serverId = $this->input->getInt('server_id', 0);
        $redirect = Route::_('index.php?option=com_proclaim&view=cwmplaylists', false);

        try {
            $stats = CwmplaylistSyncHelper::import($serverId, false, true, $dryRun);
        } catch (\Exception $e) {
            $this->setMessage(Text::sprintf('JBS_PLAYLIST_PUSH_FAILED', $e->getMessage()), 'error');
            $this->setRedirect($redirect);

            return;
        }

        if ($dryRun) {
            $would = array_merge(
                $stats['titlesWouldPush'],
                $stats['descriptionsWouldPush'],
                $stats['membershipsWouldPush'],
                $stats['membershipsWouldRemove']
            );

            $this->setMessage(
                Text::sprintf('JBS_PLAYLIST_PUSH_DRYRUN_RESULT', \count($would)),
                'info'
            );

            foreach ($would as $line) {
                $this->app->enqueueMessage($line, 'info');
            }
        } else {
            $clean = $stats['pushErrors'] === [];

            $this->setMessage(
                Text::sprintf('JBS_PLAYLIST_PUSH_RESULT', $stats['titlesPushed'], $stats['descriptionsPushed'], $stats['membershipsPushed'], $stats['membershipsRemoved']),
                $clean ? 'message' : 'warning'
            );
        }

        foreach ($stats['pushErrors'] as $error) {
            $this->app->enqueueMessage($error, 'warning');
        }

        foreach ($stats['errors'] as $error) {
            $this->app->enqueueMessage($error, 'warning');
        }

        $this->setRedirect($redirect);
    }
}

<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * API controller for playlists — READ ONLY.
 *
 * GET /api/index.php/v1/proclaim/playlists       — list (published + archived)
 * GET /api/index.php/v1/proclaim/playlists/:id   — detail
 *
 * Filters: ?filter[search]=&filter[server_id]=&filter[series_id]=
 *
 * Playlists are queryable but never writable. `sync_enabled` and
 * `writeback_enabled` govern whether Proclaim pushes changes to the remote
 * platform, so a PATCH here would not just edit a local row — it could arm real
 * mutations against a church's live YouTube account. Playlist state is changed
 * deliberately in the admin UI, never as a side effect of an HTTP call.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlaylistsController extends AbstractReadOnlyController
{
    protected $contentType = 'playlists';

    protected $default_view = 'playlists';

    /**
     * List playlists — published and archived only.
     *
     * @return  static
     *
     * @since   __DEPLOY_VERSION__
     */
    public function displayList()
    {
        $this->modelState->set('filter.published', [1, 2]);

        $apiFilter = $this->input->get('filter', [], 'array');
        $clean     = InputFilter::getInstance();

        if (\array_key_exists('search', $apiFilter)) {
            $this->modelState->set('filter.search', $clean->clean($apiFilter['search'], 'STRING'));
        }

        if (\array_key_exists('server_id', $apiFilter)) {
            $this->modelState->set('filter.server_id', (int) $apiFilter['server_id']);
        }

        if (\array_key_exists('series_id', $apiFilter)) {
            $this->modelState->set('filter.series_id', (int) $apiFilter['series_id']);
        }

        return parent::displayList();
    }

    /**
     * Get the model, mapping API names to Cwm-prefixed Proclaim classes.
     *
     * @param   string  $name    Model name
     * @param   string  $prefix  Model prefix
     * @param   array   $config  Configuration
     *
     * @return  BaseDatabaseModel|false
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        $map = [
            'playlists' => 'Cwmplaylists',
            'playlist'  => 'Cwmplaylist',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }
}

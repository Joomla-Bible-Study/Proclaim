<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\View\Playlists;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON:API view for playlists.
 *
 * `params` and `default_settings` are withheld: both carry sync configuration
 * rather than content. `remote_playlist_id` is rendered because it is the public
 * platform identifier that already appears in a playlist's own share URL.
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render in the item response.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $fieldsToRenderItem = [
        'id',
        'title',
        'alias',
        'description',
        'remote_playlist_id',
        'server_id',
        'series_id',
        'sync_enabled',
        'writeback_enabled',
        'last_sync',
        'published',
        'access',
        'language',
        'ordering',
    ];

    /**
     * The fields to render in the list response.
     *
     * Limited to what the playlists list query selects — it does not include
     * alias, description, writeback_enabled or language.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'remote_playlist_id',
        'server_id',
        'series_id',
        'sync_enabled',
        'last_sync',
        'published',
        'access',
        'ordering',
    ];
}

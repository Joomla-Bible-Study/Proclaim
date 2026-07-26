<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\View\Servers;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON:API view for media servers.
 *
 * SECURITY: `params` must never appear in either list below. It is the registry
 * holding each server addon's credentials — api_key, client_secret,
 * access_token — and the item model loads the full row, so omitting it here is
 * the only thing keeping those out of an HTTP response. The list query does not
 * select it, but the item query does. Do not add it.
 *
 * @since  10.3.4
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render in the item response.
     *
     * @var    array
     * @since  10.3.4
     */
    protected $fieldsToRenderItem = [
        'id',
        'server_name',
        'type',
        'location_id',
        'media',
        'stats_synced_at',
        'published',
        'access',
    ];

    /**
     * The fields to render in the list response.
     *
     * @var    array
     * @since  10.3.4
     */
    protected $fieldsToRenderList = [
        'id',
        'server_name',
        'type',
        'location_id',
        'published',
    ];
}

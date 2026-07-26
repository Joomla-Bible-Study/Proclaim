<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\View\Templates;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON:API view for display templates.
 *
 * `tmpl` (the markup itself) is rendered on the item only, never in lists — it
 * is the substance of the resource, but large and pointless to repeat per row.
 * `params` is withheld as it carries template configuration.
 *
 * Sites that would rather not publish their markup at all should set the
 * `api_access` option to "API Key Required" rather than public reads.
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
        'title',
        'type',
        'tmpl',
        'location_id',
        'published',
        'access',
    ];

    /**
     * The fields to render in the list response.
     *
     * Limited to what the templates list query selects.
     *
     * @var    array
     * @since  10.3.4
     */
    protected $fieldsToRenderList = [
        'id',
        'title',
        'published',
    ];
}

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
 * `params` is withheld as it carries template configuration.
 *
 * ⚠️ `tmpl` was rendered on the item until 10.6.0 and is gone from both lists.
 * The column behind it was retired: nothing ever wrote to it, so the attribute
 * only ever carried an empty string, but a consumer reading it by name now
 * finds it absent rather than empty.
 *
 * Sites that would rather not publish their markup at all should leave the
 * webservices plugin's "Allow public reads" parameter off, so a token is
 * required — which is the default.
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

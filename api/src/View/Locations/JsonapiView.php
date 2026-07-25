<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\View\Locations;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON:API view for locations (campuses).
 *
 * A location's postal address, phone and web page are public-facing contact
 * details, so they are rendered. Deliberately withheld: `email_to` (the site's
 * notification target), `user_id` and `contact_id` (bindings to Joomla
 * accounts), and `fax` / `mobile` / `misc` / `sortname1-3`, which are internal
 * or personal rather than published contact info.
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
        'location_text',
        'address',
        'suburb',
        'state',
        'country',
        'postcode',
        'telephone',
        'webpage',
        'image',
        'published',
        'access',
        'language',
        'ordering',
        'landing_show',
    ];

    /**
     * The fields to render in the list response.
     *
     * Limited to what the locations list query selects.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    protected $fieldsToRenderList = [
        'id',
        'location_text',
        'published',
        'access',
    ];
}

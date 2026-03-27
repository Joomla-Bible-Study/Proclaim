<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\View\Media;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON:API view for media files.
 *
 * @since  10.3.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render in the item response.
     *
     * @var    array
     * @since  10.3.0
     */
    protected $fieldsToRenderItem = [
        'id',
        'study_id',
        'server_id',
        'server_name',
        'studytitle',
        'createdate',
        'plays',
        'downloads',
        'params',
        'access',
        'published',
        'language',
    ];

    /**
     * The fields to render in the list response.
     *
     * @var    array
     * @since  10.3.0
     */
    protected $fieldsToRenderList = [
        'id',
        'study_id',
        'server_id',
        'server_name',
        'studytitle',
        'createdate',
        'plays',
        'downloads',
        'access',
        'published',
    ];
}

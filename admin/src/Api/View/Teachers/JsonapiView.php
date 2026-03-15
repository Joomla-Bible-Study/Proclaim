<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Api\View\Teachers;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON:API view for teachers.
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
        'teachername',
        'alias',
        'title',
        'image',
        'short',
        'description',
        'website',
        'email',
        'phone',
        'access',
        'published',
        'params',
    ];

    /**
     * The fields to render in the list response.
     *
     * @var    array
     * @since  10.3.0
     */
    protected $fieldsToRenderList = [
        'id',
        'teachername',
        'alias',
        'title',
        'image',
        'short',
        'access',
        'published',
    ];
}

<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\View\Comments;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON:API view for study comments.
 *
 * SECURITY: `user_email` and `user_id` must never appear in either list below.
 * The comments list query does select user_email, so these field lists are the
 * only thing withholding a commenter's address from an HTTP response — leaking
 * it would be a personal-data breach, and user_id would let a caller correlate
 * commenters with site accounts. Do not add them.
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
        'study_id',
        'full_name',
        'comment_date',
        'comment_text',
        'published',
        'access',
        'language',
    ];

    /**
     * The fields to render in the list response.
     *
     * Note that study_id is absent: the comments list query does not select it.
     *
     * @var    array
     * @since  10.3.4
     */
    protected $fieldsToRenderList = [
        'id',
        'full_name',
        'comment_date',
        'comment_text',
        'published',
        'access',
        'language',
    ];
}

<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\View\Topics;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * JSON:API view for topics.
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
        'topic_text',
        'published',
        'access',
        'language',
        'params',
    ];

    /**
     * The fields to render in the list response.
     *
     * Limited to what the topics list query selects — it does not include
     * access or language.
     *
     * @var    array
     * @since  10.3.4
     */
    protected $fieldsToRenderList = [
        'id',
        'topic_text',
        'published',
    ];
}

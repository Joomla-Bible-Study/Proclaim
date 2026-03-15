<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Api\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * Read-only API controller for series.
 *
 * GET /api/index.php/v1/proclaim/series       — list
 * GET /api/index.php/v1/proclaim/series/:id   — detail
 *
 * @since  10.3.0
 */
class SeriesController extends ApiController
{
    /**
     * The content type for serialization.
     *
     * @var    string
     * @since  10.3.0
     */
    protected $contentType = 'series';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  10.3.0
     */
    protected $default_view = 'series';
}

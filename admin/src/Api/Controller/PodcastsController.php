<?php

/**
 * @package    Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Api\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\MVC\Controller\ApiController;

/**
 * Read-only API controller for podcasts.
 *
 * GET /api/index.php/v1/proclaim/podcasts       — list
 * GET /api/index.php/v1/proclaim/podcasts/:id   — detail
 *
 * @since  10.3.0
 */
class PodcastsController extends ApiController
{
    protected $contentType = 'podcasts';

    protected $default_view = 'podcasts';

    /**
     * @since  10.3.0
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        $map = [
            'podcasts' => 'Cwmpodcasts',
            'podcast'  => 'Cwmpodcast',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }
}

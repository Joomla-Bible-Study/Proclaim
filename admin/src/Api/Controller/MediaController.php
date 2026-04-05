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
 * Read-only API controller for media files.
 *
 * GET /api/index.php/v1/proclaim/media       — list
 * GET /api/index.php/v1/proclaim/media/:id   — detail
 *
 * @since  10.3.0
 */
class MediaController extends ApiController
{
    protected $contentType = 'media';

    protected $default_view = 'media';

    /**
     * @since  10.3.0
     */
    /**
     * List media files — published and archived only.
     *
     * @return  static
     *
     * @since   10.3.0
     */
    public function displayList()
    {
        $this->modelState->set('filter.published', [1, 2]);

        return parent::displayList();
    }

    /**
     * @since  10.3.0
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        $map = [
            'media'      => 'Cwmmediafiles',
            'medium'     => 'Cwmmediafile',
            'mediafile'  => 'Cwmmediafile',
            'mediafiles' => 'Cwmmediafiles',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }
}

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

    /**
     * Normalize API JSON input for the media file model.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The processed data
     *
     * @since   10.3.0
     */
    protected function preprocessSaveData(array $data): array
    {
        // Model expects podcast_id as array for implode to CSV
        if (isset($data['podcast_id']) && \is_string($data['podcast_id'])) {
            $data['podcast_id'] = explode(',', $data['podcast_id']);
        }

        $user = $this->app->getIdentity();

        // Strip internal system fields — prevent mass assignment
        unset($data['asset_id'], $data['checked_out'], $data['checked_out_time'], $data['modified_by']);

        if (isset($data['created_by']) && !$user->authorise('core.admin', 'com_proclaim')) {
            unset($data['created_by']);
        }

        if (!$user->authorise('core.edit.state', 'com_proclaim')) {
            $data['published'] = 0;
        }

        return $data;
    }
}

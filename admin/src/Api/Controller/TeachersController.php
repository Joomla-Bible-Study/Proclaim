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

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Controller\ApiController;

/**
 * API controller for teachers.
 *
 * GET    /api/index.php/v1/proclaim/teachers       — list (published + archived)
 * GET    /api/index.php/v1/proclaim/teachers/:id   — detail
 * POST   /api/index.php/v1/proclaim/teachers       — create
 * PATCH  /api/index.php/v1/proclaim/teachers/:id   — update
 * DELETE /api/index.php/v1/proclaim/teachers/:id   — delete
 *
 * Filters: ?filter[search]=&filter[language]=
 *
 * @since  10.3.0
 */
class TeachersController extends ApiController
{
    protected $contentType = 'teachers';

    protected $default_view = 'teachers';

    /**
     * List teachers — published and archived only.
     *
     * Supports query filters: ?filter[search]=keyword&filter[language]=en-GB
     *
     * @return  static
     *
     * @since   10.3.0
     */
    public function displayList()
    {
        $this->modelState->set('filter.published', [1, 2]);

        $apiFilter = $this->input->get('filter', [], 'array');
        $clean     = InputFilter::getInstance();

        if (\array_key_exists('search', $apiFilter)) {
            $this->modelState->set('filter.search', $clean->clean($apiFilter['search'], 'STRING'));
        }

        if (\array_key_exists('language', $apiFilter)) {
            $this->modelState->set('filter.language', $clean->clean($apiFilter['language'], 'CMD'));
        }

        return parent::displayList();
    }

    /**
     * Get the model, mapping API names to Cwm-prefixed Proclaim classes.
     *
     * @param   string  $name    Model name
     * @param   string  $prefix  Model prefix
     * @param   array   $config  Configuration
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel|false
     *
     * @since   10.3.0
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        $map = [
            'teachers' => 'Cwmteachers',
            'teacher'  => 'Cwmteacher',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Normalize API JSON input for the teacher model.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The processed data
     *
     * @since   10.3.0
     */
    protected function preprocessSaveData(array $data): array
    {
        if (!isset($data['image'])) {
            $data['image'] = '';
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

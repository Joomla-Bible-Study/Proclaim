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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * API controller for locations (campuses).
 *
 * GET    /api/index.php/v1/proclaim/locations       — list (published + archived)
 * GET    /api/index.php/v1/proclaim/locations/:id   — detail
 * POST   /api/index.php/v1/proclaim/locations       — create
 * PATCH  /api/index.php/v1/proclaim/locations/:id   — update
 * DELETE /api/index.php/v1/proclaim/locations/:id   — delete
 *
 * Filters: ?filter[search]=&filter[language]=
 *
 * Note that the list model also applies Proclaim's multi-campus visibility
 * filter, so a caller only ever sees locations they are entitled to.
 *
 * @since  10.3.4
 */
class LocationsController extends AbstractWritableController
{
    protected $contentType = 'locations';

    protected $default_view = 'locations';

    protected $logType = 'location';

    protected $logTitleField = 'location_text';

    /**
     * List locations — published and archived only.
     *
     * @return  static
     *
     * @since   10.3.4
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
     * @return  BaseDatabaseModel|false
     *
     * @since   10.3.4
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        $map = [
            'locations' => 'Cwmlocations',
            'location'  => 'Cwmlocation',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Normalize API JSON input for the location model.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The processed data
     *
     * @since   10.3.4
     */
    protected function preprocessSaveData(array $data): array
    {
        $user = $this->app->getIdentity();

        // Strip internal system fields to prevent mass assignment, plus three
        // that must never be settable over the API: email_to is a notification
        // target (an open door to redirecting site mail), and user_id /
        // contact_id bind a location to a Joomla account.
        unset(
            $data['asset_id'],
            $data['checked_out'],
            $data['checked_out_time'],
            $data['modified_by'],
            $data['email_to'],
            $data['user_id'],
            $data['contact_id']
        );

        if (isset($data['created_by']) && !$user->authorise('core.admin', 'com_proclaim')) {
            unset($data['created_by']);
        }

        if (!$user->authorise('core.edit.state', 'com_proclaim')) {
            $data['published'] = 0;
        }

        return $data;
    }
}

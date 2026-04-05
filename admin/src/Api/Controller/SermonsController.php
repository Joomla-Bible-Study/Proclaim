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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Read-only API controller for sermons (messages/studies).
 *
 * GET /api/index.php/v1/proclaim/sermons       — list
 * GET /api/index.php/v1/proclaim/sermons/:id   — detail
 *
 * Returns published and archived items only (excludes unpublished and trashed).
 *
 * @since  10.3.0
 */
class SermonsController extends ApiController
{
    /**
     * List sermons — published and archived only.
     *
     * Supports query filters: ?filter[teacher]=5&filter[series]=3&filter[search]=keyword
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

        if (\array_key_exists('teacher', $apiFilter)) {
            $this->modelState->set('filter.teacher', $clean->clean($apiFilter['teacher'], 'INT'));
        }

        if (\array_key_exists('series', $apiFilter)) {
            $this->modelState->set('filter.series', $clean->clean($apiFilter['series'], 'INT'));
        }

        if (\array_key_exists('messagetype', $apiFilter)) {
            $this->modelState->set('filter.messagetype', $clean->clean($apiFilter['messagetype'], 'INT'));
        }

        if (\array_key_exists('location', $apiFilter)) {
            $this->modelState->set('filter.location', $clean->clean($apiFilter['location'], 'INT'));
        }

        if (\array_key_exists('year', $apiFilter)) {
            $this->modelState->set('filter.year', $clean->clean($apiFilter['year'], 'INT'));
        }

        if (\array_key_exists('language', $apiFilter)) {
            $this->modelState->set('filter.language', $clean->clean($apiFilter['language'], 'CMD'));
        }

        return parent::displayList();
    }

    /**
     * The content type for serialization.
     *
     * @var    string
     * @since  10.3.0
     */
    protected $contentType = 'sermons';

    /**
     * The default view for the display method.
     *
     * @var    string
     * @since  10.3.0
     */
    protected $default_view = 'sermons';

    /**
     * Get the model, mapping API names to Cwm-prefixed model classes.
     *
     * Joomla's MVCFactory expects Model\SermonsModel but Proclaim uses
     * Model\CwmmessagesModel (Cwm prefix + "messages" entity name).
     *
     * @param   string  $name    Model name
     * @param   string  $prefix  Model prefix
     * @param   array   $config  Configuration
     *
     * @return  BaseDatabaseModel|false
     *
     * @since   10.3.0
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        // Map API names to Cwm-prefixed Proclaim model names
        $map = [
            'sermons' => 'Cwmmessages',
            'sermon'  => 'Cwmmessage',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Normalize API JSON input for the sermon model.
     *
     * Converts clean JSON arrays for scriptures/teachers into the keyed
     * subform format that Joomla's form processing expects.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The processed data
     *
     * @since   10.3.0
     */
    protected function preprocessSaveData(array $data): array
    {
        // Normalize scriptures: [{...}, {...}] → {"scriptures0": {...}, "scriptures1": {...}}
        if (isset($data['scriptures']) && array_is_list($data['scriptures'])) {
            $keyed = [];

            foreach ($data['scriptures'] as $i => $row) {
                $keyed['scriptures' . $i] = $row;
            }

            $data['scriptures'] = $keyed;
        }

        // Normalize teachers: [{...}, {...}] → {"teachers0": {...}, "teachers1": {...}}
        if (isset($data['teachers']) && array_is_list($data['teachers'])) {
            $keyed = [];

            foreach ($data['teachers'] as $i => $row) {
                $keyed['teachers' . $i] = $row;
            }

            $data['teachers'] = $keyed;
        }

        // Default image to empty string to prevent null error in model save
        if (!isset($data['image'])) {
            $data['image'] = '';
        }

        return $this->stripProtectedFields($data);
    }

    /**
     * Remove fields that should not be set directly via API.
     *
     * Prevents mass assignment of ownership, internal state, and
     * system-managed fields. Published state requires core.edit.state.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The cleaned data
     *
     * @since   10.3.0
     */
    private function stripProtectedFields(array $data): array
    {
        $user = $this->app->getIdentity();

        // Never allow setting internal system fields via API
        unset(
            $data['asset_id'],
            $data['checked_out'],
            $data['checked_out_time'],
            $data['modified_by'],
        );

        // Only admins can set created_by (creating on behalf of someone)
        if (isset($data['created_by']) && !$user->authorise('core.admin', 'com_proclaim')) {
            unset($data['created_by']);
        }

        // Restrict published state — users without core.edit.state default to unpublished
        if (!$user->authorise('core.edit.state', 'com_proclaim')) {
            $data['published'] = 0;
        }

        return $data;
    }
}

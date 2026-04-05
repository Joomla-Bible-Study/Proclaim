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
 * API controller for series.
 *
 * GET    /api/index.php/v1/proclaim/series       — list (published + archived)
 * GET    /api/index.php/v1/proclaim/series/:id   — detail
 * POST   /api/index.php/v1/proclaim/series       — create
 * PATCH  /api/index.php/v1/proclaim/series/:id   — update
 * DELETE /api/index.php/v1/proclaim/series/:id   — delete
 *
 * Filters: ?filter[search]=&filter[location]=&filter[language]=
 *
 * Note: Doctrine singularize('series') === 'series', so write operations
 * override add/edit/delete to set $itemModelRequested for the singular model.
 *
 * @since  10.3.0
 */
class SeriesController extends ApiController
{
    protected $contentType = 'series';

    protected $default_view = 'series';

    /**
     * Map API model names to Cwm-prefixed Proclaim classes.
     *
     * Series is special: Doctrine singularize('series') === 'series', so
     * both list and item requests pass 'series'. We track which context
     * we're in via $itemModelRequested (set by displayItem override).
     *
     * @since  10.3.0
     */
    private bool $itemModelRequested = false;

    /**
     * List series — published and archived only.
     *
     * Supports query filters: ?filter[search]=keyword&filter[location]=1&filter[language]=en-GB
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

        if (\array_key_exists('location', $apiFilter)) {
            $this->modelState->set('filter.location', $clean->clean($apiFilter['location'], 'INT'));
        }

        if (\array_key_exists('language', $apiFilter)) {
            $this->modelState->set('filter.language', $clean->clean($apiFilter['language'], 'CMD'));
        }

        return parent::displayList();
    }

    /**
     * @since  10.3.0
     */
    public function displayItem($id = null)
    {
        $this->itemModelRequested = true;

        return parent::displayItem($id);
    }

    /**
     * @since  10.3.0
     */
    public function add()
    {
        $this->itemModelRequested = true;

        return parent::add();
    }

    /**
     * @since  10.3.0
     */
    public function edit()
    {
        $this->itemModelRequested = true;

        return parent::edit();
    }

    /**
     * @since  10.3.0
     */
    public function delete($id = null)
    {
        $this->itemModelRequested = true;

        return parent::delete($id);
    }

    /**
     * @since  10.3.0
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        if (strtolower($name) === 'series') {
            $name = $this->itemModelRequested ? 'Cwmserie' : 'Cwmseries';

            return parent::getModel($name, $prefix, $config);
        }

        $map = [
            'serie' => 'Cwmserie',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Strip protected fields from API input.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The cleaned data
     *
     * @since   10.3.0
     */
    protected function preprocessSaveData(array $data): array
    {
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

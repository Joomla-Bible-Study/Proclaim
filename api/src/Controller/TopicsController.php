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
 * API controller for topics.
 *
 * GET    /api/index.php/v1/proclaim/topics       — list (published + archived)
 * GET    /api/index.php/v1/proclaim/topics/:id   — detail
 * POST   /api/index.php/v1/proclaim/topics       — create
 * PATCH  /api/index.php/v1/proclaim/topics/:id   — update
 * DELETE /api/index.php/v1/proclaim/topics/:id   — delete
 *
 * Filters: ?filter[search]=&filter[language]=
 *
 * @since  __DEPLOY_VERSION__
 */
class TopicsController extends AbstractWritableController
{
    protected $contentType = 'topics';

    protected $default_view = 'topics';

    protected $logType = 'topic';

    protected $logTitleField = 'topic_text';

    /**
     * List topics — published and archived only.
     *
     * @return  static
     *
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public function getModel($name = '', $prefix = '', $config = [])
    {
        $map = [
            'topics' => 'Cwmtopics',
            'topic'  => 'Cwmtopic',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Normalize API JSON input for the topic model.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The processed data
     *
     * @since   __DEPLOY_VERSION__
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

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
 * API controller for message types (study types).
 *
 * GET    /api/index.php/v1/proclaim/messagetypes       — list (published + archived)
 * GET    /api/index.php/v1/proclaim/messagetypes/:id   — detail
 * POST   /api/index.php/v1/proclaim/messagetypes       — create
 * PATCH  /api/index.php/v1/proclaim/messagetypes/:id   — update
 * DELETE /api/index.php/v1/proclaim/messagetypes/:id   — delete
 *
 * Filters: ?filter[search]=
 *
 * @since  10.3.4
 */
class MessagetypesController extends AbstractWritableController
{
    protected $contentType = 'messagetypes';

    protected $default_view = 'messagetypes';

    protected $logType = 'messagetype';

    protected $logTitleField = 'message_type';

    /**
     * List message types — published and archived only.
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
            'messagetypes' => 'Cwmmessagetypes',
            'messagetype'  => 'Cwmmessagetype',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Normalize API JSON input for the message type model.
     *
     * @param   array  $data  The incoming data
     *
     * @return  array  The processed data
     *
     * @since   10.3.4
     */
    protected function preprocessSaveData(array $data): array
    {
        return $this->stripProtectedFields($data);
    }
}

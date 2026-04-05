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

        return $data;
    }
}

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
 * API controller for display templates — READ ONLY.
 *
 * GET /api/index.php/v1/proclaim/templates       — list (published + archived)
 * GET /api/index.php/v1/proclaim/templates/:id   — detail
 *
 * Filters: ?filter[search]=&filter[type]=
 *
 * Templates are queryable but never writable. They are site markup rather than
 * content, so an HTTP write is a defacement vector — it would let a caller
 * change what every visitor sees on the front end. The `params` column is
 * withheld by the view as it carries template configuration, not content.
 *
 * @since  __DEPLOY_VERSION__
 */
class TemplatesController extends AbstractReadOnlyController
{
    protected $contentType = 'templates';

    protected $default_view = 'templates';

    /**
     * List templates — published and archived only.
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

        if (\array_key_exists('type', $apiFilter)) {
            $this->modelState->set('filter.type', $clean->clean($apiFilter['type'], 'CMD'));
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
            'templates' => 'Cwmtemplates',
            'template'  => 'Cwmtemplate',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }
}

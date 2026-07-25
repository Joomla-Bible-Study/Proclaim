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
 * API controller for media servers — READ ONLY.
 *
 * GET /api/index.php/v1/proclaim/servers       — list (published + archived)
 * GET /api/index.php/v1/proclaim/servers/:id   — detail
 *
 * Filters: ?filter[search]=&filter[type]=
 *
 * Servers are queryable but never writable, and the `params` column is withheld
 * entirely by the view: it is the registry that holds each addon's credentials
 * (api_key, client_secret, access_token). Writing servers over HTTP would let a
 * caller overwrite or exfiltrate those, so no write route is registered and
 * AbstractReadOnlyController refuses the verbs outright.
 *
 * @since  __DEPLOY_VERSION__
 */
class ServersController extends AbstractReadOnlyController
{
    protected $contentType = 'servers';

    protected $default_view = 'servers';

    /**
     * List servers — published and archived only.
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
            'servers' => 'Cwmservers',
            'server'  => 'Cwmserver',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }
}

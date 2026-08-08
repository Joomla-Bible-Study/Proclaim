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

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * API controller for component metadata.
 *
 * GET /api/index.php/v1/proclaim/info — the installed Proclaim version.
 *
 * A singleton resource, not a database entity — see CwminfoModel. Only
 * displayItem is ever routed to this controller (see the webservices plugin);
 * there is no list variant and no :id segment.
 *
 * @since  __DEPLOY_VERSION__
 */
class InfoController extends AbstractReadOnlyController
{
    protected $contentType = 'info';

    protected $default_view = 'info';

    /**
     * Get the model, mapping the API name to the Cwm-prefixed Proclaim class.
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
            'info' => 'Cwminfo',
        ];

        $name = $map[strtolower($name)] ?? $name;

        return parent::getModel($name, $prefix, $config);
    }
}

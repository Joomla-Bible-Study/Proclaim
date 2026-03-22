<?php

/**
 * Helper for mod_proclaim — delegates to CwmsermonsModel for query execution.
 *
 * @package     Proclaim
 * @subpackage  mod.proclaim
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @link        https://www.christianwebministries.org
 * */

namespace CWM\Module\Proclaim\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Application\SiteApplication;
use Joomla\Registry\Registry;

/**
 * BibleStudy mod helper
 *
 * Thin adapter that boots the component and delegates to CwmsermonsModel
 * so that the module and component share a single query code path.
 *
 * @package     Proclaim
 * @subpackage  mod.proclaim
 * @since       7.1.0
 */
class ProclaimHelper
{
    /**
     * Get Latest
     *
     * @param   Registry         $params  Merged item params (admin + template + module)
     * @param   SiteApplication  $app     The site application
     *
     * @return array
     *
     * @throws \Exception
     * @since 7.1.0
     */
    public function getLatest(Registry $params, SiteApplication $app): array
    {
        // Boot the component and obtain its MVCFactory
        $component = $app->bootComponent('com_proclaim');

        /** @var \CWM\Component\Proclaim\Site\Model\CwmsermonsModel $model */
        $model = $component->getMVCFactory()
            ->createModel('Cwmsermons', 'Site', ['ignore_request' => true]);

        // Configure model state from module params
        $model->setModuleState($params);

        return $model->getItems();
    }
}

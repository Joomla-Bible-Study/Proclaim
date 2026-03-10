<?php

/**
 * @package     Proclaim.Admin
 * @subpackage  mod_proclaimicon
 *
 * @copyright    (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Module\Proclaimicon\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_proclaimIcon
 *
 * @since  10.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array|false
     *
     * @since   10.0.0
     */
    #[\Override]
    protected function getLayoutData(): array|false
    {
        if (!\defined('CWM_LOADED')) {
            require_once JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';
        }

        $data = parent::getLayoutData();

        if ($data) {
            $data['buttons'] = $this->getHelperFactory()
                ->getHelper('ProclaimIconHelper')
                ->getButtons($data['params'], $this->getApplication());
        }

        return $data;
    }
}

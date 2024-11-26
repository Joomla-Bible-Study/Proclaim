<?php

/**
 * @package     Proclaim.Admin
 * @subpackage  mod_proclaimicon
 *
 * @copyright    (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

namespace CWM\Module\Proclaimicon\Administrator\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

if (!\defined('BIBLESTUDY_COMPONENT_NAME')) {
    require_once $api;
}

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_proclaimicon
 *
 * @since  10.0.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   10.0.0
     */
    protected function getLayoutData(): array
    {
        $data = parent::getLayoutData();

        $data['buttons'] = $this->getHelperFactory()->getHelper('ProclaimIconHelper')->getButtons($data['params'], $this->getApplication());

        return $data;
    }
}

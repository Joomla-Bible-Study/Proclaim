<?php

/**
 * @package         Proclaim.Plugins
 * @subpackage      Task.Proclaim
 * @copyright  (C)  2007 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use CWM\Plugin\Task\Proclaim\Extension\Proclaim;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new Proclaim(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('task', 'proclaim'),
                    JPATH_ROOT . '/media/com_proclaim/backup'
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};

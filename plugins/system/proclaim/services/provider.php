<?php

/**
 * @package     Proclaim
 * @subpackage  plg_system_proclaim
 *
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use CWM\Plugin\System\Proclaim\Extension\Proclaim;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   10.1.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new Proclaim(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'proclaim')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};

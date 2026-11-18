<?php

/**
 * @package    Proclaim.Plugin
 * @subpackage WebServices.Proclaim
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use CWM\Plugin\WebServices\Proclaim\Extension\Proclaim;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   10.3.0
     */
    public function register(Container $container): void
    {
        // CWM Proclaim needs PHP 8.4. Below it, register nothing: Joomla then boots a do-nothing plugin in this one's place,
        // so none of CWM Proclaim's code loads and the site keeps working. Keep 8.4-only
        // syntax out of this file, or the guard itself cannot run.
        if (PHP_VERSION_ID < 80400) {
            return;
        }

        $container->set(
            PluginInterface::class,
            function (Container $container) {
                // CMSPlugin has warned since 6.x that passing a dispatcher to
                // the constructor will not be supported in 7.0, where the class
                // stops implementing DispatcherAwareInterface. Config-first is
                // the supported form on every version we target.
                $plugin = new Proclaim(
                    (array) PluginHelper::getPlugin('webservices', 'proclaim')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};

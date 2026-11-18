<?php

/**
 * @package     Proclaim
 * @subpackage  Plugin.Schemaorg.Proclaim
 *
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use CWM\Plugin\Schemaorg\Proclaim\Extension\Proclaim;
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
    public function register(Container $container)
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
                $plugin = new Proclaim(
                    (array) PluginHelper::getPlugin('schemaorg', 'proclaim')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};

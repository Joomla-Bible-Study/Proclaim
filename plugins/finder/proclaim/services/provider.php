<?php

/**
 * @package        Proclaim.Finder
 * @subpackage     plg_finder_proclaim
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

\defined('_JEXEC') or die;

use CWM\Plugin\Finder\Proclaim\Extension\Proclaim;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
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
     * @since   4.3.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                // Config first, then the database: Joomla 7 types the finder
                // Adapter's second parameter as ?DatabaseInterface, so the old
                // dispatcher-first form fatals before the constructor body runs.
                // CMSPlugin has warned since 6.x that passing a dispatcher will
                // not be supported in 7.0, and core's own finder plugins build
                // this way. setDatabase() stays for 5.x/6.x, where the
                // constructor takes no database argument.
                $plugin = new Proclaim(
                    (array) PluginHelper::getPlugin('finder', 'proclaim'),
                    $container->get(DatabaseInterface::class)
                );
                $plugin->setApplication(Factory::getApplication());
                $plugin->setDatabase($container->get(DatabaseInterface::class));

                return $plugin;
            }
        );
    }
};

<?php

/**
 * @package         Proclaim
 * @subpackage      mod_proclaim_podcast
 * @copyright   (C) 2026 CWM Team All rights reserved
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 * @link            https://www.christianwebministries.org
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The Proclaim module service provider.
 *
 * @since  4.2.0
 */
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
        // CWM Proclaim needs PHP 8.3. Below it, register nothing: Joomla then renders nothing for this module,
        // so none of CWM Proclaim's code loads and the site keeps working. Keep 8.3-only
        // syntax out of this file, or the guard itself cannot run.
        if (PHP_VERSION_ID < 80300) {
            return;
        }

        $container->registerServiceProvider(new ModuleDispatcherFactory('\\CWM\\Module\\ProclaimPodcast'));

        $container->registerServiceProvider(new Module());
    }
};

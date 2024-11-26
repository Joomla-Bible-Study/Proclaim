<?php

/**
 * @package     Proclaim.Admin
 * @subpackage  mod_quickicon
 *
 * @copyright  (C) 2007 CWM Team All rights reserved
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       https://www.christianwebministries.org
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\HelperFactory;
use Joomla\CMS\Extension\Service\Provider\Module;
use Joomla\CMS\Extension\Service\Provider\ModuleDispatcherFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The proclaimicon module service provider.
 *
 * @since  10.0.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   10.0.0
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new ModuleDispatcherFactory('\\CWM\\Module\\Proclaimicon'));
        $container->registerServiceProvider(new HelperFactory('\\CWM\\Module\\Proclaimicon\\Administrator\\Helper'));

        $container->registerServiceProvider(new Module());
    }
};

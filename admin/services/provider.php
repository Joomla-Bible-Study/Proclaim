<?php

/**
 * @package        Proclaim.Admin
 * @copyright  (C) 2025 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

\defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Extension\ProclaimComponent;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * To Proclaim service provider.
 *
 * @since  4.0.0
 */
return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new CategoryFactory('\\CWM\\Component\\Proclaim'));
        $container->registerServiceProvider(new MVCFactory('\\CWM\\Component\\Proclaim'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\CWM\\Component\\Proclaim'));
        $container->registerServiceProvider(new RouterFactory('\\CWM\\Component\\Proclaim'));

        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new ProclaimComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};

<?php

/**
 * @package        Proclaim.Admin
 * @copyright  (C) 2026 CWM Team All rights reserved
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @link           https://www.christianwebministries.org
 */

\defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Extension\ProclaimComponent;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\Language\Text;
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
                // Gate: Proclaim requires PHP 8.3+. If running on an older version,
                // return a minimal stub so the site doesn't crash with a parse error.
                if (PHP_VERSION_ID < 80300) {
                    try {
                        $app = Factory::getApplication();

                        if ($app->isClient('administrator')) {
                            $app->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);
                            $app->enqueueMessage(
                                Text::sprintf(
                                    'COM_PROCLAIM_ERROR_PHP_VERSION',
                                    '8.3.0',
                                    PHP_VERSION
                                ),
                                'error'
                            );
                        }
                    } catch (\Exception $e) {
                        // Silently fail if we can't enqueue the message
                    }

                    // Return a bare MVCComponent that won't trigger PHP 8.3 syntax errors
                    $stub = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));
                    $stub->setMVCFactory($container->get(MVCFactoryInterface::class));

                    return $stub;
                }

                $component = new ProclaimComponent($container->get(ComponentDispatcherFactoryInterface::class));

                $component->setRegistry($container->get(Registry::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setRouterFactory($container->get(RouterFactoryInterface::class));

                return $component;
            }
        );
    }
};

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
        // CWM Proclaim needs PHP 8.3, the floor its installer and update feed hold too.
        // Below it, its classes may not even parse, and a site that ends up there,
        // say after its host changes PHP, must keep a working Joomla. So none of
        // CWM Proclaim's code loads: administrators get a banner, and the component
        // answers with it instead of running. Keep 8.3-only syntax out of this
        // file, or the guard itself cannot run.
        if (PHP_VERSION_ID < 80300) {
            $this->standDown($container);

            return;
        }

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

    /**
     * Stand CWM Proclaim down on a PHP below its floor, without breaking the site.
     *
     * Registers Joomla's own LegacyComponent, which answers every service a booted
     * component is asked for without loading any of CWM Proclaim's classes, and replaces
     * only its dispatcher. In the backend that dispatcher returns to the
     * dashboard, where the banner says why; on the site and the API it answers 503
     * with an empty body. Nothing is thrown, so there is no error page.
     *
     * @param   Container  $container  The component's container.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function standDown(Container $container): void
    {
        $this->postPhpBanner();

        $container->set(
            \Joomla\CMS\Extension\ComponentInterface::class,
            static fn () => new class ('com_proclaim') extends \Joomla\CMS\Extension\LegacyComponent {
                public function getDispatcher(
                    \Joomla\CMS\Application\CMSApplicationInterface $application
                ): \Joomla\CMS\Dispatcher\DispatcherInterface {
                    return new class ($application) implements \Joomla\CMS\Dispatcher\DispatcherInterface {
                        public function __construct(
                            private \Joomla\CMS\Application\CMSApplicationInterface $application
                        ) {
                        }

                        public function dispatch(): void
                        {
                            if ($this->application->isClient('administrator')) {
                                $this->application->redirect('index.php');
                            }

                            $this->application->setHeader('status', '503', true);
                        }
                    };
                }
            }
        );
    }

    /**
     * Tell administrators that CWM Proclaim is switched off, and why.
     *
     * Backend only: a visitor can do nothing about the server's PHP. Posted once
     * per request, by whichever of CWM Proclaim's extensions boots first.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function postPhpBanner(): void
    {
        try {
            $app = \Joomla\CMS\Factory::getApplication();
        } catch (\Throwable) {
            // No application yet, e.g. the CLI before it has booted.
            return;
        }

        if (!$app->isClient('administrator')) {
            return;
        }

        $language = $app->getLanguage();

        if (!$language->load('com_proclaim', JPATH_ADMINISTRATOR)) {
            $language->load('com_proclaim', JPATH_ADMINISTRATOR . '/components/com_proclaim');
        }

        $message = \Joomla\CMS\Language\Text::sprintf('COM_PROCLAIM_ERROR_PHP_VERSION', '8.3.0', PHP_VERSION);

        foreach ($app->getMessageQueue() as $queued) {
            if (($queued['message'] ?? null) === $message) {
                return;
            }
        }

        $app->enqueueMessage($message, 'error');
    }
};

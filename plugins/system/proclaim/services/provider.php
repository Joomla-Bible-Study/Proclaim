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
        // CWM Proclaim needs PHP 8.3. Below it, register nothing, so none of its code
        // loads and the site keeps working. This plugin boots on every request,
        // which makes it the one that tells administrators why. Keep 8.3-only
        // syntax out of this file, or the guard itself cannot run.
        if (PHP_VERSION_ID < 80300) {
            $this->postPhpBanner();

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
                    (array) PluginHelper::getPlugin('system', 'proclaim')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
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
     * @since   10.7.8
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

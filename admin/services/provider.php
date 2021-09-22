<?php
/**
 * @package     Mywalks.Administrator
 * @subpackage  com_mywalks
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\CategoryFactory;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use CWM\Component\BibleStudy\Administrator\Extension\ProclaimComponent;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The Proclaim service provider.
 *
 * @since  4.0.0
 */
return new class implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function register(Container $container)
	{
		$container->registerServiceProvider(new CategoryFactory('\\CWM\\Component\\BibleStudy'));
		$container->registerServiceProvider(new MVCFactory('\\CWM\\Component\\BibleStudy'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('\\CWM\\Component\\BibleStudy'));
		$container->registerServiceProvider(new RouterFactory('\\CWM\\Component\\BibleStudy'));
		$container->set(
			ComponentInterface::class,
			function (Container $container) {
				$component = new ProclaimComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setRegistry($container->get(Registry::class));
				$component->setMVCFactory($container->get(MVCFactoryInterface::class));
				$component->setRouterFactory($container->get(RouterFactoryInterface::class));

				// Always load JBSM API if it exists.
				$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

				if (file_exists($api))
				{
					require_once $api;
				}

				return $component;
			}
		);
	}
};

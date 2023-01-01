<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_proclaim
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Language\Text;

/**
 * ComponentDispatcher class for com_proclaim
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * @var string
	 * @since 10.0.0
	 */
	protected string $defaultController = 'cwmlandingpage';

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch()
	{
		$this->applyViewAndController();

		if ($this->input->get('view') === 'cwmlandingpage' && $this->input->get('layout') === 'modal')
		{
			if (!$this->app->getIdentity()->authorise('core.create', 'com_proclaim'))
			{
				$this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'warning');

				return;
			}

			$this->app->getLanguage()->load('com_proclaim', JPATH_ADMINISTRATOR);
		}

		parent::dispatch();
	}

	/**
	 * Update View and Controller to work with Namespace Case-Sensitive
	 *
	 * @return void
	 * @since 10.0.0
	 */
	protected function applyViewAndController(): void
	{
		$controller = $this->input->getCmd('controller', null);
		$view       = $this->input->getCmd('view', null);
		$task       = $this->input->getCmd('task', 'display');

		if (str_contains($task, '.'))
		{
			// Explode the controller.task command.
			[$controller, $task] = explode('.', $task);
		}

		if (empty($controller) && empty($view))
		{
			$controller = $this->defaultController;
			$view       = $this->defaultController;
		}
		elseif (!empty($controller) && empty($view))
		{
			$view = $controller;
		}

		if ($view === 'featured')
		{
			$view = 'cwmsermons';
		}

		$view = $this->mapView($view);

		$this->input->set('view', $view);
		$this->input->set('controller', $controller);
		$this->input->set('task', $task);
	}

	/**
	 * System to set all urls to lower case
	 *
	 * @param   string  $view  URL View String
	 *
	 * @return string
	 *
	 * @since 10.0.0
	 */
	private function mapView(string $view): string
	{
		return strtolower($view);
	}
}

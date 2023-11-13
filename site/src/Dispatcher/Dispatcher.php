<?php
/**
 * @package     Proclaim.Site
 * @subpackage  com_proclaim
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Site\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CwmproclaimHelper;
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
	protected string $defaultController ='DisplayController';

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   4.0.0
	 */
	public function dispatch(): void
	{
		CwmproclaimHelper::applyViewAndController($this->defaultController);

		// Always load Proclaim API if it exists.
		$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

		if (file_exists($api))
		{
			require_once $api;
		}

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
}

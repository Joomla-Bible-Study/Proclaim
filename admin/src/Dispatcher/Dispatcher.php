<?php
/**
 * @package         Proclaim.Admin
 * @subpackage      com_proclaim
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CWMProclaimHelper;
use Joomla\CMS\Dispatcher\ComponentDispatcher;

/**
 * ComponentDispatcher class for com_users
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * @var string
	 * @since 10.0.0
	 */
	protected string $defaultController = 'cwmcpanel';

	/**
	 * @return void
	 *
	 * @throws \Throwable
	 * @since 10.0.0
	 */
	public function dispatch(): void
	{
		CWMProclaimHelper::applyViewAndController($this->defaultController);

		// Always load Proclaim API if it exists.
		$api = JPATH_ADMINISTRATOR . '/components/com_proclaim/api.php';

		if (file_exists($api))
		{
			require_once $api;
		}

		// Fix for controller name
		if ($this->input->get('controller') === 'cwmassets')
		{
			$this->input->set('controller', 'CWMAssets');
		}

		parent::dispatch();
	}

	/**
	 * Override checkAccess to allow users edit profile without having to have core.manager permission
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	protected function checkAccess(): void
	{
		$task         = $this->input->getCmd('task');
		$view         = $this->input->getCmd('view');
		$layout       = $this->input->getCmd('layout');
		$allowedTasks = ['user.edit', 'user.apply', 'user.save', 'user.cancel'];

		// Allow users to edit their own account
		if (in_array($task, $allowedTasks, true) || ($view === 'user' && $layout === 'edit'))
		{
			$user = $this->app->getIdentity();
			$id   = $this->input->getInt('id');

			if ((int) $user->id === $id)
			{
				return;
			}
		}

		parent::checkAccess();
	}
}

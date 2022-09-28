<?php
/**
 * @package         Joomla.Administrator
 * @subpackage      com_users
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace CWM\Component\Proclaim\Administrator\Dispatcher;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

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
	protected $defaultController = 'CWMCpanel';

	/**
	 * Array of Views to namespace names
	 * @var array
	 * @since 10.0.0
	 */
	protected array $viewMap = [
		'cwmadmin'         => 'CWMAdmin',
		'cwmachive'        => 'CWMAchive',
		'cwmassets'        => 'CWMAssets',
		'cwmbackup'        => 'CWMBackup',
		'cwmcomment'       => 'CWMComment',
		'cwmcomments'      => 'CWMComments',
		'cwmcpanel'        => 'CWMCpanel',
		'cwmdir'           => 'CWMDir',
		'cwminstall'       => 'CWMInstall',
		'cwmlocation'      => 'CWMLocation',
		'cwmlocations'     => 'CWMLocations',
		'cwmmediafile'     => 'CWMMediaFile',
		'cwmmediafiles'    => 'CWMMediaFiles',
		'cwmmessage'       => 'CWMMessage',
		'cwmmessages'      => 'CWMMessages',
		'cwmmessagetype'   => 'CWMMessageType',
		'cwmmessagetypes'  => 'CWMMessageTypes',
		'cwmmigrate'       => 'CWMMigrate',
		'cwmpodcast'       => 'CWMPodcast',
		'cwmpodcasts'      => 'CWMPodcasts',
		'cwmserie'         => 'CWMSerie',
		'cwmseries'        => 'CWMSeries',
		'cwmserver'        => 'CWMServer',
		'cwmservers'       => 'CWMServers',
		'cwmteacher'       => 'CWMTeacher',
		'cwmteachers'      => 'CWMTeachers',
		'cwmtemplatecode'  => 'CWMTemplateCode',
		'cwmtemplatecodes' => 'CWMTemplateCodes',
		'cwmtemplate'      => 'CWMTemplate',
		'cwmtemplates'     => 'CWMTemplates',
		'cwmtopic'         => 'CWMTopic',
		'cwmtopics'        => 'CWMTopics',
		'cwmupload'        => 'CWMUpload',
	];

	/**
	 * @return void
	 *
	 * @throws \Throwable
	 * @since 10.0.0
	 */
	public function dispatch(): void
	{
		$this->applyViewAndController();

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
		elseif (empty($controller) && !empty($view))
		{
			$controller = $view;
		}
		elseif (!empty($controller) && empty($view))
		{
			$view = $controller;
		}

		$controller = $this->mapView($controller);
		$view       = $this->mapView($view);

		$this->input->set('view', $view);
		$this->input->set('controller', $controller);
		$this->input->set('task', $task);
	}

	private function mapView(string $view)
	{
		$view = strtolower($view);

		return $this->viewMap[$view] ?? $view;
	}
}

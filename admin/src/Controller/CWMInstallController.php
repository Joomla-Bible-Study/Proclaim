<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Controller;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Model\CWMInstallModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Session\Session;

/**
 * Controller for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMInstallController extends BaseController
{
	/**
	 * @var string
	 * @since 7.0.0
	 */
	public string $modelName;

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected string $view_list = 'CWMInstall';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'CWMInstall';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  BaseDatabaseModel  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'CWMInstall', $prefix = '', $config = array('ignore_request' => true)): BaseDatabaseModel
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Constructor.
	 *
	 * @param   string  $task  An optional associative array of configuration settings.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function execute($task): void
	{
		if ($task !== 'run' && $task !== 'clear' && $task !== 'browse')
		{
			$task = 'browse';
		}

		parent::execute($task);
	}

	/**
	 * Start of installer display hook.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function browse(): void
	{
		// Check for request forgeries.
		(Session::checkToken('get') || Session::checkToken()) or jexit(Text::_('JINVALID_TOKEN'));

		$app = Factory::getApplication();
		$session = $app->getSession();
		$stack = $session->get('migration_stack', '', 'CWM');

		if (empty($stack) || !is_array($stack))
		{
			CWMHelper::clearcache('site');
			CWMHelper::clearcache('administrator');
			$session->set('migration_stack', '', 'CWM');

			$model = new CWMInstallModel;
			$state = $model->startScanning();
			$app->input->set('scanstate', $state);
			$app->input->set('view', 'CWMInstall');

			$this->display(false);
		}
		else
		{
			$this->run();
		}
	}

	/**
	 * Clear and start of installer display hook.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function clear(): void
	{
		// Check for request forgeries.
		(Session::checkToken('get') || Session::checkToken()) or jexit(Text::_('JINVALID_TOKEN'));

		CWMHelper::clearcache('site');
		CWMHelper::clearcache('administrator');
		$session = Factory::getApplication()->getSession();
		$session->set('migration_stack', '', 'CWM');
		$app = Factory::getApplication();
		$app->input->set('view', 'CWMInstall');

		$this->display(false);
	}

	/**
	 * Run function loop
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function run(): void
	{
		// Check for request forgeries.
		(Session::checkToken('get') || Session::checkToken()) or jexit(Text::_('JINVALID_TOKEN'));

		$app   = Factory::getApplication();
		$model = new CWMInstallModel;
		$state = $model->run();
		$app->input->set('scanstate', $state);
		$app->input->set('view', 'CWMInstall');

		$this->display(false);
	}
}

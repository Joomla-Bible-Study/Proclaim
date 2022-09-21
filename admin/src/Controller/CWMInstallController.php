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

// No Direct Access
defined('_JEXEC') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Model\CWMInstallModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * Controller for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMInstallController extends FormController
{
	/**
	 * @var string
	 * @since 7.0.0
	 */
	public string $modelName;

	/**
	 * The context for storing internal data, e.g. record.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $context = 'install';

	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $view_item = 'install';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $view_list = 'install';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @throws \Exception
	 * @since 1.5
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'install';
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
	public function execute($task)
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
	public function browse()
	{
		$app = Factory::getApplication();
		$session = $app->getSession();
		$stack = $session->get('migration_stack', '', 'CWM');

		if (empty($stack))
		{
			CWMHelper::clearcache('site');
			CWMHelper::clearcache('administrator');
			$session->set('migration_stack', '', 'CWM');

			$model = new CWMInstallModel;
			$state = $model->startScanning();
			$app->input->set('scanstate', $state);
			$app->input->set('view', 'install');

			$this->display(false);
		}
		else
		{
			$this->clear();
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
	public function clear()
	{
		CWMHelper::clearcache('site');
		CWMHelper::clearcache('administrator');
		$session = Factory::getApplication()->getSession();
		$session->set('migration_stack', '', 'CWM');
		$this->browse();
	}

	/**
	 * Run function loop
	 *
	 * @return void
	 *
	 * @throws \Exception
	 * @since 9.0.0
	 */
	public function run()
	{
		$app   = Factory::getApplication();
		$model = new CWMInstallModel;
		$state = $model->run();
		$app->input->set('scanstate', $state);
		$app->input->set('view', 'install');

		$this->display(false);
	}
}

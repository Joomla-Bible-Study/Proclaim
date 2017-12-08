<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Admin
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class BiblestudyControllerInstall extends JControllerForm
{
	public $modelName;

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
	 * @since 9.0.0
	 */
	public function execute($task)
	{
		if ($task != 'run' && $task != 'clear' && $task != 'browse')
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
	 * @since 9.0.0
	 */
	public function browse()
	{
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$stack = $session->get('migration_stack', '', 'JBSM');

		if (empty($stack))
		{
			JBSMHelper::clearcache('site');
			JBSMHelper::clearcache('admin');
			$session->set('migration_stack', '', 'JBSM');

			/** @var BibleStudyModelInstall $model */
			$model = $this->getModel('install');
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
	 * @since 9.0.0
	 */
	public function clear()
	{
		JBSMHelper::clearcache('site');
		JBSMHelper::clearcache('admin');
		$session = JFactory::getSession();
		$session->set('migration_stack', '', 'JBSM');
		$this->browse();
	}

	/**
	 * Run function loop
	 *
	 * @return void
	 *
	 * @since 9.0.0
	 */
	public function run()
	{
		$app   = JFactory::getApplication();
		/** @var BibleStudyModelInstall $model */
		$model = $this->getModel('install');
		$state = $model->run();
		$app->input->set('scanstate', $state);
		$app->input->set('view', 'install');

		$this->display(false);
	}
}

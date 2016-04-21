<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * Controller for Admin
 *
 * @package  BibleStudy.Admin
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
	 */
	public function execute($task)
	{
		if ($task != 'run')
		{
			$task = 'browse';
		}
		parent::execute($task);
	}

	/**
	 * Start of installer display hook.
	 *
	 * @return void
	 */
	public function browse()
	{
		$app = JFactory::getApplication();
		/** @var BibleStudyModelInstall $model */
		$model = $this->getModel('install');
		$state = $model->startScanning();
		$app->input->set('scanstate', $state);
		$app->input->set('view', 'install');

		$this->display(false);
	}

	/**
	 * Run function loop
	 *
	 * @return void
	 *
	 * @since 8.0.0
	 */
	public function run()
	{
		$app   = JFactory::getApplication();
		$model = $this->getModel('install');
		$state = $model->run();
		$app->input->set('scanstate', $state);
		$app->input->set('view', 'install');

		$this->display(false);
	}
}

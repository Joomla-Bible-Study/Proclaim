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
 * Controller for Assets
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class BiblestudyControllerAssets extends JControllerForm
{
	public $modelName;

	/**
	 * The context for storing internal data, e.g. record.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $context = 'assets';

	/**
	 * The URL view item variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $view_item = 'assets';

	/**
	 * The URL view list variable.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $view_list = 'assets';

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

		$this->modelName = 'assets';
	}

	/**
	 * Constructor.
	 *
	 * @param   string  $task  An optional associative array of configuration settings.
	 *
	 * @return void
	 *
	 * @since 1.5
	 */
	public function execute($task)
	{
		if ($task != 'run' && $task != 'checkassets' && $task != 'clear')
		{
			$task = 'browse';
		}

		parent::execute($task);
	}

	/**
	 * Check Assets
	 *
	 * @return void
	 *
	 * @since 8.0.0
	 */
	public function checkassets()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var BibleStudyModelAssets $model */
		$model = $this->getModel('assets');
		$checkassets = $model->checkAssets();
		$session = JFactory::getSession();
		$session->set('assat_stack', '', 'JBSM');
		$session->set('checkassets', $checkassets, 'JBSM');
		parent::display();
	}

	/**
	 * Start of installer display hook.
	 *
	 * @return void
	 *
	 * @since 8.0.0
	 */
	public function browse()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$stack = $session->get('asset_stack', '', 'JBSM');

		if (empty($stack))
		{
			JBSMHelper::clearcache('site');
			JBSMHelper::clearcache('admin');
			$session->set('asset_stack', '', 'JBSM');

			/** @var BibleStudyModelAssets $model */
			$model = $this->getModel('assets');
			$state = $model->startScanning();
			$app->input->set('scanstate', $state);
			$app->input->set('view', 'assets');

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
	 * @since 9.0.2
	 */
	public function clear()
	{
		// Check for request forgeries.
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		JBSMHelper::clearcache('admin');
		JBSMHelper::clearcache('site');
		$session = JFactory::getSession();
		$session->set('assat_stack', '', 'JBSM');
		$app = JFactory::getApplication();
		$app->input->set('view', 'assets');
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
		// Check for request forgeries.
		JSession::checkToken('get') or JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app   = JFactory::getApplication();
		/** @var BibleStudyModelAssets $model */
		$model = $this->getModel('assets');
		$state = $model->run();
		$app->input->set('scanstate', $state);
		$app->input->set('view', 'assets');

		$this->display(false);
	}
}

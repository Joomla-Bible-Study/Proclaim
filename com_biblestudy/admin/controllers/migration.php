<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controller');
jimport('joomla.html.parameter');

JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');
JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');
JLoader::register('fixJBSAssets', dirname(__FILE__) . '/lib/biblestudy.assets.php');

/**
 * JBS Export Migration Controller
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 */
class BiblestudyControllerMigration extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->modelName = 'migration';
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
	 * Constructor.
	 *
	 * @return void
	 */
	public function browse()
	{
		$model = $this->getModel('migration');
		$state = $model->startScanning();
		$model->setState('scanstate', $state);

		$this->display(false);
	}

	/**
	 * Run Function
	 *
	 * @return void
	 *
	 * @since 8.0.0
	 */
	public function run()
	{
		$app = JFactory::getApplication();
		$model = $this->getModel('migration');
		$state = $model->run(true);
		$model->setState('scanstate', $state);

		if ($app->input->get('jbsimport', 0))
		{
			$app->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . JText::_('JBS_IBM_REVIEW_ADMIN_TEMPLATE'), 'message');

			// Final step is to fix assets
			$assets = new FixJBSAssets;
			$assets->fixAssets();
			$installer = new Com_BiblestudyInstallerScript;
			$installer->deleteUnexistingFiles();
			$installer->fixMenus();
			$installer->fixImagePaths();
			$installer->fixemptyaccess();
			$installer->fixemptylanguage();
		}
		else
		{
			JBSMDbHelper::resetdb();
			$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_MIGRATED'), 'warning');
		}

		$this->display(false);
	}

}

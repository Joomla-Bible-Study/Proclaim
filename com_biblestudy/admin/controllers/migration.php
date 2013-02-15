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

// todo: need to finish the Jloader
include_once BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.restore.php';
include_once BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.backup.php';
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
	 * Start the Update
	 *
	 * @return void
	 */
	public function run()
	{
		$model = $this->getModel('migration');
		//$state = $model->run(true);
		var_dump($model->getState('scanstate'));
		//$model->setState('scanstate', $state);

		//$this->display(false);
	}

	/**
	 * Import function from the backup page
	 *
	 * @return void
	 *
	 * @since 7.1.0
	 */
	public function import()
	{
		$app    = JFactory::getApplication();
		$import = new JBSImport;
		$parent = false;
		$result = $import->importdb($parent);

		if ($result === true)
		{
			$app->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '');
		}
		$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1');
	}

	/**
	 * Do the import
	 *
	 * @param   boolean  $parent     Source of info
	 * @param   boolean  $cachable   ?
	 * @param   boolean  $urlparams  Description
	 *
	 * @return void
	 */
	public function doimport($parent = true, $cachable = false, $urlparams = false)
	{
		$copysuccess = false;
		$result      = null;

		// This should be where the form admin/form_migrate comes to with either the file select box or the tmp folder input field
		$app   = JFactory::getApplication();
		$input = new JInput;
		$input->set('view', $input->get('view', 'admin', 'cmd'));

		// Add commands to move tables from old prefix to new
		$oldprefix = $input->get('oldprefix', '', 'string');

		if ($oldprefix)
		{
			if ($this->copyTables($oldprefix))
			{
				$copysuccess = 1;
			}
			else
			{
				$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_COPIED'), 'worning');
				$copysuccess = false;
			}
		}
		else
		{
			$import = new JBSImport;
			$result = $import->importdb($parent);
		}
		if ($result || $copysuccess)
		{
			$model = $this->getModel('migration');
			$state = $model->run(true);
			$model->setState('scanstate', $state);

			if ($state)
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
				$input->set('migrationdone', '1');
			}
			elseif (!$copysuccess)
			{
				JBSMDbHelper::resetdb();
			}
			else
			{
				JBSMDbHelper::resetdb();
				$app->enqueueMessage(JText::_('JBS_CMN_DATABASE_NOT_MIGRATED'), 'warning');
			}
		}
		//$this->setRedirect('index.php?option=com_biblestudy&task=admin.edit&id=1');
	}

	/**
	 * Copy Old Tables to new Joomla! Tables
	 *
	 * @param   string  $oldprefix  ?
	 *
	 * @return boolean
	 */
	public function copyTables($oldprefix)
	{
		// Create table tablename_new like tablename; -> this will copy the structure...
		// Insert into tablename_new select * from tablename; -> this would copy all the data
		$db     = JFactory::getDBO();
		$tables = $db->getTableList();
		$prefix = $db->getPrefix();

		foreach ($tables as $table)
		{
			$isjbs = substr_count($table, $oldprefix . 'bsms');

			if ($isjbs)
			{
				$oldlength       = strlen($oldprefix);
				$newsubtablename = substr($table, $oldlength);
				$newtablename    = $prefix . $newsubtablename;
				$query           = 'DROP TABLE IF EXISTS ' . $newtablename;

				if (!JBSMDbHelper::performdb($query))
				{
					return false;
				}
				$query = 'CREATE TABLE ' . $newtablename . ' LIKE ' . $table;

				if (!JBSMDbHelper::performdb($query))
				{
					return false;
				}
				$query = 'INSERT INTO ' . $newtablename . ' SELECT * FROM ' . $table;

				if (!JBSMDbHelper::performdb($query))
				{
					return false;
				}
			}
		}

		return true;
	}

}

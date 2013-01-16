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
include_once BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.restore.php';
include_once BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.backup.php';
include_once BIBLESTUDY_PATH_ADMIN_LIB . DIRECTORY_SEPARATOR . 'biblestudy.migrate.php';
JLoader::register('Com_BiblestudyInstallerScript', JPATH_ADMINISTRATOR . '/components/com_biblestudy/biblestudy.script.php');
JLoader::register('JBSMDbHelper', JPATH_ADMINISTRATOR . '/components/com_biblestudy/helpers/dbhelper.php');
JLoader::register('fixJBSAssets', dirname(__FILE__) . '/lib/biblestudy.assets.php');

/**
 * JBS Export Migration Controller
 *
 * @package  BibleStudy.Admin
 * @since    7.1.0
 *
 * @todo     need to redo to us progress bare system.
 */
class BiblestudyControllerMigration extends JControllerLegacy
{

	/**
	 * Method to display the view
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return void
	 *
	 * @access    public
	 */
	public function display($cachable = false, $urlparams = array())
	{

		$input = new JInput;
		$input->set('view', 'admin');
		$app = JFactory::getApplication();
		$input->set('migrationdone', '0');
		$task      = $input->get('task');
		$oldprefix = $input->get('oldprefix', '');
		$run       = $input->get('run', '0', 'int');

		if ($task == 'export' && ($run == 1 || $run == 2))
		{
			$export = new JBSExport;

			if (!$result = $export->exportdb($run))
			{
				$msg = JText::_('JBS_CMN_OPERATION_FAILED');
				$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
			}
			elseif ($run == 2)
			{
				if (!$result)
				{
					$msg = $result;
				}
				else
				{
					$msg = JText::_('JBS_CMN_OPERATION_SUCCESSFUL');
				}
				$this->setRedirect('index.php?option=com_biblestudy&view=admin&layout=edit&id=1', $msg);
			}
		}

		if ($task == 'migrate' && $run == 1 && !$oldprefix)
		{

			$migrate   = new JBSMigrate;
			$migration = $migrate->migrate();

			if ($migration)
			{
				$app->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . '', 'message');
				$input->set('migrationdone', '1');

				// --$input->set('jbsmessages', $jbsmessages);
			}
			else
			{
				$app->enqueueMessage(JText::_('JBS_CMN_OPERATION_FAILED'), 'warning');
			}
		}

		if ($task == 'import')
		{
			$this->import();
		}
		parent::display();
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
			$migrate   = new JBSMigrate;
			$migration = $migrate->migrate();

			if ($migration)
			{
				$app->enqueueMessage('' . JText::_('JBS_CMN_OPERATION_SUCCESSFUL') . JText::_('JBS_IBM_REVIEW_ADMIN_TEMPLATE'), 'message');

				// Final step is to fix assets
				$this->fixAssets();
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
		$this->setRedirect('index.php?option=com_biblestudy&task=admin.edit&id=1');
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

	/**
	 * Fix Assets Table
	 *
	 * @return boolean
	 */
	public function fixAssets()
	{
		$asset = new fixJBSAssets;
		$asset->fixAssets();

		return true;
	}

}

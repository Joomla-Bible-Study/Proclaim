<?php
/**
 * @version $Id: controller.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/
// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
include_once(JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'restore.php');
include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jbsmigration'.DS.'backup.php');
include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_jbsmigration'.DS.'migrate.php');
/**
 * JBS Export Migration Controller
 *
 *
 */
class jbsmigrationController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{
		$application = JFactory::getApplication();
		JRequest::setVar('migrationdone','0','get');
		$task = JRequest::getWord('task','','get');
		$run = 0;

		$run = JRequest::getInt('run','','get');

		$import = JRequest::getVar('file','','post');
		if ($task == 'export' && $run == 1)
		{
			$export = new JBSExport();
			$result = $export->exportdb();
			if ($result){
				$application->enqueueMessage( ''. JText::_('JBS_EI_SUCCESS') .'' ) ;
			}
			else
			{
				$application->enqueueMessage( ''. JText::_('JBS_EI_FAILURE') .'' ) ;
			}
		}
		if ($task == 'migrate' && $run == 1)
		{
			$migrate = new JBSMigrate();
			$migration = $migrate->migrate();
			if ($migration)
			{
				$application->enqueueMessage( ''. JText::_('JBS_EI_SUCCESS') .'' ) ;
				JRequest::setVar('migrationdone','1','get');
			}
			else
			{
				$application->enqueueMessage( ''. JText::_('JBS_EI_FAILURE') .'' ) ;
			}
		}
		parent::display();
	}

	function doimport()
	{

		$application = JFactory::getApplication();
		$import = new JBSImport();
		$result = $import->importdb();
		if ($result)
		{
			$migrate = new JBSMigrate();
			$migration = $migrate->migrate();
			if ($migration)
			{
				$application->enqueueMessage( ''. JText::_('JBS_EI_SUCCESS') .'' ) ;
				JRequest::setVar('migrationdone','1','get');
			}
			else
			{
				$application->enqueueMessage( ''. JText::_('JBS_EI_FAILURE') .'' ) ;
			}
			$application->enqueueMessage( ''. JText::_('JBS_EI_SUCCESS_REVIEW_ADMIN_TEMPLATE') .'' ) ;
			JRequest::setVar('migrationdone','1','get');
		}
		else
		{
			$application->enqueueMessage( ''. JText::_('JBS_EI_FAILURE') .'' ) ;
		}

		parent::display();
	}

}

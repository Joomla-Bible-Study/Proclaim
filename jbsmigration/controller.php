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
        $oldprefix = JRequest::getInt('oldprefix','','post');
        
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
		if ($task == 'migrate' && $run == 1 && !$oldprefix)
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
        
        //Add commands to move tables from old prefix to new
        $oldprefix = JRequest::getWord('oldprefix','','post');
        
        if ($oldprefix) 
            {
                $tablescopied = $this->copyTables($oldprefix);
                //if error
                //check for empty array - if not, print results
                if (empty($tablescopied)){$copysuccess = 1; print_r($tablescopied);}
                else
                {$copysuccess = false;}
                
            }
       if (!$oldprefix)
       {
            
    		$import = new JBSImport();
    		$result = $import->importdb();
       }
		
		if ($result || $copysuccess)
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

function performdb($query)
	{
		$db = JFactory::getDBO();
		$results = false;
		$db->setQuery($query);
		$db->query();
		if ($db->getErrorNum() != 0)
		{
			$results = JText::_('JBS_EI_DB_ERROR').': '.$db->getErrorNum()."<br /><font color=\"red\">";
			$results .= $db->stderr(true);
			$results .= "</font>";
			return $results;
		}
		else
		{
			$results = false; return $results;
		}
	}
    
 function copyTables($oldprefix)
 {
    //create table tablename_new like tablename; -> this will copy the structure...
    //insert into tablename_new select * from tablename; -> this would copy all the data
    $results = array();
    $db = JFactory::getDBO();
    $tables = $db->getTableList();
	$prefix = $db->getPrefix();
    foreach ($tables as $table)
    {
        $isjbs = substr_count($table,$oldprefix.'bsms');
        if ($isjbs)
        {
            $oldlength = strlen($oldprefix);
            $newsubtablename = substr($table,$oldlength );
            $newtablename = $prefix.$newsubtablename;
            $results = array();
            $query = 'DROP TABLE IF EXISTS '.$newtablename;
            $result = $this->performdb($query);
                if ($result){$results[] = $result;}
            $query = 'CREATE TABLE '.$newtablename.' LIKE '.$table;
            $result = $this->performdb($query);
                if ($result){$results[] = $result;}
            $query = 'INSERT INTO '.$newtablename.' SELECT * FROM '.$table;
            $result = $this->performdb($query);
                if ($result){$results[] = $result;}
            
        }
    }
    return $results;
    
 }
} // end of class

<?php

/**
 * @version $Id: migrate.php 1 $
 * @package COM_JBSMIGRATION
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die('Restricted access');

class JBSMigrate
{

	function migrate()
	{
		$result = false;
		$msg2 = '';
		$message = array();
		$application = JFactory::getApplication();
		$db = JFactory::getDBO();

		//First we check to see if there is a current version database installed. This will have a #__bsms_version table so we check for it's existence.
		//check to be sure a really early version is not installed $versiontype: 1 = current version type 2 = older version type 3 = no version
		//
		$tables = $db->getTableList();
		$prefix = $db->getPrefix();
		$versiontype = '';
		$currentversion = false;
		$oldversion = false;
		$jbsexists = false;
		foreach ($tables as $table)
		{
			$studies = $prefix.'bsms_version';
			$currentversionexists = substr_count($table,$studies);
			if ($currentversionexists > 0){
				$currentversion = true; $versiontype = 1;
			}
		}
		//Only move forward if a current version type is not found
		if (!$currentversion)
		{
			//Now let's check to see if there is an older database type (prior to 6.2)
			$oldversion = false;
			foreach ($tables as $table)
			{
				$studies = $prefix.'bsms_schemaVersion';
				$oldversionexists = substr_count($table,$studies);
				if ($oldversionexists > 0){
					$oldversion = true; $olderversiontype = 1; $versiontype = 2;
				}
			}
			if (!$oldversion)
			{
				foreach ($tables as $table)
				{
					$studies = $prefix.'bsms_schemaversion';
					$olderversionexists = substr_count($table,$studies);
					if ($olderversionexists > 0){
						$oldversion = true; $olderversiontype = 2;$versiontype = 2;
					}
				}
			}
		}
		//Finally if both current version and old version are false, we double check to make sure there are no JBS tables in the database.
		if (!$currentversion && !$oldversion )
		{
			foreach ($tables as $table)
			{
				$studies = $prefix.'bsms_studies';
				$jbsexists = substr_count($table,$studies);
				if (!$jbsexists){
					$versiontype = 4;
				}
				if ($jbsexists > 0){
					$versiontype = 3;
				}
			}
		}
		//Since we are going to install something, let's check to see if there is a version table, and create it if it isn't there
		foreach ($tables as $table)
		{
			$studies = $prefix.'bsms_version';
			$jbsexists = substr_count($table,$studies);
			if (!$jbsexists)
			{
				$query = "CREATE TABLE IF NOT EXISTS `#__bsms_version` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `version` varchar(20) NOT NULL,
                      `versiondate` date NOT NULL,
                      `installdate` date NOT NULL,
                      `build` varchar(20) NOT NULL,
                      `versionname` varchar(40) DEFAULT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ";
				$db->setQuery($query);
				$db->query();
			}

		}
		$query = "CREATE TABLE IF NOT EXISTS `#__bsms_version` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `version` varchar(20) NOT NULL,
      `versiondate` date NOT NULL,
      `installdate` date NOT NULL,
      `build` varchar(20) NOT NULL,
      `versionname` varchar(40) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ";

		//Now we run a switch case on the versiontype and run an install routine accordingly
		switch ($versiontype)
		{
			case 1:
				//This is a current database version so we check to see which version. We query to get the highest build in the version table
				$query = 'SELECT * FROM #__bsms_version ORDER BY `build` DESC';
				$db->setQuery($query);
				$db->query();
				$version = $db->loadObject();
				switch ($version->build)
				{
					case '700':
						$message[] = JText::_('JBS_VERSION_700_MESSAGE');
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;

					case '624':
						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.700.upgrade.php');
						$install = new jbs700Install();
						$message[] = $install->upgrade700();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;

					case '623':
						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.623.upgrade.php');
						$install = new jbs623Install();
						$message[] = $install->upgrade623();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.700.upgrade.php');
						$install = new jbs700Install();
						$message[] = $install->upgrade700();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;

					case '622':
						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.622.upgrade.php');
						$install = new jbs622Install();
						$message[] = $install->upgrade622();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.623.upgrade.php');
						$install = new jbs623Install();
						$message[] = $install->upgrade623();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.700.upgrade.php');
						$install = new jbs700Install();
						$message[] = $install->upgrade700();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;

					case '615':


						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.622.upgrade.php');
						$install = new jbs622Install();
						$message[] = $install->upgrade622();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.623.upgrade.php');
						$install = new jbs623Install();
						$message[] = $install->upgrade623();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.700.upgrade.php');
						$install = new jbs700Install();
						$message[] = $install->upgrade700();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;

					case '614':
						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.614.upgrade.php');
						$install = new jbs614Install();
						$message[] = $install->upgrade614();
						$msg2 = $msg2.$message;
						//echo $message;

						 
						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.622.upgrade.php');
						$install = new jbs622Install();
						$message[] = $install->upgrade622();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.623.upgrade.php');
						$install = new jbs623Install();
						$message[] = $install->upgrade623();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.700.upgrade.php');
						$install = new jbs700Install();
						$message[] = $install->upgrade700();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
				}
				 
				break;

			case 2:
				//This is an older version of the software so we check it's version
				if ($olderversiontype == 1)
				{
					$db->setQuery ("SELECT schemaVersion  FROM #__bsms_schemaVersion");
				}
				else
				{
					$db->setQuery ("SELECT schemaVersion FROM #__bsms_schemaversion");
				}
				$schema = $db->loadResult();
				switch ($schema)
				{
					case '600':
						$application->enqueueMessage( ''. JText::_('UPGRADE_JBS_VERSION_PROBLEM') .'' ) ;
						return false;
						break;
						 
					case '608':

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.611.upgrade.php');
						$install = new jbs611Install();
						$message[] = $install->upgrade611();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.613.upgrade.php');
						$install = new jbs613Install();
						$message[] = $install->upgrade613();
						$msg2 = $msg2.$message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.614.upgrade.php');
						$install = new jbs614Install();
						$message[] = $install->upgrade614();
						$msg2 = $msg2.$message;
						//echo $message;

						 
						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.622.upgrade.php');
						$install = new jbs622Install();
						$message[] = $install->upgrade622();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.623.upgrade.php');
						$install = new jbs623Install();
						$message[] = $install->upgrade623();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.700.upgrade.php');
						$install = new jbs700Install();
						$message[] = $install->upgrade700();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;

					case '611':

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.613.upgrade.php');
						$install = new jbs613Install();
						$message[] = $install->upgrade613();
						$msg2 = $msg2.$message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.614.upgrade.php');
						$install = new jbs614Install();
						$message[] = $install->upgrade614();
						$msg2 = $msg2.$message;
						//echo $message;
						 

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.622.upgrade.php');
						$install = new jbs622Install();
						$message[] = $install->upgrade622();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.623.upgrade.php');
						$install = new jbs623Install();
						$message[] = $install->upgrade623();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.700.upgrade.php');
						$install = new jbs700Install();
						$message[] = $install->upgrade700();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;

					case '613':
						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.613.upgrade.php');
						$install = new jbs613Install();
						$message[] = $install->upgrade613();
						$msg2 = $msg2.$message;
						//echo $message;


						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.614.upgrade.php');
						$install = new jbs614Install();
						$message[] = $install->upgrade614();
						$msg2 = $msg2.$message;
						//echo $message;

						 

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.622.upgrade.php');
						$install = new jbs622Install();
						$message[] = $install->upgrade622();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.623.upgrade.php');
						$install = new jbs623Install();
						$message[] = $install->upgrade623();
						$msg2 = $msg2.$message;
						//echo $message;

						require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'biblestudy.700.upgrade.php');
						$install = new jbs700Install();
						$message[] = $install->upgrade700();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
                        
                        require_once (JPATH_ADMINISTRATOR .DIRECTORY_SEPARATOR. 'components' .DIRECTORY_SEPARATOR. 'com_jbsmigration' .DIRECTORY_SEPARATOR. 'migration' .DIRECTORY_SEPARATOR. 'update701.php');
						$install = new updatejbs701();
						$message[] = $install->do701update();
						$msg2 = $msg2.$message;
						//echo $message;
						break;
				}
				break;

			case 3:
				//There is a version installed, but it is older than 6.0.8 and we can't upgrade it
				JError::raiseNotice('SOME_ERROR_CODE', 'JBS_EI_NO_VERSION');
				return false;
				break;
		}
		$jbsmessages = $message;
		JRequest::setVar('jbsmessages',$jbsmessages,'get','array');
		return true;
		//return $message;
	}
}
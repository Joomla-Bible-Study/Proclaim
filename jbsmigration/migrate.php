<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */



?>
<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
class JBSMigrate
{
    
    function migrate()
    {
        $result = false;
        
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
            if ($currentversionexists > 0){$currentversion = true; $versiontype = 1;}
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
                if ($oldversionexists > 0){$oldversion = true; $olderversiontype = 1; $versiontype = 2;}
            }
            if (!$oldversion)
            {
                   foreach ($tables as $table)
                {
                    $studies = $prefix.'bsms_schemaversion';
                    $olderversionexists = substr_count($table,$studies);
                    if ($olderversionexists > 0){$oldversion = true; $olderversiontype = 2;$versiontype = 2;}
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
                if (!$jbsexists){$versiontype = 4;}
                if ($jbsexists > 0){$versiontype = 3;}
            }
        }
        
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
                    $message = JText::_('JBS_VERSION_700_MESSAGE');
                     //echo $message;
                break;
                
                case '624':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    //echo $message; 
                break;
                
                case '623':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                     //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                    //echo $message;  
                break;
                
                case '622':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                     //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                     //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                     //echo $message;   
                break;
                
                case '615':
                    $message = 'No special requirements for this version.';
                    echo JHtml::_('sliders.panel', JText::_('UPGRADE_JBS_VERSION_615') , 'publishing-details'); 
        			 //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                    //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                     //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
                     //echo $message;   
                break;
                
                case '614':
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.614.upgrade.php');
                    $install = new jbs614Install();
                    $message = $install->upgrade614();
                     //echo $message;
                    
                    $message = 'No special requirements for this version.';
                     //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.622.upgrade.php');
                    $install = new jbs622Install();
                    $message = $install->upgrade622();
                     //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.623.upgrade.php');
                    $install = new jbs623Install();
                    $message = $install->upgrade623();
                     //echo $message;
                    
                    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.700.upgrade.php');
                    $install = new jbs700Install();
                    $message = $install->upgrade700(); 
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
    				                            
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.611.upgrade.php');
                        $install = new jbs611Install();
                        $message = $install->upgrade611();
                        //echo $message;
                                                  
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                         //echo $message;
                        
                        $message = 'No special requirements for this version.';
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
                        //echo $message;     
    				break;
    				
    				case '611':
    				      
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                         //echo $message;
                        
                        $message = 'No special requirements for this version.';
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
                        //echo $message;      
    				break;
    		
    				case '613':
    				    require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.613.upgrade.php');
                        $install = new jbs613Install();
                        $message = $install->upgrade613();
                         //echo $message;
                        
                                              
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.614.upgrade.php');
                        $install = new jbs614Install();
                        $message = $install->upgrade614();
                         //echo $message;
                        
                        $message = 'No special requirements for this version.';
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.622.upgrade.php');
                        $install = new jbs622Install();
                        $message = $install->upgrade622();
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.623.upgrade.php');
                        $install = new jbs623Install();
                        $message = $install->upgrade623();
                         //echo $message;
                        
                        require_once (JPATH_ADMINISTRATOR .DS. 'components' .DS. 'com_jbsmigration' .DS. 'migration' .DS. 'biblestudy.700.upgrade.php');
                        $install = new jbs700Install();
                        $message = $install->upgrade700(); 
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
        
        
        return $message;
    }
}

?>
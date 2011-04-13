<?php

/**
 * @author Tom Fuller
 * @copyright 2011
 */

defined('_JEXEC') or die( 'Restricted access' );

class JBSImport
{
    
     function importdb()
     {
        $result = FALSE;
      // Attempt to increase the maximum execution time for php scripts
      @set_time_limit(300);
       $installtype = JRequest::getString('install_directory','','post');
       if (substr_count($installtype,'sql'))
       {
        $uploadresults = $this->_getPackageFromFolder();
        if ($uploadresults){$result = true;}
       }
       else
       {
        $uploadresults = $this->_getPackageFromUpload();
       }
        if ($uploadresults && (substr_count($installtype,'sql') < 1))
        {
               $result = $this->installdb($uploadresults);
               $docopy = $this->copynewtables();
              
        }
        return $result;
     }   	

	function _getPackageFromUpload()
	{
		// Get the uploaded file information
		$userfile = JRequest::getVar('importdb', null, 'files', 'array' );

		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads')) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLFILE'));
			return false;
		}

		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib')) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLZLIB'));
			return false;
		}

		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile) ) {
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('No file selected'));
			return false;
		}

		// Check if there was a problem uploading the file.
		if ( $userfile['error'] || $userfile['size'] < 1 )
		{
			JError::raiseWarning('SOME_ERROR_CODE', JText::_('WARNINSTALLUPLOADERROR'));
			return false;
		}

		// Build the appropriate paths
		$config =& JFactory::getConfig();
		$tmp_dest 	= $config->getValue('config.tmp_path').DS.$userfile['name'];
		$tmp_src	= $userfile['tmp_name'];

		// Move uploaded file
		jimport('joomla.filesystem.file');
		$uploaded = JFile::upload($tmp_src, $tmp_dest);
        if ($uploaded){return $tmp_src;}
        else {return false;}
	
	}
    
    function installdb($tmp_src)
    {
       @set_time_limit(300);
       $result = false;
       $userfile = JRequest::getVar('importdb', null, 'files', 'array' );
       $db = JFactory::getDBO();
       $query = file_get_contents(JPATH_SITE .DS. 'tmp' .DS. $userfile['name']);
    
       $db->setQuery($query);
       $db->queryBatch();
       	if ($db->getErrorNum() != 0)
					{
						$error = "DB function failed with error number ".$db->getErrorNum()."<br /><font color=\"red\">";
						$error .= $db->stderr(true);
						$error .= "</font>";
                        echo $error;
					}
       else
       {
        //To Do - delete uploaded file
        $result = true;
        return $result;
       }
    }
    
    function _getPackageFromFolder()
	{
		$result = false;
        
        $p_dir = JRequest::getString('install_directory','','post');
       
        $config =& JFactory::getConfig();
       
		$p_dir = JPath::clean( $p_dir );
 
		$db = JFactory::getDBO();
       $query = file_get_contents($p_dir);
    @set_time_limit(300);
       $db->setQuery($query);
       $db->queryBatch();
       	if ($db->getErrorNum() != 0)
					{
						$error = JText::_("JBS_EI_DB_ERROR").": ".$db->getErrorNum()."<br /><font color=\"red\">";
						$error .= $db->stderr(true);
						$error .= "</font>";
                        echo $error;
					}
        else
        {
            $result = true;            
        }
        //To do: delete uploaded file
		return $result;
	}
  
  function copynewtables()
  {
    
        $db = JFactory::getDBO();
        $tables = $db->getTableList();
        $prefix = $db->getPrefix();
              
        foreach ($tables AS $table)
        {
             $jbs = $prefix.'bsms_';
             $jbstables = substr_count($table,$jbs);
             if ($jbstables)
             {
                $jbsgenesis = substr_count($table,'genesis');
                if (!$jbsgenesis )
                {
                    $query = 'DROP TABLE '.$table;
                    $db->setQuery($query);
                    $db->query();
                }
                else
                {
                    $oldtablelength = strlen($table);
                    $newtablelength = $oldtablelength - 8;
                    $newtable = substr($table,0,$newtablelength);
                    $query = 'CREATE TABLE '.$newtable.' SELECT * FROM '.$table;
                    $db->setQuery($query);
                    $db->query();
                    
                    $query = 'DROP TABLE '.$table;
                    $db->setQuery($query);
                    $db->query();
                    
                    //For some reason the auto_increment was dropped in the backup so we need to add it back
                    if (!substr_count($newtable, 'timeset'))
                    {
                        $query = 'ALTER TABLE '.$newtable.' ADD PRIMARY KEY (id)';
                        $db->setQuery($query);
                        $db->query();
                        
                        $query = 'ALTER TABLE '.$newtable.' MODIFY id int(10) NOT NULL AUTO_INCREMENT';
                        $db->setQuery($query);
                        $db->query();
                    }
                    if (substr_count($newtable,'studies'))
                        {
                            $query = 'ALTER TABLE '.$newtable.' MODIFY studytext TEXT';
                            $db->setQuery($query);
                            $db->query();
                            
                            $query = 'ALTER TABLE '.$newtable.' MODIFY studytext2 TEXT';
                            $db->setQuery($query);
                            $db->query();
                        }
                    if (substr_count($newtable,'podcast'))
                        {
                            $query = 'ALTER TABLE '.$newtable.' MODIFY description TEXT';
                            $db->setQuery($query);
                            $db->query();
                        }
                         if (substr_count($newtable,'series'))
                        {
                            $query = 'ALTER TABLE '.$newtable.' MODIFY description TEXT';
                            $db->setQuery($query);
                            $db->query();
                        }
                         if (substr_count($newtable,'teachers'))
                        {
                            $query = 'ALTER TABLE '.$newtable.' MODIFY information TEXT';
                            $db->setQuery($query);
                            $db->query();
                        }
                }
                
             }
        }
       
        
        
        
  }  
  
}
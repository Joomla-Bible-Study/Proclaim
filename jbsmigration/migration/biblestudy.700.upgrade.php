<?php

/**
 * @author Tom Fuller - Joomla Bible Study
 * @copyright 2011
 */

defined( '_JEXEC' ) or die('Restricted access');


require_once ( JPATH_ROOT .DS.'libraries'.DS.'joomla'.DS.'html'.DS.'parameter.php' );

class jbs700Install{

        function upgrade700()
    {
        $db = JFactory::getDBO();
       $messages = array();
        
        //Alter some tables
        $msg = '';
                
        $query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN `player` int(2) NULL";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
        $query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN `popup` int(2) NULL";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
        $query = "DROP TABLE #__bsms_timeset";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
                    `timeset` VARCHAR(14) ,
                    `backup` VARCHAR(14) ,
                    KEY `timeset` (`timeset`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
         $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
        $query = "INSERT INTO `#__bsms_timeset` SET `timeset`='1281646339', `backup` = '1281646339'";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
        $query = "INSERT  INTO `#__bsms_media` VALUES (15,'You Tube','You Tube','','youtube24.png','You Tube Video', 1)";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
        $query = "ALTER TABLE `#__bsms_admin` ADD COLUMN `drop_tables` int(3) NULL default '0' AFTER `showhide`";
        $msg = $this->performdb($query);
        
        
        $query = "UPDATE `#__bsms_admin` SET `drop_tables` = 0 WHERE id = 1";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
       
       $query = "ALTER Table `#__bsms_order` MODIFY value VARCHAR(50)";
       $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
       $query = "ALTER Table `#__bsms_mediafiles` MODIFY podcast_id VARCHAR(50)";
       $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }     
       $query = "UPDATE `#__bsms_order` SET text = 'JBS_CMN_ASCENDING' WHERE id = 1";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
             
         $query = "UPDATE `#__bsms_order` SET text = 'JBS_CMN_DESCENDING' WHERE id = 2";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        $query = "INSERT INTO #__bsms_version SET `version` = '7.0.0', `installdate`='2011-03-12', `build`='700', `versionname`='1Kings', `versiondate`='2011-03-15'";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
        $query = 'SELECT `id`, `params` FROM #__bsms_mediafiles';
        $db->setQuery($query);
        $db->query();
        $results = $db->loadObjectList();
if ($results)
{        
    //Now run through all the results, pull out the media player and the popup type and move them to their respective db fields
    foreach ($results AS $result)
    {
        $params = new JParameter($result->params);
        $player = $params->get('player');
        $popup = $params->get('internal_popup');
        if ($player)
        {
            if ($player == 2)
            {
                $player = 3;
            }
            $query = "UPDATE #__bsms_mediafiles SET `player` = '$player' WHERE `id` = $result->id LIMIT 1";
            $msg = $this->performdb($query);
            
        }
        if ($popup)
        {
            $query = "UPDATE #__bsms_mediafiles SET `popup` = '$popup' WHERE `id` = $result->id LIMIT 1";
            $msg = $this->performdb($query);
            
        }
        
    }
}
 $query = "ALTER TABLE #__bsms_admin ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }       
  $query = "ALTER TABLE #__bsms_comments ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 
  $query = "ALTER TABLE #__bsms_folders ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
 $query = "ALTER TABLE #__bsms_locations ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }  
$query = "ALTER TABLE #__bsms_media ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 
$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

$query = "ALTER TABLE #__bsms_message_type ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }

$query = "ALTER TABLE #__bsms_mimetype ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

$query = "ALTER TABLE #__bsms_podcast ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

$query = "ALTER TABLE #__bsms_series ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

$query = "ALTER TABLE #__bsms_servers ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

$query = "ALTER TABLE #__bsms_share ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

$query = "ALTER TABLE #__bsms_studies ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 
 
 $query = "ALTER TABLE #__bsms_teachers ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

$query = "ALTER TABLE #__bsms_templates ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

$query = "ALTER TABLE #__bsms_topics ADD COLUMN asset_id INT(10) NOT NULL, ADD COLUMN access INT(10) NOT NULL";
 $msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             } 

//Get all the study records

$query = "UPDATE #__bsms_studies SET `show_level` = '1' WHERE `show_level` = '0'";
$msg = $this->performdb($query);
 if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }

$query = "UPDATE #__bsms_studies SET `show_level` = '2' WHERE `show_level` = '18'";
$msg = $this->performdb($query);
 if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }

$query = "UPDATE #__bsms_studies SET `show_level` = '3' WHERE `show_level` = '19'";
$msg = $this->performdb($query);
 if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }

$query = "UPDATE #__bsms_studies SET `show_level` = '4' WHERE `show_level` = '20'";
$msg = $this->performdb($query);
 if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
$query = "UPDATE #__bsms_studies SET `show_level` = '5' WHERE `show_level` = '22'";
$msg = $this->performdb($query);
 if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
$query = "UPDATE #__bsms_studies SET `show_level` = '6' WHERE `show_level` = '23'";
$msg = $this->performdb($query);
 if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        
$query = "UPDATE #__bsms_studies SET `show_level` = '7' WHERE `show_level` = '24'";
$msg = $this->performdb($query);
  if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }       

              
        $application = JFactory::getApplication();
       $application->enqueueMessage( ''. JText::_('Upgrading to build 700') .'' ) ;
       $results = array('build'=>'700','messages'=>$messages);
    
    return $results;
        
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

}
?>
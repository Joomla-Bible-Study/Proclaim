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
       
        
        //Alter some tables
        $msg = '';
                
        $query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN `player` int(2) NULL";
        $msg = $this->performdb($query);
        
        
        $query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN `popup` int(2) NULL";
        $msg = $this->performdb($query);
        
        
        $query = "DROP TABLE #__bsms_timeset";
        $msg = $this->performdb($query);
        
        
        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
                    `timeset` VARCHAR(14) ,
                    `backup` VARCHAR(14) ,
                    KEY `timeset` (`timeset`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
         $msg = $this->performdb($query);
        
        
        $query = "INSERT INTO `#__bsms_timeset` SET `timeset`='1281646339', `backup` = '1281646339'";
        $msg = $this->performdb($query);
        
        
        $query = "INSERT  INTO `#__bsms_media` VALUES (15,'You Tube','You Tube','','youtube24.png','You Tube Video', 1)";
        $msg = $this->performdb($query);
        
        
        $query = "ALTER TABLE `#__bsms_admin` ADD COLUMN `drop_tables` int(3) NULL default '0' AFTER `showhide`";
        $msg = $this->performdb($query);
        
        
        $query = "UPDATE `#__bsms_admin` SET `drop_tables` = 0 WHERE id = 1";
        $msg = $this->performdb($query);
        
       
        $query = "INSERT INTO #__bsms_version SET `version` = '7.0.0', `installdate`='2011-02-12', `build`='1390', `versionname`='1Kings', `versiondate`='2011-02-15'";
        $msg = $this->performdb($query);
        
        
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
//Get all the study records

$query = "UPDATE #__bsms_studies SET `show_level` = '1' WHERE `show_level` = '0'";
$msg = $this->performdb($query);


$query = "UPDATE #__bsms_studies SET `show_level` = '2' WHERE `show_level` = '18'";
$msg = $this->performdb($query);


$query = "UPDATE #__bsms_studies SET `show_level` = '3' WHERE `show_level` = '19'";
$msg = $this->performdb($query);


$query = "UPDATE #__bsms_studies SET `show_level` = '4' WHERE `show_level` = '20'";
$msg = $this->performdb($query);

        
$query = "UPDATE #__bsms_studies SET `show_level` = '5' WHERE `show_level` = '22'";
$msg = $this->performdb($query);

        
$query = "UPDATE #__bsms_studies SET `show_level` = '6' WHERE `show_level` = '23'";
$msg = $this->performdb($query);

        
$query = "UPDATE #__bsms_studies SET `show_level` = '7' WHERE `show_level` = '24'";
$msg = $this->performdb($query);
        
        $application = JFactory::getApplication();
       $application->enqueueMessage( ''. JText::_('Upgrading to build 700') .'' ) ;
        return $msg;
    }

  function performdb($query)
    {
        $db = JFactory::getDBO();
        $results = false;
        $db->setQuery($query);
        $db->query();
        
		if ($db->getErrorNum() != 0)
			{
				$results = false; return $results;
			}
			else
			{
				$results = true; return $results;
            }
    }

}
?>
<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc Updates the media files table to reflect new way of associating podcasts and adds Landing Page CSS
 */
defined( '_JEXEC' ) or die('Restricted access');

class jbs614Install{

function upgrade614()
{
  
 	$query = "CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
				  `id` int(3) NOT NULL AUTO_INCREMENT,
				  `study_id` int(3) NOT NULL DEFAULT '0',
				  `topic_id` int(3) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `id` (`id`),
				  KEY `id_2` (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
            $msg = $this->performdb($query);
            

        $query = "CREATE TABLE IF NOT EXISTS `#__bsms_timeset` (
                `timeset` VARCHAR(14) ,
                KEY `timeset` (`timeset`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $msg = $this->performdb($query);
            

			$query = "ALTER TABLE #__bsms_teachers MODIFY `title` varchar(250)";
            $msg = $this->performdb($query);
            

			$query = "ALTER TABLE #__bsms_admin ADD COLUMN showhide char(255) DEFAULT NULL";
            $msg = $this->performdb($query);
            

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN downloads int(10) DEFAULT 0";
            $msg = $this->performdb($query);
            

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN plays int(10) DEFAULT 0";
            $msg = $this->performdb($query);
            

            $query = "INSERT INTO `#__bsms_timeset` SET `timeset`='1281646339'";
            $msg = $this->performdb($query);
            



//This updates the mediafiles table to reflect the new way of associating files to podcasts
$db = JFactory::getDBO();
   $query = 'SELECT id, params, podcast_id FROM #__bsms_mediafiles WHERE podcast_id > 0';
   $db->setQuery($query);
   $db->query();
   $num_rows = @$db->getNumRows();
   if ($num_rows > 0)
   {
        $results = $db->loadObjectList();
	   foreach ($results as $result)
	   {
	   	//added the \n 
	   	$podcast = 'podcasts='.$result->podcast_id.'\n';
	   	$params = $result->params;
	   	$update = $podcast.' '.$params;
	   	$query = "UPDATE #__bsms_mediafiles SET `params` = '".$update."', `podcast_id`='0' WHERE `id` = ".$result->id;
	  	$msg = $this->performdb($query);
        }	   
	}
$application = JFactory::getApplication();
$application->enqueueMessage( ''. JText::_('Upgrading from build 614') .'' ) ;
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
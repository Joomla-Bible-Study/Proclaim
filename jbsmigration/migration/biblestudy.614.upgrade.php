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
$messages = array();  
 	$query = "CREATE TABLE IF NOT EXISTS `#__bsms_studytopics` (
				  `id` int(3) NOT NULL AUTO_INCREMENT,
				  `study_id` int(3) NOT NULL DEFAULT '0',
				  `topic_id` int(3) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `id` (`id`),
				  KEY `id_2` (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
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
                KEY `timeset` (`timeset`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
            $msg = $this->performdb($query);
             if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }

			$query = "ALTER TABLE #__bsms_teachers MODIFY `title` varchar(250)";
            $msg = $this->performdb($query);
             if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN downloads int(10) DEFAULT 0";
            $msg = $this->performdb($query);
             if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }

			$query = "ALTER TABLE #__bsms_mediafiles ADD COLUMN plays int(10) DEFAULT 0";
            $msg = $this->performdb($query);
             if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }

            $query = "INSERT INTO `#__bsms_timeset` SET `timeset`='1281646339'";
            $msg = $this->performdb($query);
             if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }



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
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }
        }	   
	}
    
    $query = "INSERT INTO #__bsms_version SET `version` = '6.2.0', `installdate`='2010-09-06', `build`='614', `versionname`='Deuteronomy', `versiondate`='2010-09-06'";
        $msg = $this->performdb($query);
         if (!$msg)
             {
                $messages[] = '<font color="green">'.JText::_('JBS_EI_QUERY_SUCCESS').': '.$query.' </font><br /><br />';
             } 
             else
             {
                $messages[] = $msg;
             }             
//$application = JFactory::getApplication();
//$application->enqueueMessage( ''. JText::_('Upgrading from build 614') .'' ) ;
$results = array('build'=>'614','messages'=>$messages);
    
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
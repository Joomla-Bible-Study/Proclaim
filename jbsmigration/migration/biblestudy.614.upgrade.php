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
 $result_table = '<table><tr><td>This routine adds some items to the css file for the Landing Page view and updates the mediafiles table</td></tr>';
 
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
   $num_rows = $db->getNumRows();
   if ($num_rows > 0)
   {
  		$add = 0;
	  //	$result_table .= '<tr><td>'.$num_rows.' rows from Media Files Records in need of updating for new podcast association.</td></tr>';
		$results = $db->loadObjectList();
	   foreach ($results as $result)
	   {
	   	//added the \n 
	   	$podcast = 'podcasts='.$result->podcast_id.'\n';
	   	$params = $result->params;
	   	$update = $podcast.' '.$params;
	   	$query = "UPDATE #__bsms_mediafiles SET `params` = '".$update."', `podcast_id`='0' WHERE `id` = ".$result->id;
	  	$db->setQuery($query);
	  	$db->query();
	   	if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
					$result_table .= '<tr><td>An error occured while updating mediafiles table: '.$error.'</td></tr>';
				}
			else
			{
				$updated = 0;
				$updated = $db->getAffectedRows(); //echo 'affected: '.$updated;
				$add = $add + $updated;
			} 
		}
	   $result_table .= '<tr><td>'.$add.' Rows in Media Files Records table updated.</td></tr>';
	   
	}

// This adds some css for the Landing Page

$dest = JPATH_SITE.DS.'components'.DS.'com_biblestudy'.DS.'assets'.DS.'css'.DS.'biblestudy.css';
$cssexists = JFile::exists($dest);
if ($cssexists)
{
    $landingread = JFile::read($dest);
    $landingexists = 1;
    	$landingexists = substr_count($landingread,'#landinglist');
    	if ($landingexists < 1)
    	{
    		$landing = '
    /* Landing Page Items */ 
    #landinglist { 
    	 
    } 
    #landing_label { 
    	 
    } 
    #landing_item { 
    	 
    } 
    #landing_title { 
    font-family:arial; 
    font-size:16px; 
    font-weight:bold; 
    	 
    } 
    #biblestudy_landing { 
    	 
    } 
    #showhide { 
    font-family:arial; 
    font-size:12px; 
    font-weight:bold; 
    text-decoration:none; 
    } 
    
    #showhide .showhideheadingbutton img {
    vertical-align:bottom;
    }
    
    #landing_table { 
    
    }
    
    #landing_td {
    width: 33%;
    }
    
    #landing_separator {
    height:15px;
    }
    /* Popup Window Items */
    .popupwindow
    {
    margin: 5px;
    text-align:center; 
    }
    p.popuptitle {
    font-weight: bold;
    color: black;
    }
    
    .popupfooter
    {
    margin: 5px;
    text-align:center;
    }
    p.popupfooter {
    font-weight: bold;
    color: grey;
    }'
    ;
    $landingwrite = $landingread.$landing;
    			$errcss = '';
    			if (!JFile::write($dest, $landingwrite))
    			{
    				$msg = false;
    			}
    			else
    			{
    				$msg = true;
    			}
    }
}
$src = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css.dist';
$dest = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css';
$cssexists = JFile::exists($dest);
if (!$cssexists)
	{
		if (!JFile::copy($src, $dest))
		{
			$msg = false;
		}
		else
		{$msg = true;}
	}

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
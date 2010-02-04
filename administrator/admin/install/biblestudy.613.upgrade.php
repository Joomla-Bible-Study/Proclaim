<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined( '_JEXEC' ) or die('Restricted access');
$result_table = '<table><tr><td>This routine checks to see if the css file is installed. If not it is copied. Then the css is updated to include Social Networking elements if needed. Finally, the database is updated to reflect changes to the way the media player is accessed. If no mediafile records are indicated, then no changes were needed. </td></tr>';
//Read current css file, add share information if not already there, write and close
jimport('joomla.filesystem.file');
$src = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css.dist';
$dest = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css';

//Let's check to see if there is a css file - if not, we'll copy one over
$cssexists = JFile::exists($dest);
if (!$cssexists)
	{
		JFile::copy($src, $dest);
		$result_table .= '<tr><td>CSS data installed</td></tr>';
	}
//Now we look inside the css to see if there are share items, if not, we'll add them
if ($cssexists)
{
	$cssread = JFile::read($dest);
	$shareexists = 0;
	$shareexists = substr_count($cssread,'#bsmsshare');
	if ($shareexists < 1)
	{
		$cssshared = '
		/*Social Networking Items */
		#bsmsshare {
		  margin: 0;
		  border-collapse:separate;
		  float:right;
		  border: 1px solid #CFCFCF;
		  background-color: #F5F5F5;
		}
		#bsmsshare th, #bsmsshare td {
		  text-align:center;
		  padding:0 0 0 0;
		  border:none;
		}
		#bsmsshare th {
			color:#0b55c4;
			font-weight:bold;
		}';
			$cssread = $cssread.$cssshared;
			$errcss = '';
			if (!JFile::write($dest, $cssread))
			{
				$result_table .= '<tr><td>There was a problem writing to the css file. Please contact customer support on JoomlaBibleStudy.org</td></tr>';
			}
	}
}

//Now we are going to update the db. We no longer use the field for AVR but it happens in a param so we need to get rid of the internal_viewer after setting the param accordingly
$database = &JFactory::getDBO();
$database->setQuery("UPDATE #__bsms_mediafiles SET params = 'player=2', internal_viewer = '0' WHERE internal_viewer = '1' AND params IS NULL");
	$database->query();
	if ($database->getErrorNum() > 0)
			{
				$error = $database->getErrorMsg();
				$result_table .= '<tr><td>An error occured while updating mediafiles table: '.$error.'</td></tr>';
			}
	else
	{
		$result = $database->getAffectedRows();
		$result_table .= '<tr><td>'.$result.' Mediafiles records updated</td></tr>';
	}
//All Videos Reloaded has a problem with Bible Study. If there is no Itemid (like from the module) then AVR will break with Popup Database Error. We created a special file for the popup view.html.php file and we copy it over, backing up the old one. It will be reinstated on a full uninstall of Bible Study
$src = JPATH_SITE.DS.'components/com_biblestudy/assets/avr/view.html.php';
$dest = JPATH_SITE.DS.'components/com_avreloaded/views/popup/view.html.php';
$avrbackup = JPATH_SITE.DS.'components/com_avreloaded/views/popup/view2.html.php';
$avrexists = JFile::exists($dest);
if ($avrexists)
	{
		$avrread = JFile::read($dest);
		$isbsms = substr_count($avrread,'JoomlaBibleStudy');
		if (!$isbsms)
		{
			JFile::copy($dest, $avrbackup);
			JFile::copy($src, $dest);
			$result_table .= '<tr><td>AVR Edited File installed</td></tr>';
		}
		
	}
	$result_table .= '</table>';
	echo $result_table;
?>
<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * This file should be used only on fresh install. It alters two files but only if components are installed.
 */
defined('_JEXEC') or die();
$result_table = '<table><tr><td>This file will check if the css is installed and then checks if you are using All Videos Reloaded. If you are, then a view file in AVR is replaced to solve a problem with Bible Study</td></tr>';
		//Check for AVR and alter file for Bible Studey
		jimport('joomla.filesystem.file');
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
		
//Check to see if the css file exists. If it does, don't do anything. If not, install the css file

$src = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css.dist';
$dest = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css';
$cssexists = JFile::exists($dest);
if (!$cssexists)
	{
		if (!JFile::copy($src, $dest))
		{
			$result_table .= '<tr><td>There was a problem copying the css data. Please manually copy /assets/css/biblestudy.css.dist to biblestudy.css</td></tr>';
		}
		else
		{$result_table .= '<tr><td>CSS data installed</td></tr>';}
	}
$result_table .= '</table>';
echo $result_table;
?>
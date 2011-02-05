<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * This file should be used only on fresh install. It alters two files but only if components are installed.
 */
defined('_JEXEC') or die();
$result_table = '<table><tr><td>This file will check if the css is installed. If not, the css file is copied.</td></tr>';
		
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
return $result_table;
?>
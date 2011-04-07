<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * This file should be used only on fresh install. It alters two files but only if components are installed.
 */
defined('_JEXEC') or die();
$result_table = '<table><tr><td>'. JText::_('JBS_INS_16_CHECK_INSTALLED_CSS') .'</td></tr>';
		
//Check to see if the css file exists. If it does, don't do anything. If not, install the css file

$src = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css.dist';
$dest = JPATH_SITE.DS.'components/com_biblestudy/assets/css/biblestudy.css';
$cssexists = JFile::exists($dest);
if (!$cssexists)
	{
		if (!JFile::copy($src, $dest))
		{
			$result_table .= '<tr><td>'. JText::_('JBS_INS_16_ERROR_COPY_CSS') .'</td></tr>';
		}
		else
		{$result_table .= '<tr><td>'. JText::_('JBS_INS_16_CSS_INSTALLED') .'</td></tr>';}
	}
$result_table .= '</table>';
return $result_table;
?>
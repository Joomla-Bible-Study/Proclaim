<?php

/**
 * @version $Id$
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();

$result_table = '<table><tr><td>'. JText::_('JBS_INS_CHECK_INSTALLED_CSS') .'</td></tr>';

//Check to see if the css file exists. If it does, don't do anything. If not, install the css file

$src = JPATH_SITE.DIRECTORY_SEPARATOR.'components/com_biblestudy/assets/css/biblestudy.css.dist';
$dest = JPATH_SITE.DIRECTORY_SEPARATOR.'components/com_biblestudy/assets/css/biblestudy.css';
$cssexists = JFile::exists($dest);
if (!$cssexists)
{
	if (!JFile::copy($src, $dest))
	{
		$result_table .= '<tr><td>'. JText::_('JBS_INS_ERROR_COPY_CSS') .'</td></tr>';
	}
	else
	{$result_table .= '<tr><td>'. JText::_('JBS_INS_CSS_INSTALLED') .'</td></tr>';
	}
}

$result_table .= '</table>';

return $result_table;
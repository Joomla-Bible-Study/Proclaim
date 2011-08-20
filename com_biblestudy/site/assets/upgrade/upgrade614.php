<?php

/**
 * @version $Id: upgrade614.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

defined('_JEXEC') or die();

function biblestudyUpgrade614($schema)
{
	$messagetable = '<table>';
	if ($schema == 614)
	{
		$messagetable = '<tr><td>Bible Study Database Schema set at 614. No update necessary</td></tr></table>';
		return $messagetable;
	}

	//Other function calls go here



	//end of the function
	$messagetable = '</table>';
	return $messagetable;
}

function updateTemplate()
{
	$db = & JFactory::getDBO();
	$db->setQuery('SELECT params, id FROM #__bsms_templates');
	$db->query();
	$rows = $db->loadObjectList();
}
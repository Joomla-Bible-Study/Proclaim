<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 * @desc This routine is designed to be the upgrade from database version 613 to 614. This corresponds with version 6.2 of com_biblestudy
 */

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
	$messagetable .= '</table>'
	return $messagetable;
}

function updateTemplate()
{
	$db = & JFactory::getDBO();
	$db->setQuery('SELECT params, id FROM #__bsms_templates');
	$db->query();
	$rows = $db->loadObjectList();
}
?>
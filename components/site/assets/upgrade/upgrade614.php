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
	if ($schema > 613)
	{
		$messagetable = '<tr><td>Bible Study Database Schema set at 614. No update necessary</td></tr></table>';
		return $messagetable;
	}


//first function call to fix the problem with internal_player in mediafilesedit
$result = $this->getMediafilefix();	
if ($result){$messagetable[] = $result;}

//Other function calls go here



//end of the function
	$messagetable .= '</table>'
	return $messagetable;
}

function getMediafilefix()
{
	$db = & JFactory::getDBO();
	$db->setQuery("UPDATE #__bsms_mediafiles SET params = 'player=2' WHERE internal_viewer = '1'";)
	$db->query();
	if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$result = '<tr><td>An error occured while updating mediafiles table: '.$error.'</td></tr>';
			}
	return $result;
}
?>
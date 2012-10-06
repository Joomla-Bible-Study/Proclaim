<?php

/**
 * @author Tom Fuller - Joomla Bible Study
 * @copyright 2010
 */

defined( '_JEXEC' ) or die('Restricted access');

$result_table = '<table><tr><td>This routine updates the mediafiles table to adjust the player type parameter</td></tr>';

$db = JFactory::getDBO();
$before = 0;
$after = 0;
//First we find out how many rows have the internal_popup set to 0
$query = "SELECT count(`id`) FROM #__bsms_mediafiles WHERE `params` LIKE '%internal_popup=0%' GROUP BY id";
$db->setQuery($query);
$db->query();
$before = $db->loadResult();

//Now we adjust those rows that have internal_popup set to 0 and we change it to 2
$query = 'SELECT id, params FROM #__bsms_mediafiles';
$db->setQuery($query);
$db->query();
$results = $db->loadObjectList();
foreach ($results AS $result)
{
	$isplayertype = substr_count($result->params,'internal_popup=0');
	if ($isplayertype)
	{
		$oldparams = $result->params;
		$newparams = str_replace('internal_popup=0','internal_popup=2',$oldparams);
		$query = "UPDATE #__bsms_mediafiles SET `params` = '".$newparams."' WHERE id = ".$result->id;
		$db->setQuery($query);
		$db->query();
	}
}

//Now we check again to see if there are any rows that didn't get changed and report that
$query = "SELECT count(`id`) FROM #__bsms_mediafiles WHERE `params` LIKE '%internal_popup=0%' GROUP BY id";
$db->setQuery($query);
$db->query();
$after = $db->loadResult();
if (!$after) {
	$after = 0;
}
$result_table .= '<tr><td>'.$before.' row(s) in need of adjustment.</td></tr> <tr><td>'.$after.' row(s) not fixed.</td></tr></table>';
echo $result_table;
?>
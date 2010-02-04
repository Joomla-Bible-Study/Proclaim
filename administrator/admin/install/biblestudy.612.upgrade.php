<?php

/**
 * @author Joomla Bible Study
 * @copyright 2010
 */
defined('_JEXEC') or die();
$db = &JFactory::getDBO();
$result_table = '<table><tr><td>This routine updates the mediafiles table. If 0 results then no records needed updating</td></tr>';
$db->setQuery("UPDATE #__bsms_mediafiles SET params = 'player=2', internal_viewer = '0' WHERE internal_viewer = '1' AND params IS NULL");
	$db->query();
	if ($db->getErrorNum() > 0)
			{
				$error = $db->getErrorMsg();
				$result_table .= '<tr><td>An error occured while updating mediafiles table: '.$error.'</td></tr>';
			}
	else
	{
		$result = $db->getAffectedRows();
		if ($result > 0)
		{
			$result_table .= '<tr><td>'.$result.' Mediafiles records updated</td></tr>';
		}
		
	}
$result_table .= '</table>';
echo $result_table;
?>
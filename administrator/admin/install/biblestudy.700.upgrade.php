<?php

/**
 * @author Tom Fuller - Joomla Bible Study
 * @copyright 2011
 */

defined( '_JEXEC' ) or die('Restricted access');
require_once ( JPATH_ROOT .DS.'libraries'.DS.'joomla'.DS.'html'.DS.'parameter.php' );


$db = JFactory::getDBO();

//Get all the media file records
$query = 'SELECT `id`, `params` FROM #__bsms_mediafiles';
$db->setQuery($query);
$db->query();
$results = $db->loadObjectList();
//Now run through all the results, pull out the media player and the popup type and move them to their respective db fields
foreach ($results AS $result)
{
    $params = new JParameter($result->params);
    $player = $params->get('player');
    $popup = $params->get('internal_popup');
    if ($player)
    {
        if ($player == 2)
        {
            $player = 3;
        }
        $query = "UPDATE #__bsms_mediafiles SET `player` = '$player' WHERE `id` = $result->id LIMIT 1";
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS').' '.$error;
                }
    }
    if ($popup)
    {
        $query = "UPDATE #__bsms_mediafiles SET `popup` = '$popup' WHERE `id` = $result->id LIMIT 1";
        $db->setQuery($query);
        $db->query();
        if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_CMN_ERROR_RESETTING_PLAYS').' '.$error;
                }
    }
}
$res = '<table><tr><td>This routine moves player and popup from a parameter to new database fields.</td></tr>';  //santon 2010-12-28 convert to phrase
if (!$msg){$res .= JText::_('JBS_INS_NO_ERROR');}
else
{
    $msg = array();
    $r = 'Errors: <br />';
    foreach ($msg AS $m)
    {
        $r .= $m.'<br />';
    }
}
$result_table .= '<tr>
		<td>
			<div id="id_1" onClick="javascript:showDetail(this);" style="cursor:pointer;">
				<img id="__img" src="images/expandall.png" border="0">
				Executing PHP Code
			</div>
			<div id=_details" style="display:None;" class="details">'.$res.$r.'</div>
		</td>
		<td width="20" valign="top"><img src="images/collapseall.png" border="0"></td>
	</tr>';

$result_table .= '</td></tr></table>';
echo $result_table;
?>
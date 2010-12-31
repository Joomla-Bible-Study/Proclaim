<?php

/**
 * @author Tom Fuller - Joomla Bible Study
 * @copyright 2011
 */

defined( '_JEXEC' ) or die('Restricted access');
require_once ( JPATH_ROOT .DS.'libraries'.DS.'joomla'.DS.'html'.DS.'parameter.php' );


$db = JFactory::getDBO();
$msg = array();
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
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
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
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                }
    }
    
}
//Get all the study records

$query = "UPDATE #__bsms_studies SET `show_level` = '1' WHERE `show_level` = '0'";
$db->setQuery($query);
$db->query();
if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                }
$query = "UPDATE #__bsms_studies SET `show_level` = '2' WHERE `show_level` = '18'";
$db->setQuery($query);
$db->query();
if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                }
$query = "UPDATE #__bsms_studies SET `show_level` = '3' WHERE `show_level` = '19'";
$db->setQuery($query);
$db->query();
if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                }
$query = "UPDATE #__bsms_studies SET `show_level` = '4' WHERE `show_level` = '20'";
$db->setQuery($query);
$db->query();
if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                }
$query = "UPDATE #__bsms_studies SET `show_level` = '5' WHERE `show_level` = '22'";
$db->setQuery($query);
$db->query();
if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                }
$query = "UPDATE #__bsms_studies SET `show_level` = '6' WHERE `show_level` = '23'";
$db->setQuery($query);
$db->query();
if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                }
$query = "UPDATE #__bsms_studies SET `show_level` = '7' WHERE `show_level` = '24'";
$db->setQuery($query);
$db->query();
if ($db->getErrorNum() > 0)
				{
					$error = $db->getErrorMsg();
                    $msg[] = JText::_('JBS_ADM_ERROR_OCCURED').' '.$error;
                }
$res = '<table><tr><td>This routine moves player and popup from a parameter to new database fields and adjusts user groups for the studies show_level.</td></tr>';  //santon 2010-12-28 convert to phrase
if (count($msg) < 1){$res .= JText::_('JBS_INS_NO_ERROR');}
else
{
    
    $r = 'Errors: <br />';
    foreach ($msg AS $m)
    {
        $r .= $m.'<br />';
    }
}
$result_table = '<tr>
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
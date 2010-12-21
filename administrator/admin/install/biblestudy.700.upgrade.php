<?php

/**
 * @author Tom Fuller - Joomla Bible Study
 * @copyright 2011
 */

defined( '_JEXEC' ) or die('Restricted access');
require_once ( JPATH_ROOT .DS.'libraries'.DS.'joomla'.DS.'html'.DS.'parameter.php' );
$result_table = '<table><tr><td>This routine updates the mediafiles table to move the player type parameter to its own field in the database</td></tr>';

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
$res = implode('<br />',$msg);

$result_table .= '<tr><td>Results:'.$res.'</td></tr></table>';
echo $result_table;
?>
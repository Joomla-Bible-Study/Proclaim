<?php

/**
 * @author Tom Fuller
 * @copyright 2010
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

class JBSAdmin
{
    
    function getMediaPlayer()
{
    $db = JFactory::getDBO();
   $query = "Select #__components.name FROM #__components WHERE #__components.name LIKE '%AvReloaded%'";
   $db->setQuery($query);
   $db->query();
   $num_rows = $db->getNumRows(); 
    if ($num_rows)
    {
        $player = 'avr';
    }
    else
    {
        $player = false;
    }
    $query = 'SELECT element, published FROM #__plugins WHERE #__plugins.element LIKE "%jw_allvideos%"';
    $db->setQuery($query);
    $db->query();
    $num_rows = $db->getNumRows();
    $isav = $db->loadObject($query);
    if ($num_rows && $isav->published == 1){$player = 'av';}
    return $player; //dump ($player, 'player: ');
 }
}

?>
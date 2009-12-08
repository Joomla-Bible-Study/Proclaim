<?php defined('_JEXEC') or die('Restriced Access');
/**
 * @author Joomla Bible Study
 * @copyright 2009
 * @desc This is a general helper file. In the future we should bring lots of small functions into this file
 */
//This function is designed to extract an Itemid for the component if none exists in the GET variable. Mainly to address problems with 
// All Videos Reloaded
 function getItemidLink(){
  $itemid = null;
  $component =& JComponentHelper::getComponent('com_biblestudy');
  $menus  = &JApplication::getMenu('site', array());
  $activeMenu = $menus->getActive();
  if ($activeMenu->componentid == $component->id) {
   $itemid = $activeMenu->id;
  } else {
   $items  = $menus->getItems('componentid', $component->id);
   foreach ($items as &$menu) {
    if (@$menu->query['view'] == 'studieslist') {
     $itemid = $menu->id;
     break;
    }
   }
   if (!isset($itemid) && count($items)) {
    $itemid = $items[0]->id;
   }
  } //dump ($itemid, 'helper: ');
  return($itemid ? $itemid : '');
 }

?>
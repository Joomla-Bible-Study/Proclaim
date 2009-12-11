<?php defined('_JEXEC') or die('Restriced Access');
/**
 * @author Joomla Bible Study
 * @copyright 2009
 * @desc This is a general helper file. In the future we should bring lots of small functions into this file
 */
//This function is designed to extract an Itemid for the component if none exists in the GET variable. Mainly to address problems with 
// All Videos Reloaded
 function getItemidLink($isplugin, $admin_params){
  $itemid = null;
  $component =& JComponentHelper::getComponent('com_biblestudy');
  $menus  = &JApplication::getMenu('site', array()); 
  if ($menus->_active > 0)
  {
  	$activeMenu = $menus->getActive(); 
	if ($activeMenu->componentid == $component->id) 
  	{
   		$itemid = $activeMenu->id;
  	}
  }
   else {
   $items  = $menus->getItems('componentid', $component->id);
   foreach ($items as &$menu) {
    if (@$menu->query['view'] == $admin_params->get('itemidlinkview','studieslist')) {
     $itemid = $menu->id;
     break;
    }
   }
   if (!isset($itemid) && count($items)) {
    $itemid = $items[0]->id;
   }
  } //dump ($itemid, 'helper: ');
  	$itemidprefix = '&Itemid=';
	if ($isplugin > 0){$itemidprefix = '&amp;Itemid=';}
  switch ($admin_params->get('itemidlinktype'))
   			{
			   	case 0:
			   	$itemid = '';
			   	return $itemid;
			   	break;
			   	
			   	case 1:
   				//Look for an itemid in the com_menu table from the /helpers/helper.php file
   				$itemid = $itemidprefix.$itemid;
   				return ($itemid ? $itemid : '');
   				break;
   				
			   	case 2:
   				//Add in an Itemid from the parameter
   				$itemid = $itemidprefix.$admin_params->get('itemidlinknumber',1);
   				return ($itemid ? $itemid : '');
   				break;
   			} 
  //return($itemid ? $itemid : '');
 }
function getAdminsettings()
	{
			$db =& JFactory::getDBO();
			$query = 'SELECT *'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$db->setQuery($query);
			$adminsettings = $db->loadObject(); //dump ($adminsettings, 'adminsettings: ');
			
		
		return $adminsettings;
	}
?>
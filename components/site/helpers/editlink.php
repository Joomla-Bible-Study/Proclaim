<?php
defined('_JEXEC') or die();

function getEditlink($id, $params) {
	
	$user =& JFactory::getUser();
	$entry_user = $user->get('gid');
	if (!$entry_user) { $entry_user = 0;}
	$entry_access = $params->get('entry_access');
	if (!$entry_access) {$entry_access = 23;}
	$allow_entry = $params->get('allow_entry_study');
	if (!$entry_user) { $entry_user = 0; }
	if (!$entry_access) { $entry_access = 23; }
	if ($allow_entry > 0) {

if ($entry_user >= $entry_access){
				$editlink .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]='.$id.'">'.JText::_('[Edit]').'</a>';
}
	}
else {$editlink = null;}                    
   return $editlink;

}
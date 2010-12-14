<?php
defined('_JEXEC') or die();
require_once (JPATH_ROOT  .DS. 'components' .DS. 'com_biblestudy' .DS. 'lib' .DS. 'biblestudy.admin.class.php');
function getEditlink($id, $params) 
{

	$admin = new JBSAdmin();
    $allow = $admin->getPermission();
    
   
    if ($allow)
        {
            $editlink .= '<a href="'.JURI::base().'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]='.$id.'">['.JText::_('JBS_CMN_EDIT').']</a>';
        }
	
    else {$editlink = null;}
 return $editlink;

}

<?php

/**
 * @version $Id: editlink.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 **/

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
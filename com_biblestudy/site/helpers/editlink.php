<?php

/**
 * EditLink Helper
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
// --JLoader::register('JBSAdmin', JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.admin.class.php');
JLoader::register('JBSAdmin', JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.admin.class.php');

/**
 * Get Edit Link
 *
 * @param   int     $id      Id of Study
 * @param   object  $params  Params form Admin
 *
 * @return string|null
 */
function getEditlink($id, $params)
{

	$admin    = new JBSAdmin;
	$allow    = $admin->getPermission();
	$editlink = null;

	if ($allow)
	{
		$editlink .= '<a href="' . JURI::base() . 'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]=' .
			$id . '">[' . JText::_('JBS_CMN_EDIT') . ']</a>';
	}

	return $editlink;
}

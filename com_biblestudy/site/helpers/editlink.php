<?php

/**
 * EditLink Helper
 *
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
//require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
JLoader::register('JBSAdmin', dirname(__FILE__) . '/lib/biblestudy.admin.class.php');

/**
 * Get Edit Link
 *
 * @param int    $id
 * @param object $params
 *
 * @return string|null
 */
function getEditlink($id, $params)
{

	$admin    = new JBSAdmin();
	$allow    = $admin->getPermission();
	$editlink = null;
	if ($allow) {
		$editlink .= '<a href="' . JURI::base() . 'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&task=edit&layout=form&cid[]=' . $id . '">[' . JText::_('JBS_CMN_EDIT') . ']</a>';
	}

	return $editlink;
}
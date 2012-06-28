<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');

/**
 *
 * @param type $admin_params
 * @param type $params
 * @return null
 */
function getEditlisting($admin_params, $params) {

    $mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $database = JFactory::getDBO();
    $editlisting = null;
    $message = JRequest::getVar('msg');
    $user = JFactory::getUser();
    $admin = new JBSAdmin();
    $allow = $admin->getPermission();
    if ($allow) {

        if ($message) {
            $editlisting .= '<div class="message' . $params->get('pageclass_sfx') . '"><h2>' . $message . '</h2></div>';
        } //End of if $message

        $editlisting .= '<div id="studyheader">' . JText::_('JBS_CMN_STUDIES') . '</div>';
        $editlisting .= '<div class="studyedit">';
        $editlisting .= '<a href="' . JURI::base() . 'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&layout=form">' . JText::_('JBS_CMN_ADD_STUDY') . '</a><br />';
        $editlisting .= '<a href="' . JURI::base() . 'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form">' . JText::_('JBS_CMN_ADD_MEDIA') . '</a><br />';
        if ($params->get('show_comments') > 0) {
            $editlisting .= '<a href="' . JURI::base() . 'index.php?option=com_biblestudy&view=commentslist">' . JText::_('JBS_CMN_MANAGE_COMMENTS') . '</a><br /><br />';
            $editlisting .= '</div>';
        } //end if show_comments
    }//End if $allow
    else {
        $editlisting = null;
    }
    return $editlisting;
}
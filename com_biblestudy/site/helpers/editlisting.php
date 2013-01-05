<?php

/**
 * EditListing Helper
 *
 * @package    BibleStudy.Site
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

JLoader::register('JBSAdmin', JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.admin.class.php');

/**
 * Get Edit Listing
 *
 * @param   object  $admin_params  Admin Params
 * @param   object  $params        Not Sure What this is
 *
 * @return string|null
 */
function getEditlisting($admin_params, $params)
{

	$mainframe   = JFactory::getApplication();
	$input       = new JInput;
	$option      = $input->get('option', '', 'cmd');
	$database    = JFactory::getDBO();
	$editlisting = null;
	$message     = $input->get('msg');
	$user        = JFactory::getUser();
	$admin       = new JBSAdmin;
	$allow       = $admin->getPermission();
	if ($allow)
	{

		if ($message)
		{
			$editlisting .= '<div class="message' . $params->get('pageclass_sfx') . '"><h2>' . $message . '</h2></div>';

		} // End of if $message

		$editlisting .= '<div id="studyheader">' . JText::_('JBS_CMN_STUDIES') . '</div>';
		$editlisting .= '<div class="studyedit">';
		$editlisting .= '<a href="' . JURI::base() . 'index.php?option=com_biblestudy&controller=studiesedit&view=studiesedit&layout=form">'
			. JText::_('JBS_CMN_ADD_STUDY') . '</a><br />';
		$editlisting .= '<a href="' . JURI::base() . 'index.php?option=com_biblestudy&controller=mediafilesedit&view=mediafilesedit&layout=form">'
			. JText::_('JBS_CMN_ADD_MEDIA') . '</a><br />';

		if ($params->get('show_comments') > 0)
		{
			$editlisting .= '<a href="' . JURI::base() . 'index.php?option=com_biblestudy&view=commentslist">' . JText::_(
				'JBS_CMN_MANAGE_COMMENTS'
			) . '</a><br /><br />';
			$editlisting .= '</div>';

		} // End if show_comments

	} // End if $allow
	else
	{
		$editlisting = null;
	}

	return $editlisting;
}
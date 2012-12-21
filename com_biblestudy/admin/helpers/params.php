<?php

/**
 * Params Helper
 *
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

/**
 * This is for Retrieving Admin and Template db
 *
 * @package BibleStudy.Admin
 * @since   7.0.0
 */
class JBSMParams
{

	public static $extension = 'com_biblestudy';

	/**
	 * Gets the settings from Admin
	 *
	 * @return object Return Admin table
	 */
	public static function getAdmin()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')
				->from('#__bsms_admin')
				->where("`id` = '1'");
		$db->setQuery($query);
		$admin = $db->loadObject();
		$registry = new JRegistry();
		$registry->loadString($admin->params);
		$admin->params = $registry;
		//Add the current user id
		$user = JFactory::getUser();
		$admin->user_id = $user->id;
		return $admin;
	}

	/**
	 * Get Template Params
	 *
	 * @return object Return active template info
	 */
	public static function getTemplateparams()
	{
		$db = JFactory::getDbo();
		$pk = JFactory::getApplication()->input->getInt('t', '1');
		$query = $db->getQuery(true);
		$query->select('*')
				->from('#__bsms_templates')
				->where('published = 1 AND id = ' . $db->q($pk));
		$db->setQuery($query);
		$template = $db->loadObject();
		if ($template) {
			$registry = new JRegistry();
			$registry->loadString($template->params);
			$template->params = $registry;
			return $template;
		}
		return false;
	}

}
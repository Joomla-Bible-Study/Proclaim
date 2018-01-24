<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2018 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * This is for Retrieving Admin and Template db
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 *
 * @property $template->params Registry
 */
class JBSMParams
{
	/**
	 * Extension Name
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_biblestudy';

	/** @var  Object Admin Table
	 *
	 * @since 1.5 */
	public static $admin;

	/** @var  Object Template Table
	 *
	 * @since 1.5 */
	public static $template_table;

	/** @var int Default template id and used to check if changed form from last query
	 *
	 * @since 1.5 */
	public static $t_id = 1;

	/**
	 * Gets the settings from Admin
	 *
	 * @return mixed Return Admin table
	 *
	 * @since 7.0
	 */
	public static function getAdmin()
	{
		if (!self::$admin)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__bsms_admin')
				->where($db->qn('id') . ' = ' . (int) 1);
			$db->setQuery($query);
			$admin    = $db->loadObject();
			$registry = new Registry;
			$registry->loadString($admin->params);
			$admin->params = $registry;

			// Add the current user id
			$user           = JFactory::getUser();
			$admin->user_id = $user->id;

			self::$admin = $admin;
		}

		return self::$admin;
	}

	/**
	 * Get Template Params
	 *
	 * @param   int  $pk  Id of Template to look for
	 *
	 * @return TableTemplate Return active template info
	 *
	 * @since 7.0
	 */
	public static function getTemplateparams($pk = null)
	{
		$db = JFactory::getDbo();

		if (!$pk)
		{
			$pk = JFactory::getApplication()->input->getInt('t', '1');
		}

		if (self::$t_id != $pk || !self::$template_table)
		{
			self::$t_id = $pk;
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__bsms_templates')
				->where('published = ' . (int) 1)
				->where('id = ' . (int) self::$t_id);
			$db->setQuery($query);
			$template = $db->loadObject();

			if (!$template)
			{
				self::$t_id = 1;
				$query = $db->getQuery(true);
				$query->select('*')
					->from('#__bsms_templates')
					->where('published = ' . (int) 1)
					->where('id = ' . (int) self::$t_id);
				$db->setQuery($query);
				$template = $db->loadObject();
			}

			if ($template)
			{
				$registry = new Registry;
				$registry->loadString($template->params);
				$template->params = $registry;
			}
			else
			{
				$template = new stdClass;
				$template->params = new Registry;
			}

			self::$template_table = $template;
		}

		return self::$template_table;
	}
}

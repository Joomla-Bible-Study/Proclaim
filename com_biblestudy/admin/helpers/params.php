<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2015 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * This is for Retrieving Admin and Template db
 *
 * @package  BibleStudy.Admin
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
	 */
	public static $extension = 'com_biblestudy';

	/** @var  Object Admin Table */
	public static $admin;

	/** @var  Object Template Table */
	public static $template_table;

	/** @var int Default template id and used to check if changed form from last query */
	public static $t_id = 1;

	/**
	 * Gets the settings from Admin
	 *
	 * @return mixed Return Admin table
	 */
	public static function getAdmin()
	{
		if (JBSMDbHelper::checkIfTable('#__bsms_admin') && !self::$admin)
		{
			$db    = JFactory::getDBO();
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
		elseif (!self::$admin)
		{
			return false;
		}

		return self::$admin;
	}

	/**
	 * Get Template Params
	 *
<<<<<<< HEAD
	 * @param   int  $pk  Id of Template to look for
	 *
=======
>>>>>>> Joomla-Bible-Study/master
	 * @return TableTemplate Return active template info
	 */
	public static function getTemplateparams($pk = null)
	{
		$db = JFactory::getDbo();
<<<<<<< HEAD
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

			if ($template)
			{
				$registry = new Registry;
				$registry->loadString($template->params);
				$template->params = $registry;
			}
			self::$template_table = $template;
		}

		return self::$template_table;
=======
		$pk = JFactory::getApplication()->input->getInt('t', 1);

		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__bsms_templates')
			->where('published = ' . (int) 1)
			->where('id = ' . (int) $pk);
		$db->setQuery($query);
		$template = $db->loadObject();

		if ($template)
		{
			$registry = new JRegistry;
			$registry->loadString($template->params);
			$template->params = $registry;
			return $template;
		}

		return false;
>>>>>>> Joomla-Bible-Study/master
	}

}

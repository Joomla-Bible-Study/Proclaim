<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Database\DatabaseFactory;
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
	public static string $extension = 'com_biblestudy';

	/** @var  object Admin Table
	 *
	 * @since 1.5
	 */
	public static $admin;

	/** @var  object Template Table
	 *
	 * @since 1.5
	 */
	public static $template_table;

	/** @var integer Default template id and used to check if changed form from last query
	 *
	 * @since 1.5
	 */
	public static int $t_id = 1;

	/**
	 * Gets the settings from Admin
	 *
	 * @return object Return Admin table
	 *
	 * @throws Exception
	 * @since 7.0
	 */
	public static function getAdmin()
	{
		if (!self::$admin)
		{
			$app    = JFactory::getApplication();
			$driver = new DatabaseFactory;
			$db     = $driver->getDriver();
			$query  = $db->getQuery(true);
			$query->select('*')
				->from('#__bsms_admin')
				->where($db->qn('id') . ' = ' . (int) 1);
			$db->setQuery($query);
			$admin = $db->loadObject();

			if (isset($admin->params))
			{
				$registry = new Registry;

				// Used to Catch Jason Error's
				try
				{
					$registry->loadString($admin->params);
				}
				catch (Exception $e)
				{
					$msg = $e->getMessage();
					$app->enqueueMessage('Can\'t load Admin Params - ' . $msg, 'error');
				}

				$admin->params = $registry;

				// Add the current user id
				$user           = $app->getIdentity();
				$admin->user_id = $user->id;
			}

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
	 * @throws Exception
	 * @since 7.0
	 */
	public static function getTemplateparams($pk = null)
	{
		$driver = new DatabaseFactory;
		$db     = $driver->getDriver();

		if (!$pk)
		{
			$pk = JFactory::getApplication()->input->getInt('t', '1');
		}

		if (self::$t_id !== $pk || !self::$template_table)
		{
			self::$t_id = $pk;
			$query      = $db->getQuery(true);
			$query->select('*')
				->from('#__bsms_templates')
				->where('published = ' . (int) 1)
				->where('id = ' . (int) self::$t_id);
			$db->setQuery($query);
			$template = $db->loadObject();

			if (!$template)
			{
				self::$t_id = 1;
				$query      = $db->getQuery(true);
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
				$template         = new stdClass;
				$template->params = new Registry;
			}

			self::$template_table = $template;
		}

		return self::$template_table;
	}

	/**
	 * Update Component Params
	 *
	 * @param   array  $param_array  Array ('name' => 'params')
	 *
	 * @return void
	 *
	 * @throws \JsonException
	 * @since 9.1.5
	 */
	public static function setCompParams($param_array)
	{
		if (count($param_array) > 0)
		{
			// Read the existing component value(s)
			$driver = new DatabaseFactory;
			$db     = $driver->getDriver();
			$query  = $db->getQuery(true);
			$query->select('params')
				->from('#__extensions')
				->where('name = ' . $db->q('com_biblestudy'));
			$db->setQuery($query);
			$params = json_decode($db->loadResult(), true, 512, JSON_THROW_ON_ERROR);

			// Add the new variable(s) to the existing one(s)
			foreach ($param_array as $name => $value)
			{
				$params[(string) $name] = (string) $value;
			}

			// Store the combined new and existing values back as a JSON string
			$paramsString = json_encode($params, JSON_THROW_ON_ERROR);
			$query->clear();
			$query->update('#__extensions')
				->set('params = ' . $db->q($paramsString))
				->where('name = ' . $db->q('com_biblestudy'));
			$db->setQuery($query);
			$db->execute();
		}
	}
}

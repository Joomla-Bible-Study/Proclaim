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

/**
 * BibleStudy Helper class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class JBSMBibleStudyHelper
{
	public static $admin_params = null;

	/**
	 * Set extension
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Get Actions
	 *
	 * @param   int     $Itemid  ID
	 * @param   string  $type    Type
	 *
	 * @return JObject
	 *
	 * @since 1.5
	 */
	public static function getActions($Itemid = 0, $type = '')
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		if (empty($Itemid))
		{
			$assetName = 'com_biblestudy';
		}
		else
		{
			switch ($type)
			{
				case 'admin':
					$assetName = 'com_biblestudy.admin.' . (int) $Itemid;
					break;
				case 'assets':
					$assetName = 'com_biblestudy.assets.' . (int) $Itemid;
					break;
				case 'backup':
					$assetName = 'com_biblestudy.backup.' . (int) $Itemid;
					break;
				case 'comment':
					$assetName = 'com_biblestudy.comment.' . (int) $Itemid;
					break;
				case 'database':
					$assetName = 'com_biblestudy.database.' . (int) $Itemid;
					break;
				case 'location':
					$assetName = 'com_biblestudy.location.' . (int) $Itemid;
					break;
				case 'messagetype':
					$assetName = 'com_biblestudy.messagetype.' . (int) $Itemid;
					break;
				case 'migrate':
					$assetName = 'com_biblestudy.migrate.' . (int) $Itemid;
					break;

				case 'podcast':
					$assetName = 'com_biblestudy.podcast.' . (int) $Itemid;
					break;

				case 'serie':
					$assetName = 'com_biblestudy.serie.' . (int) $Itemid;
					break;

				case 'server':
					$assetName = 'com_biblestudy.server.' . (int) $Itemid;
					break;

				case 'teacher':
					$assetName = 'com_biblestudy.teacher.' . (int) $Itemid;
					break;

				case 'template':
					$assetName = 'com_biblestudy.template.' . (int) $Itemid;
					break;

				case 'topic':
					$assetName = 'com_biblestudy.topic.' . (int) $Itemid;
					break;

				case 'message':
					$assetName = 'com_biblestudy.message.' . (int) $Itemid;
					break;

				case 'mediafile':
					$assetName = 'com_biblestudy.mediafile.' . (int) $Itemid;
					break;

				case 'templatecode':
					$assetName = 'com_biblestudy.templatecode' . (int) $Itemid;
					break;

				default:
					$assetName = 'com_biblestudy.studiesedit.' . (int) $Itemid;
					break;
			}
		}

		$actions = array(
			'core.admin',
			'core.manage',
			'core.create',
			'core.edit',
			'core.edit.own',
			'core.edit.state',
			'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public static function addSubmenu($vName)
	{
		$admin = JBSMParams::getAdmin();
		$simple = $admin->params->get('simple_mode', '0');
		self::rendermenu(
			JText::_('JBS_CMN_CONTROL_PANEL'), 'index.php?option=com_biblestudy&view=cpanel', $vName == 'cpanel'
		);
		self::rendermenu(
			JText::_('JBS_CMN_ADMINISTRATION'), 'index.php?option=com_biblestudy&task=admin.edit&id=1', $vName == 'admin'
		);
		self::rendermenu(
			JText::_('JBS_CMN_STUDIES'), 'index.php?option=com_biblestudy&view=messages', $vName == 'messages'
		);
		self::rendermenu(
			JText::_('JBS_CMN_MEDIA_FILES'), 'index.php?option=com_biblestudy&view=mediafiles', $vName == 'mediafiles'
		);
		self::rendermenu(
			JText::_('JBS_CMN_TEACHERS'), 'index.php?option=com_biblestudy&view=teachers', $vName == 'teachers'
		);
		self::rendermenu(
			JText::_('JBS_CMN_SERIES'), 'index.php?option=com_biblestudy&view=series', $vName == 'series'
		);

		if (!$simple)
		{
			self::rendermenu(
				JText::_('JBS_CMN_MESSAGETYPES'), 'index.php?option=com_biblestudy&view=messagetypes', $vName == 'messagetypes'
			);
			self::rendermenu(
				JText::_('JBS_CMN_LOCATIONS'), 'index.php?option=com_biblestudy&view=locations', $vName == 'locations'
			);
			self::rendermenu(
				JText::_('JBS_CMN_TOPICS'), 'index.php?option=com_biblestudy&view=topics', $vName == 'topics'
			);
			self::rendermenu(
				JText::_('JBS_CMN_COMMENTS'), 'index.php?option=com_biblestudy&view=comments', $vName == 'comments'
			);
		}

		self::rendermenu(
			JText::_('JBS_CMN_SERVERS'), 'index.php?option=com_biblestudy&view=servers', $vName == 'servers'
		);
		self::rendermenu(
			JText::_('JBS_CMN_PODCASTS'), 'index.php?option=com_biblestudy&view=podcasts', $vName == 'podcasts'
		);

		if (!$simple)
		{
			self::rendermenu(
				JText::_('JBS_CMN_TEMPLATES'), 'index.php?option=com_biblestudy&view=templates', $vName == 'templates'
			);
			self::rendermenu(
				JText::_('JBS_CMN_TEMPLATECODE'), 'index.php?option=com_biblestudy&view=templatecodes', $vName == 'templatecodes'
			);
		}
	}

	/**
	 *  Rendering Menu based on Joomla! Version.
	 *
	 * @param   string  $text   Label
	 * @param   string  $url    Url of link
	 * @param   string  $vName  Name of view
	 *
	 * @return void
	 *
	 * @since 1.5
	 */
	public static function rendermenu($text, $url, $vName)
	{
		JHtmlSidebar::addEntry($text, $url, $vName);
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   string  $text  The string to filter
	 *
	 * @return string The filtered string
	 *
	 * @since 1.5
	 */
	public static function filterText($text)
	{
		// Filter settings
		jimport('joomla.application.component.helper');
		$config     = JComponentHelper::getParams('com_biblestudy');
		$user       = JFactory::getUser();
		$userGroups = JAccess::getGroupsByUser($user->get('id'));

		$filters = $config->get('filters');

		$blackListTags       = array();
		$blackListAttributes = array();

		$whiteListTags       = array();
		$whiteListAttributes = array();

		$whiteList  = false;
		$blackList  = false;
		$unfiltered = false;

		// Cycle through each of the user groups the user is in.
		// Remember they are include in the Public group as well.
		foreach ($userGroups AS $groupId)
		{
			// May have added a group by not saved the filters.
			if (!isset($filters->$groupId))
			{
				continue;
			}

			// Each group the user is in could have different filtering properties.
			$filterData = $filters->$groupId;
			$filterType = strtoupper($filterData->filter_type);

			if ($filterType == 'NH')
			{
				// Maximum HTML filtering.
			}
			else
			{
				if ($filterType == 'NONE')
				{
					// No HTML filtering.
					$unfiltered = true;
				}
				else
				{
					// Black or white list.
					// Prepossess the tags and attributes.
					$tags           = explode(',', $filterData->filter_tags);
					$attributes     = explode(',', $filterData->filter_attributes);
					$tempTags       = array();
					$tempAttributes = array();

					foreach ($tags AS $tag)
					{
						$tag = trim($tag);

						if ($tag)
						{
							$tempTags[] = $tag;
						}
					}

					foreach ($attributes AS $attribute)
					{
						$attribute = trim($attribute);

						if ($attribute)
						{
							$tempAttributes[] = $attribute;
						}
					}

					// Collect the black or white list tags and attributes.
					// Each list is cumulative.
					if ($filterType == 'BL')
					{
						$blackList           = true;
						$blackListTags       = array_merge($blackListTags, $tempTags);
						$blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
					}
					else
					{
						if ($filterType == 'WL')
						{
							$whiteList           = true;
							$whiteListTags       = array_merge($whiteListTags, $tempTags);
							$whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
						}
					}
				}
			}
		}

		// Remove duplicates before processing (because the black list uses both sets of arrays).
		$blackListTags       = array_unique($blackListTags);
		$blackListAttributes = array_unique($blackListAttributes);
		$whiteListTags       = array_unique($whiteListTags);
		$whiteListAttributes = array_unique($whiteListAttributes);

		// Unfiltered assumes first priority.
		if ($unfiltered)
		{
			$filter = JFilterInput::getInstance(array(), array(), 1, 1, 0);
		}
		else
		{
			// Black lists take second precedence.
			if ($blackList)
			{
				// Remove the white-listed attributes from the black-list.
				$filter = JFilterInput::getInstance(

				// Blacklisted tags
					array_diff($blackListTags, $whiteListTags),
					// Blacklisted attributes
					array_diff($blackListAttributes, $whiteListAttributes),
					// Blacklist tags
					1,
					// Blacklist attributes
					1
				);
			}
			else
			{
				// White lists take third precedence.
				if ($whiteList)
				{
					// Turn off xss auto clean
					$filter = JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);
				}
				else
				{
					// No HTML takes last place.
					$filter = JFilterInput::getInstance();
				}
			}
		}

		$text = $filter->clean($text, 'html');

		return $text;
	}

	/**
	 * Debug switch state form Admin Settings page
	 *
	 * @return int '1' is on '0' is off
	 *
	 * @since 7.1.0
	 */
	public static function debug()
	{
		if (!JBSMDbHelper::getInstallState())
		{
			self::$admin_params = JBSMParams::getAdmin();

			if (!isset(self::$admin_params->debug))
			{
				self::$admin_params        = new stdClass;
				self::$admin_params->debug = 1;
			}

			return self::$admin_params->debug;
		}

		return 0;
	}

	/**
	 * Media Types
	 *
	 * @since 8.0.0
	 * @depreciated 9.0.0
	 *
	 * @throws Exception For bad function
	 * @return void
	 */
	public static function getMediaTypes()
	{
		JLog::add('getMediaTypes is nologer supported', JLog::NOTICE, 'com_biblestudy');
		throw new Exception('Bad function getMediaTypes is nologer supported');
	}

	/**
	 * Media Years
	 *
	 * @return array        Returns list of years of media files based on createdate
	 *
	 * @since 8.0.0
	 */
	public static function getMediaYears()
	{
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT YEAR(createdate) as value, YEAR(createdate) as text');
		$query->from('#__bsms_mediafiles');
		$query->order('value');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Message Types
	 *
	 * @return array  Returns list of message types
	 *
	 * @since 8.0.0
	 */
	public static function getMessageTypes()
	{
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('messageType.id AS value, messageType.message_type AS text');
		$query->from('#__bsms_message_type AS messageType');
		$query->join('INNER', '#__bsms_studies AS study ON study.messagetype = messageType.id');
		$query->group('messageType.id');
		$query->order('messageType.message_type');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Study Years
	 *
	 * @return array Returns list of years of studies based on studydate
	 *
	 * @since 8.0.0
	 */
	public static function getStudyYears()
	{
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT YEAR(studydate) as value, YEAR(studydate) as text');
		$query->from('#__bsms_studies');
		$query->order('value');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Teachers
	 *
	 * @return array       Returns list of Teachers
	 *
	 * @since 8.0.0
	 */
	public static function getTeachers()
	{
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('teacher.id AS value, teacher.teachername AS text');
		$query->from('#__bsms_teachers AS teacher');
		$query->join('INNER', '#__bsms_studies AS study ON study.teacher_id = teacher.id');
		$query->group('teacher.id');
		$query->order('teacher.teachername');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Study Books
	 *
	 * @return array Returns list of books
	 *
	 * @since 8.0.0
	 */
	public static function getStudyBooks()
	{
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('book.booknumber AS value, book.bookname AS text, book.id');
		$query->from('#__bsms_books AS book');
		$query->join('INNER', '#__bsms_studies AS study ON study.booknumber = book.booknumber');
		$query->group('book.id');
		$query->order('book.booknumber');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		foreach ($options as $option)
		{
			$option->text = JText::_($option->text);
		}

		return $options;
	}

	/**
	 * Study Media Types
	 *
	 * @return array       Returns list of books
	 *
	 * @since 8.0.0
	 */
	public static function getStudyMediaTypes()
	{
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('messageType.id AS value, messageType.message_type AS text');
		$query->from('#__bsms_message_type AS messageType');
		$query->join('INNER', '#__bsms_studies AS study ON study.messagetype = messageType.id');
		$query->group('messageType.id');
		$query->order('messageType.message_type');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Study Locations
	 *
	 * @return array       Returns list of books
	 *
	 * @since 8.0.0
	 */
	public static function getStudyLocations()
	{
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id AS value, location_text AS text');
		$query->from('#__bsms_locations');
		$query->order('location_text ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Sorting the array a Column
	 *
	 * @param   array   &$arr  Array to sort
	 * @param   string  $col   Sort column
	 * @param   int     $dir   Direction to sort
	 *
	 * @return void applied back to the array
	 *
	 * @since 1.5
	 */
	public static function array_sort_by_column(&$arr, $col, $dir = SORT_ASC)
	{
		$sort_col = array();

		foreach ($arr as $key => $row)
		{
			$sort_col[$key] = $row[$col];
		}

		array_multisort($sort_col, $dir, $arr);
	}

	/**
	 * Debug stop
	 *
	 * @param   string  $msg  Message to sent.
	 *
	 * @return void
	 *
	 * @throws \Exception
	 *
	 * @since 1.5
	 */
	public static function stop($msg = '')
	{
		echo $msg;
		$mainframe = JFactory::getApplication();
		$mainframe->close();
	}

	/**
	 * String Starts With
	 *
	 * @param   string  $haystack  String to search.
	 * @param   string  $needle    What to search for.
	 *
	 * @return bool
	 *
	 * @since 1.5
	 */
	public static function startsWith($haystack, $needle)
	{
		// Search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
	}

	/**
	 * String Ends with.
	 *
	 * @param   string  $haystack  String to search.
	 * @param   string  $needle    What to search for.
	 *
	 * @return bool
	 *
	 * @since 1.5
	 */
	public static function endsWith($haystack, $needle)
	{
		// Search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}
}

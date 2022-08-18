<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

namespace CWM\Component\Proclaim\Administrator\Helper;

// No Direct Access
use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use stdClass;

defined('_JEXEC') or die;


/**
 * BibleStudy Helper class
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
class CWMProclaimHelper
{
	/**
	 * Admin Prams
	 *
	 * @var object
	 *
	 * @since 1.5
	 */
	public static $admin_params = null;

	/**
	 * Set extension
	 *
	 * @var string
	 *
	 * @since 1.5
	 */
	public static $extension = 'com_proclaim';

	/**
	 * Get Actions
	 *
	 * @param   int     $Itemid  ID
	 * @param   string  $type    Type
	 *
	 * @return \Joomla\Registry\Registry
	 *
	 * @throws \Exception
	 * @since 1.5
	 */
	public static function getActions($Itemid = 0, $type = '')
	{
		$result = new Registry;
		$user   = Factory::getApplication()->getIdentity();

		if (empty($Itemid))
		{
			$assetName = 'com_proclaim';
		}
		else
		{
			switch ($type)
			{
				case 'administrator':
					$assetName = 'com_proclaim.administration.' . (int) $Itemid;
					break;
				case 'assets':
					$assetName = 'com_proclaim.assets.' . (int) $Itemid;
					break;
				case 'backup':
					$assetName = 'com_proclaim.backup.' . (int) $Itemid;
					break;
				case 'comment':
					$assetName = 'com_proclaim.comment.' . (int) $Itemid;
					break;
				case 'database':
					$assetName = 'com_proclaim.database.' . (int) $Itemid;
					break;
				case 'location':
					$assetName = 'com_proclaim.location.' . (int) $Itemid;
					break;
				case 'messagetype':
					$assetName = 'com_proclaim.messagetype.' . (int) $Itemid;
					break;
				case 'migrate':
					$assetName = 'com_proclaim.migrate.' . (int) $Itemid;
					break;

				case 'podcast':
					$assetName = 'com_proclaim.podcast.' . (int) $Itemid;
					break;

				case 'serie':
					$assetName = 'com_proclaim.serie.' . (int) $Itemid;
					break;

				case 'server':
					$assetName = 'com_proclaim.server.' . (int) $Itemid;
					break;

				case 'teacher':
					$assetName = 'com_proclaim.teacher.' . (int) $Itemid;
					break;

				case 'template':
					$assetName = 'com_proclaim.template.' . (int) $Itemid;
					break;

				case 'topic':
					$assetName = 'com_proclaim.topic.' . (int) $Itemid;
					break;

				case 'message':
					$assetName = 'com_proclaim.message.' . (int) $Itemid;
					break;

				case 'mediafile':
					$assetName = 'com_proclaim.mediafile.' . (int) $Itemid;
					break;

				case 'templatecode':
					$assetName = 'com_proclaim.templatecode' . (int) $Itemid;
					break;

				default:
					$assetName = 'com_proclaim.studiesedit.' . (int) $Itemid;
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
	 * @throws \Exception
	 * @since    1.6
	 */
	public static function addSubmenu($vName)
	{
		$simple_view = CWMHelper::getSimpleView();

		self::rendermenu(
			Text::_('JBS_CMN_CONTROL_PANEL'), 'index.php?option=com_proclaim&view=cpanel', $vName == 'cpanel'
		);
		self::rendermenu(
			Text::_('JBS_CMN_ADMINISTRATION'), 'index.php?option=com_proclaim&task=administration.edit&id=1', $vName == 'administrator'
		);
		self::rendermenu(
			Text::_('JBS_CMN_STUDIES'), 'index.php?option=com_proclaim&view=messages', $vName == 'messages'
		);
		self::rendermenu(
			Text::_('JBS_CMN_MEDIA_FILES'), 'index.php?option=com_proclaim&view=mediafiles', $vName == 'mediafiles'
		);
		self::rendermenu(
			Text::_('JBS_CMN_TEACHERS'), 'index.php?option=com_proclaim&view=teachers', $vName == 'teachers'
		);
		self::rendermenu(
			Text::_('JBS_CMN_SERIES'), 'index.php?option=com_proclaim&view=series', $vName == 'series'
		);

		if (!$simple_view->mode)
		{
			self::rendermenu(
				Text::_('JBS_CMN_MESSAGETYPES'), 'index.php?option=com_proclaim&view=messagetypes', $vName == 'messagetypes'
			);
			self::rendermenu(
				Text::_('JBS_CMN_LOCATIONS'), 'index.php?option=com_proclaim&view=locations', $vName == 'locations'
			);
			self::rendermenu(
				Text::_('JBS_CMN_TOPICS'), 'index.php?option=com_proclaim&view=topics', $vName == 'topics'
			);
			self::rendermenu(
				Text::_('JBS_CMN_COMMENTS'), 'index.php?option=com_proclaim&view=comments', $vName == 'comments'
			);
		}

		self::rendermenu(
			Text::_('JBS_CMN_SERVERS'), 'index.php?option=com_proclaim&view=servers', $vName == 'servers'
		);
		self::rendermenu(
			Text::_('JBS_CMN_PODCASTS'), 'index.php?option=com_proclaim&view=podcasts', $vName == 'podcasts'
		);

		if (!$simple_view->mode)
		{
			self::rendermenu(
				Text::_('JBS_CMN_TEMPLATES'), 'index.php?option=com_proclaim&view=templates', $vName == 'templates'
			);
			self::rendermenu(
				Text::_('JBS_CMN_TEMPLATECODE'), 'index.php?option=com_proclaim&view=templatecodes', $vName == 'templatecodes'
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
		//JHtmlSidebar::addEntry($text, $url, $vName);
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
		$config     = ComponentHelper::getParams('com_proclaim');
		$user       = Factory::getApplication()->getIdentity();
		$userGroups = Access::getGroupsByUser($user->get('id'));

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
		foreach ($userGroups as $groupId)
		{
			// May have added a group by not saved the filters.
			if (!isset($filters->$groupId))
			{
				continue;
			}

			// Each group the user is in could have different filtering properties.
			$filterData = $filters->$groupId;
			$filterType = strtoupper($filterData->filter_type);

			if ($filterType !== 'NH')
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

					foreach ($tags as $tag)
					{
						$tag = trim($tag);

						if ($tag)
						{
							$tempTags[] = $tag;
						}
					}

					foreach ($attributes as $attribute)
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
			$filter = InputFilter::getInstance(array(), array(), 1, 1, 0);
		}
		else
		{
			// Black lists take second precedence.
			if ($blackList)
			{
				// Remove the white-listed attributes from the black-list.
				$filter = InputFilter::getInstance(
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
					$filter = InputFilter::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);
				}
				else
				{
					// No HTML takes last place.
					$filter = InputFilter::getInstance();
				}
			}
		}

		return $filter->clean($text, 'html');
	}

	/**
	 * Debug switch state form Admin Settings page
	 *
	 * @return integer '1' is on '0' is off
	 *
	 * @throws \Exception
	 * @since 7.1.0
	 */
	public static function debug()
	{
		if (!CWMDbHelper::getInstallState())
		{
			self::$admin_params = CWMParams::getAdmin();

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
	 * @return void
	 * @throws \Exception For bad function
	 * @since       8.0.0
	 * @depreciated 9.0.0
	 *
	 */
	public static function getMediaTypes()
	{
		Log::add('getMediaTypes is nologer supported', Log::NOTICE, 'com_proclaim');
		throw new \Exception('Bad function getMediaTypes is nologer supported');
	}

	/**
	 * Media Years
	 *
	 * @return array        Returns list of years of media files based on createdate
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public static function getMediaYears()
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');
		//$db      = $driver->getDriver();
		$query   = $db->getQuery(true);

		$query->select('DISTINCT YEAR(createdate) as value, YEAR(createdate) as text');
		$query->from('#__bsms_mediafiles');
		$query->order('value');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Message Types
	 *
	 * @return array  Returns list of message types
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public static function getMessageTypes()
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');
		//$db      = $driver->getDriver();
		$query   = $db->getQuery(true);

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
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Study Years
	 *
	 * @return array Returns list of years of studies based on studydate
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public static function getStudyYears()
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');
		//$db      = $driver->getDriver();
		$query   = $db->getQuery(true);

		$query->select('DISTINCT YEAR(studydate) as value, YEAR(studydate) as text');
		$query->from('#__bsms_studies');
		$query->order('value');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Teachers
	 *
	 * @return array       Returns list of Teachers
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public static function getTeachers()
	{
		$options = array();
		$driver  = Factory::getContainer()->get('DatabaseDriver');
		$db      = $driver->getDriver();
		$query   = $db->getQuery(true);

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
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Study Books
	 *
	 * @return array Returns list of books
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public static function getStudyBooks()
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');
		//$db      = $driver->getDriver();
		$query   = $db->getQuery(true);

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
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		foreach ($options as $option)
		{
			$option->text = Text::_($option->text);
		}

		return $options;
	}

	/**
	 * Study Media Types
	 *
	 * @return array       Returns list of books
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public static function getStudyMediaTypes()
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');
		//$db      = $driver->getDriver();
		$query   = $db->getQuery(true);

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
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Study Locations
	 *
	 * @return array       Returns list of books
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public static function getStudyLocations()
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');
		//$db      = $driver->getDriver();
		$query   = $db->getQuery(true);

		$query->select('id AS value, location_text AS text');
		$query->from('#__bsms_locations');
		$query->order('location_text ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'worning');
		}

		return $options;
	}

	/**
	 * Sorting the array a Column
	 *
	 * @param   array   $arr  Array to sort
	 * @param   string  $col  Sort column
	 * @param   int     $dir  Direction to sort
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
		Factory::getApplication()->close();
	}

	/**
	 * String Starts With
	 *
	 * @param   string  $haystack  String to search.
	 * @param   string  $needle    What to search for.
	 *
	 * @return boolean
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
	 * @return boolean
	 *
	 * @since 1.5
	 */
	public static function endsWith($haystack, $needle)
	{
		// Search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
	}

	/**
	 * Get user ids in an object
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 9.1.4
	 */
	public static function getUsers()
	{
		$options = array();

		$db = Factory::getContainer()->get('DatabaseDriver');
		//$db     = $driver->getDriver();
		$query  = $db->getQuery(true);

		$query->select('id AS value, username AS text');
		$query->from('#__users');
		$query->order('id');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'worning');

			return $options;
		}

		return $options;
	}

	/**
	 * Get haf of array count
	 *
	 * @param   array|object  $array  Array or Object to count
	 *
	 * @return object
	 *
	 * @since 9.1.7
	 */
	public static function halfarray($array)
	{
		$count = count($array);

		$return        = new stdClass;
		$return->half  = floor($count / 2);
		$return->count = $count;

		return $return;
	}

	/**
	 * Method to filter transitions by given id of state
	 *
	 * @param   array  $transitions  Array of transitions
	 * @param   int    $pk           Id of state
	 * @param   int    $workflowId   Id of the workflow
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function filterTransitions(array $transitions, int $pk, int $workflowId = 0): array
	{
		return array_values(
			array_filter(
				$transitions,
				function ($var) use ($pk, $workflowId) {
					return in_array($var['from_stage_id'], [-1, $pk]) && $workflowId == $var['workflow_id'];
				}
			)
		);
	}
}

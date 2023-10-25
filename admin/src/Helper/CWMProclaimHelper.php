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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Registry\Registry;
use stdClass;

/**
 * Proclaim Helper class
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
	public static string $extension = 'com_proclaim';

	/**
	 * Array of Views to namespace names
	 * @var array
	 * @since 10.0.0
	 */
	public static array $viewMap = [
		'cwmadmin'         => 'CWMAdmin',
		'cwmachive'        => 'CWMAchive',
		'cwmbackup'        => 'CWMBackup',
		'cwmcomment'       => 'CWMComment',
		'cwmcomments'      => 'CWMComments',
		'cwmcpanel'        => 'CWMCpanel',
		'cwmdir'           => 'CWMDir',
		'cwminstall'       => 'CWMInstall',
		'cwmlocation'      => 'CWMLocation',
		'cwmlocations'     => 'CWMLocations',
		'cwmmediafile'     => 'CWMMediaFile',
		'cwmmediafiles'    => 'CWMMediaFiles',
		'cwmmessage'       => 'CWMMessage',
		'cwmmessages'      => 'CWMMessages',
		'cwmmessagetype'   => 'CWMMessageType',
		'cwmmessagetypes'  => 'CWMMessageTypes',
		'cwmmigrate'       => 'CWMMigrate',
		'cwmpodcast'       => 'CWMPodcast',
		'cwmpodcasts'      => 'CWMPodcasts',
		'cwmserie'         => 'CWMSerie',
		'cwmseries'        => 'CWMSeries',
		'cwmserver'        => 'CWMServer',
		'cwmservers'       => 'CWMServers',
		'cwmteacher'       => 'CWMTeacher',
		'cwmteachers'      => 'CWMTeachers',
		'cwmtemplatecode'  => 'CWMTemplateCode',
		'cwmtemplatecodes' => 'CWMTemplateCodes',
		'cwmtemplate'      => 'CWMTemplate',
		'cwmtemplates'     => 'CWMTemplates',
		'cwmtopic'         => 'CWMTopic',
		'cwmtopics'        => 'CWMTopics',
		'cwmupload'        => 'CWMUpload',
	];

	/**
	 * Update View and Controller to work with Namespace Case-Sensitive
	 *
	 * @param   string  $defaultController  Default Controller
	 *
	 * @return void
	 * @throws \Exception
	 * @since    10.0.0
	 */
	public static function applyViewAndController(string $defaultController): void
	{
		$input = Factory::getApplication()->input;
		$controller = $input->getCmd('controller', null);
		$view       = $input->getCmd('view', null);
		$task       = $input->getCmd('task', 'display');

		if (str_contains($task, '.'))
		{
			// Explode the controller.task command.
			[$controller, $task] = explode('.', $task);
		}

		if (empty($controller) && empty($view))
		{
			$controller = $defaultController;
			$view       = $defaultController;
		}
		elseif (!empty($controller) && empty($view))
		{
			$view = $controller;
		}

		if ($controller !== null)
		{
			$controller = self::mapView($controller);
		}

		$view       = self::mapView($view);

		$input->set('view', $view);
		$input->set('controller', $controller);
		$input->set('task', $task);
	}

	/**
	 * System to set all urls to lower case
	 *
	 * @param   string  $view  URL View String
	 *
	 * @return string
	 *
	 * @since 10.0.0
	 */
	private static function mapView(string $view): string
	{
		$view = strtolower($view);
		$viewMap = self::$viewMap;

		return $viewMap[$view] ?? $view;
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
	public static function addSubmenu(string $vName): void
	{
		$simple_view = CWMHelper::getSimpleView();

		self::rendermenu(
			Text::_('JBS_CMN_CONTROL_PANEL'), 'index.php?option=com_proclaim&view=cwmcpanel', $vName == 'cpanel'
		);
		self::rendermenu(
			Text::_('JBS_CMN_ADMINISTRATION'), 'index.php?option=com_proclaim&task=cwmadmin.edit&id=1', $vName == 'administrator'
		);
		self::rendermenu(
			Text::_('JBS_CMN_STUDIES'), 'index.php?option=com_proclaim&view=cwmmessages', $vName == 'messages'
		);
		self::rendermenu(
			Text::_('JBS_CMN_MEDIA_FILES'), 'index.php?option=com_proclaim&view=cwmmediafiles', $vName == 'mediafiles'
		);
		self::rendermenu(
			Text::_('JBS_CMN_TEACHERS'), 'index.php?option=com_proclaim&view=cwmteachers', $vName == 'teachers'
		);
		self::rendermenu(
			Text::_('JBS_CMN_SERIES'), 'index.php?option=com_proclaim&view=cwmseries', $vName == 'series'
		);

		if (!$simple_view->mode)
		{
			self::rendermenu(
				Text::_('JBS_CMN_MESSAGETYPES'), 'index.php?option=com_proclaim&view=cwmmessagetypes', $vName == 'messagetypes'
			);
			self::rendermenu(
				Text::_('JBS_CMN_LOCATIONS'), 'index.php?option=com_proclaim&view=cwmlocations', $vName == 'locations'
			);
			self::rendermenu(
				Text::_('JBS_CMN_TOPICS'), 'index.php?option=com_proclaim&view=cwmtopics', $vName == 'topics'
			);
			self::rendermenu(
				Text::_('JBS_CMN_COMMENTS'), 'index.php?option=com_proclaim&view=cwmcomments', $vName == 'comments'
			);
		}

		self::rendermenu(
			Text::_('JBS_CMN_SERVERS'), 'index.php?option=com_proclaim&view=cwmservers', $vName == 'servers'
		);
		self::rendermenu(
			Text::_('JBS_CMN_PODCASTS'), 'index.php?option=com_proclaim&view=cwmpodcasts', $vName == 'podcasts'
		);

		if (!$simple_view->mode)
		{
			self::rendermenu(
				Text::_('JBS_CMN_TEMPLATES'), 'index.php?option=com_proclaim&view=cwmtemplates', $vName == 'templates'
			);
			self::rendermenu(
				Text::_('JBS_CMN_TEMPLATECODE'), 'index.php?option=com_proclaim&view=cwmtemplatecodes', $vName == 'templatecodes'
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
	public static function rendermenu(string $text, string $url, string $vName): void
	{
		// JHtmlSidebar::addEntry($text, $url, $vName);
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 * This may show not to be used but is in the XML files.
	 *
	 * @param   string  $text  The string to filter
	 *
	 * @return string The filtered string
	 *
	 * @throws \Exception
	 * @since 1.5
	 */
	public static function filterText(string $text): string
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
				if ($filterType === 'NONE')
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
						$blackListTags       = array_merge([], ...$tempTags);
						$blackListAttributes = array_merge([], ...$tempAttributes);
					}
					else
					{
						if ($filterType == 'WL')
						{
							$whiteList           = true;
							$whiteListTags       = array_merge([], ...$tempTags);
							$whiteListAttributes = array_merge([], ...$tempAttributes);
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
	 * @since 7.1.0
	 */
	public static function debug(): int
	{
		if (!CWMDbHelper::getInstallState())
		{
			try
			{
				self::$admin_params = CWMParams::getAdmin();
			}
			catch (\Exception $e)
			{
				die;
			}

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
	 * Media Years
	 *
	 * @return array        Returns list of years of media files based on createdate
	 *
	 * @throws \Exception
	 * @since 8.0.0
	 */
	public static function getMediaYears(): array
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');

		// $db      = $driver->getDriver();
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
	public static function getMessageTypes(): array
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');

		// $db      = $driver->getDriver();
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
	public static function getStudyYears(): array
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');

		// $db      = $driver->getDriver();
		$query   = $db->getQuery(true);

		$query->select('DISTINCT YEAR(studydate) as value, YEAR(studydate) as text');
		$query->from('#__bsms_studies');
		$query->order('value DESC');

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
	public static function getTeachers(): array
	{
		$options = array();
		$driver  = Factory::getContainer()->get('DatabaseDriver');
		$db      = $driver->getDriver();
		$query   = $db->getQuery(true);

		$query->select('teacher.id AS value, teacher.teachername AS text');
		$query->from('#__bsms_teachers AS teacher');
		$query->join('INNER', '#__bsms_studies AS study ON study.teacher_id = teacher.id');
		$query->group('teacher.id');
		$query->order('value ASC');

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
	public static function getStudyBooks(): array
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');

		// $db      = $driver->getDriver();
		$query   = $db->getQuery(true);

		$query->select('book.booknumber AS value, book.bookname AS text, book.id');
		$query->from('#__bsms_books AS book');
		$query->join('INNER', '#__bsms_studies AS study ON study.booknumber = book.booknumber');
		$query->group('book.id');
		$query->order('value ASC');

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
	public static function getStudyMediaTypes(): array
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');

		// $db      = $driver->getDriver();
		$query   = $db->getQuery(true);

		$query->select('messageType.id AS value, messageType.message_type AS text');
		$query->from('#__bsms_message_type AS messageType');
		$query->join('INNER', '#__bsms_studies AS study ON study.messagetype = messageType.id');
		$query->group('messageType.id');
		$query->order('text ASC');

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
	public static function getStudyLocations(): array
	{
		$options = array();
		$db  = Factory::getContainer()->get('DatabaseDriver');

		// $db      = $driver->getDriver();
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
	public static function array_sort_by_column(array &$arr, string $col, int $dir = SORT_ASC): void
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
	public static function stop(string $msg = ''): void
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
	public static function startsWith(string $haystack, string $needle): bool
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
	public static function endsWith(string $haystack, string $needle): bool
	{
		// Search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
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
}

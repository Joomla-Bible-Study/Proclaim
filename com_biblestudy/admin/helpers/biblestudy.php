<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

/**
 * BibleStudy Helper class
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
class JBSMBibleStudyHelper
{

	/**
	 * Set extension
	 *
	 * @var string
	 */
	public static $extension = 'com_biblestudy';

	/**
	 * Get Actions
	 *
	 * @param   int    $Itemid  ID
	 * @param   string $type    Type
	 *
	 * @return JObject
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

				case 'folder':
					$assetName = 'com_biblestudy.foldersedit.' . (int) $Itemid;
					break;

				case 'comments':
					$assetName = 'com_biblestudy.commentsedit.' . (int) $Itemid;
					break;

				case 'location':
					$assetName = 'com_biblestudy.locationsedit.' . (int) $Itemid;
					break;

				case 'mediaimage':
					$assetName = 'com_biblestudy.mediaedit.' . (int) $Itemid;
					break;

				case 'messagetype':
					$assetName = 'com_biblestudy.messagetypeedit.' . (int) $Itemid;
					break;

				case 'mimetype':
					$assetName = 'com_biblestudy.mimetypeedit.' . (int) $Itemid;
					break;

				case 'podcast':
					$assetName = 'com_biblestudy.podcastedit.' . (int) $Itemid;
					break;

				case 'serie':
					$assetName = 'com_biblestudy.serie.' . (int) $Itemid;
					break;

				case 'server':
					$assetName = 'com_biblestudy.serversedit.' . (int) $Itemid;
					break;

				case 'share':
					$assetName = 'com_biblestudy.shareedit.' . (int) $Itemid;
					break;

				case 'teacher':
					$assetName = 'com_biblestudy.teacheredit.' . (int) $Itemid;
					break;

				case 'template':
					$assetName = 'com_biblestudy.templateedit.' . (int) $Itemid;
					break;

				case 'topic':
					$assetName = 'com_biblestudy.topicsedit.' . (int) $Itemid;
					break;

				case 'message':
					$assetName = 'com_biblestudy.message.' . (int) $Itemid;
					break;

				case 'mediafile':
					$assetName = 'com_biblestudy.mediafile.' . (int) $Itemid;
					break;

				case 'style':
					$assetName = 'com_biblestudy.style' . (int) $Itemid;
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
	 * @param   string $vName  The name of the active view.
	 *
	 * @return void
	 *
	 * @since    1.6
	 */
	public static function addSubmenu($vName)
	{
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
		self::rendermenu(
			JText::_('JBS_CMN_MESSAGE_TYPES'), 'index.php?option=com_biblestudy&view=messagetypes', $vName == 'messagetypes'
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
		self::rendermenu(
			JText::_('JBS_CMN_SERVERS'), 'index.php?option=com_biblestudy&view=servers', $vName == 'servers'
		);
		self::rendermenu(
			JText::_('JBS_CMN_FOLDERS'), 'index.php?option=com_biblestudy&view=folders', $vName == 'folders'
		);
		self::rendermenu(
			JText::_('JBS_CMN_PODCASTS'), 'index.php?option=com_biblestudy&view=podcasts', $vName == 'podcasts'
		);
		self::rendermenu(
			JText::_('JBS_CMN_SOCIAL_NETWORKING_LINKS'), 'index.php?option=com_biblestudy&view=shares', $vName == 'shares'
		);
		self::rendermenu(
			JText::_('JBS_CMN_TEMPLATES'), 'index.php?option=com_biblestudy&view=templates', $vName == 'templates'
		);
		self::rendermenu(
			JText::_('JBS_CMN_TEMPLATECODE'), 'index.php?option=com_biblestudy&view=templatecodes', $vName == 'templatecodes'
		);
		self::rendermenu(
			JText::_('JBS_CMN_MEDIAIMAGES'), 'index.php?option=com_biblestudy&view=mediaimages', $vName == 'mediaimages'
		);
		self::rendermenu(
			JText::_('JBS_CMN_MIME_TYPES'), 'index.php?option=com_biblestudy&view=mimetypes', $vName == 'mimetypes'
		);
		self::rendermenu(
			JText::_('JBS_CMN_STYLES'), 'index.php?option=com_biblestudy&view=styles', $vName == 'styles'
		);
	}

	/**
	 *  Rendering Menu based on Joomla! Version.
	 *
	 * @param   string $text   ?
	 * @param   string $url    ?
	 * @param   string $vName  ?
	 *
	 * @return void
	 */
	public static function rendermenu($text, $url, $vName)
	{
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtmlSidebar::addEntry($text, $url, $vName);
		}
		else
		{
			JSubMenuHelper::addEntry($text, $url, $vName);
		}
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   string $text  The string to filter
	 *
	 * @return string The filtered string
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

		$noHtml     = false;
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
				$noHtml = true;
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
					// Preprocess the tags and attributes.
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
					// Each list is cummulative.
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

		} // Black lists take second precedence.
		else
		{
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

			} // White lists take third precedence.
			else
			{
				if ($whiteList)
				{
					// Turn off xss auto clean
					$filter = JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);

				} // No HTML takes last place.
				else
				{
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
		include_once BIBLESTUDY_PATH_ADMIN_HELPERS . '/params.php';
		$admin_params = JBSMParams::getAdmin();

		if (!isset($admin_params->debuge))
		{
			$admin_params        = new stdClass;
			$admin_params->debug = 1;
		}

		return $admin_params->debug;
	}

	/**
	 * Media Types
	 *
	 * @return array        Returns lists of media types
	 *
	 * @since 8.0.0
	 */
	public static function getMediaTypes()
	{
		$options = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id As value, media_image_name As text');
		$query->from('#__bsms_media AS a');
		$query->order('a.media_image_name');

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
}


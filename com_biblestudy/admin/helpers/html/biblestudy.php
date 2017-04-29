<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
defined('JPATH_BASE') or die;

/**
 * Biblestudy HTML class.
 *
 * @package  BibleStudy.Admin
 * @since    7.0.0
 */
abstract class JHtmlBiblestudy
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  9.0.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the bPopup JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @param   mixed  $debug  Is debugging mode on? [optional]
	 * @param   mixed  $extra  Option to load extra js [optional]
	 *
	 * @return  void
	 *
	 * @since   9.0.0
	 */
	public static function framework($debug = null, $extra = null)
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		JHtml::_('bootstrap.framework', $debug);
		$app = JFactory::getApplication();
		$menu = $app->getMenu();

		if ($menu->getActive() !== null)
		{
			JHtml::_('bootstrap.loadCss');
		}

		JHtml::script('media/com_biblestudy/js/biblestudy.js');
		JHtml::script('media/com_biblestudy/js/modernizr.js');

		self::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Loads CSS files needed by Bootstrap
	 *
	 * @param   Joomla\Registry\Registry  $params  Params for css
	 * @param   string                    $url     Url of a css file to load
	 * @param   string                    $extra   Url of a css file to load
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadCss($params, $url = null, $extra = null)
	{
		JHtml::stylesheet('media/com_biblestudy/css/general.css');

		// Import Stylesheets
		if ($params)
		{
			$css = $params->get('css');

			if ($css <= "-1")
			{
				JHtml::stylesheet('media/com_biblestudy/css/biblestudy.css');
			}
			else
			{
				JHtml::stylesheet('media/com_biblestudy/css/site/' . $css);
			}
		}

		if ($url)
		{
			JHtml::stylesheet($url);
		}

		if ($extra == 'font-awesome')
		{
			JHtml::script('https://use.fontawesome.com/ac3aa5180f.js');
		}
	}

	/**
	 * Display a batch widget for the player selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function players()
	{
		// Create the batch selector to change the player on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . JText::_('JBS_MED_PLAYER')
			. '::' . JText::_('JBS_MED_PLAYER_DESC') . '">',
			JText::_('JBS_MED_PLAYER'), '</label>', '<select name="batch[player]" class="inputbox" id="batch-player">',
			'<option value="">' . JText::_('JBS_BAT_PLAYER_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::playerlist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    object    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function playerlist()
	{
		$options   = array();
		$options[] = array('value' => '', 'text' => JText::_('JBS_CMN_USE_GLOBAL'));
		$options[] = array('value' => 0, 'text' => JText::_('JBS_CMN_DIRECT_LINK'));
		$options[] = array('value' => 1, 'text' => JText::_('JBS_CMN_USE_INTERNAL_PLAYER'));
		$options[] = array('value' => 3, 'text' => JText::_('JBS_CMN_USE_AV'));
		$options[] = array('value' => 7, 'text' => JText::_('JBS_CMN_USE_MP3_PLAYER'));
		$options[] = array('value' => 8, 'text' => JText::_('JBS_CMN_USE_EMBED_CODE'));
		$object    = new stdClass;

		foreach ($options as $key => $value)
		{
			$object->$key = $value;
		}

		return $object;
	}

	/**
	 * Display a batch widget for the player selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function link_type()
	{
		// Create the batch selector to change the player on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . JText::_('JBS_MED_SHOW_DOWNLOAD_ICON')
			. '::' . JText::_('JBS_MED_SHOW_DOWNLOAD_ICON_DESC') . '">',
			JText::_('JBS_MED_SHOW_DOWNLOAD_ICON'), '</label>',
			'<select name="batch[link_type]" class="inputbox" id="batch-link_type">',
			'<option value="">' . JText::_('JBS_BAT_DOWNLOAD_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::Link_typelist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return   Object    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function Link_typelist()
	{
		$options = array();

		$options[] = array('value' => 0, 'text' => JText::_('JBS_MED_NO_DOWNLOAD_ICON'));
		$options[] = array('value' => 1, 'text' => JText::_('JBS_MED_SHOW_DOWNLOAD_ICON'));
		$options[] = array('value' => 2, 'text' => JText::_('JBS_MED_SHOW_ONLY_DOWNLOAD_ICON'));

		$object = new stdClass;

		foreach ($options as $key => $value)
		{
			$object->$key = $value;
		}

		return $object;
	}

	/**
	 * Display a batch widget for the popup selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function popup()
	{
		// Create the batch selector to change the popup on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . JText::_('JBS_MED_INTERNAL_POPUP')
			. '::' . JText::_('JBS_MED_INTERNAL_POPUP_DESC') . '">',
			JText::_('JBS_MED_POPUP'), '</label>', '<select name="batch[popup]" class="inputbox" id="batch-popup">',
			'<option value="">' . JText::_('JBS_BAT_POPUP_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::popuplist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    object    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function popuplist()
	{
		$options   = array();
		$options[] = array('value' => 3, 'text' => JText::_('JBS_CMN_USE_GLOBAL'));
		$options[] = array('value' => 2, 'text' => JText::_('JBS_CMN_INLINE'));
		$options[] = array('value' => 1, 'text' => JText::_('JBS_CMN_POPUP'));

		$object = new stdClass;

		foreach ($options as $key => $value)
		{
			$object->$key = $value;
		}

		return $object;
	}

	/**
	 * Display a batch widget for the player selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function mediatype()
	{
		// Create the batch selector to change the mediatype on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . JText::_('JBS_CMN_IMAGE')
			. '::' . JText::_('JBS_MED_IMAGE_DESC') . '">',
			JText::_('JBS_MED_SELECT_MEDIA_TYPE'), '</label>',
			'<select name="batch[mediatype]" class="inputbox" id="batch-mediatype">',
			'<option value="">' . JText::_('JBS_BAT_MEDIATYPE_NOCHANGE') . '</option>',
			'</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function Mediatypelist()
	{
		$options = null;
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('id As value, media_text As text');
		$query->from('#__bsms_media AS a');
		$query->order('a.media_text ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		return $options;
	}

	/**
	 * Display a batch widget for the teacher selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function Teacher()
	{
		// Create the batch selector to change the teacher on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
			JText::_('JBS_CMN_TEACHER') . '::' . JText::_('JBS_BAT_TEACHER_DESC') . '">',
			JText::_('JBS_CMN_TEACHER'), '</label>',
			'<select name="batch[teacher]" class="inputbox" id="batch-teacher">',
			'<option value="">' . JText::_('JBS_BAT_TEACHER_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::Teacherlist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function Teacherlist()
	{
		$options = null;
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('id As value, teachername As text');
		$query->from('#__bsms_teachers AS a');
		$query->order('a.teachername ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		return $options;
	}

	/**
	 * Display a batch widget for the teacher selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function Messagetype()
	{
		// Create the batch selector to change the message type on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
			JText::_('JBS_CMN_MESSAGETYPE') . '::' . JText::_('JBS_BAT_MESSAGETYPE_DESC') . '">',
			JText::_('JBS_CMN_MESSAGETYPE'), '</label>',
			'<select name="batch[messagetype]" class="inputbox" id="batch-messagetype">',
			'<option value="">' . JText::_('JBS_BAT_MESSAGETYPE_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::Messagetypelist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function Messagetypelist()
	{
		$options = null;
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('id As value, message_type As text');
		$query->from('#__bsms_message_type AS a');
		$query->order('a.message_type ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		return $options;
	}

	/**
	 * Display a batch widget for the teacher selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function Series()
	{
		// Create the batch selector to change the series on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
			JText::_('JBS_CMN_SERIES') . '::' . JText::_('JBS_BAT_SERIES_DESC') . '">',
			JText::_('JBS_CMN_SERIES'), '</label>',
			'<select name="batch[series]" class="inputbox" id="batch-series">',
			'<option value="">' . JText::_('JBS_BAT_SERIES_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::Serieslist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function Serieslist()
	{
		$options = null;
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('id As value, series_text As text');
		$query->from('#__bsms_series AS a');
		$query->order('a.series_text ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		return $options;
	}
}

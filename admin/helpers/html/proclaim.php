<?php
/**
 * Part of Proclaim Package
 *
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 * */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('JPATH_BASE') or die;


/**
 * Biblestudy HTML class.
 *
 * @package  Proclaim.Admin
 * @since    7.0.0
 */
abstract class JHtmlProclaim
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  9.0.0
	 */
	protected static array $loaded = array();

	/**
	 * Method to load the bPopup JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @param   mixed        $debug  Is debugging mode on? [optional]
	 * @param   string|null  $extra  Option to load extra js [optional]
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 * @since   9.0.0
	 */
	public static function framework($debug = null, string $extra = null): void
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = Factory::getApplication()->getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		HtmlHelper::_('bootstrap.framework', $debug);
		$app  = Factory::getApplication();
		$menu = $app->getMenu();

		if ($menu->getActive() !== null)
		{
			HtmlHelper::_('bootstrap.loadCss');
		}

		HtmlHelper::script('media/com_proclaim/js/biblestudy.min.js');
		HtmlHelper::script('media/com_proclaim/js/modernizr.min.js');

		// @todo may need ot look at including this or offing CDN???
		//HTMLHelper::script('https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js');

		self::$loaded[__METHOD__] = true;
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
	public static function loadCss($params, $url = null, $extra = null): void
	{
		HtmlHelper::stylesheet('media/com_proclaim/css/general.min.css');

		// Import Stylesheets
		if ($params)
		{
			$css = $params->get('css');

			if ($css <= "-1")
			{
				HtmlHelper::stylesheet('media/com_proclaim/css/proclaim.min.css');
			}
			else
			{
				HtmlHelper::stylesheet('media/com_proclaim/css/site/' . $css);
			}
		}

		if ($url)
		{
			HtmlHelper::stylesheet($url);
		}

		if ($extra === 'font-awesome')
		{
			// @todo move to local option
			HTMLHelper::script('https://use.fontawesome.com/releases/v5.12.0/js/all.js',
				[],
				['defer' => 'defer']
			);
			HTMLHelper::script('https://use.fontawesome.com/releases/v5.12.0/js/v4-shims.js',
				[],
				['defer' => 'defer']
			);
		}

		if ($extra === 'podcast')
		{
			HTMLHelper::stylesheet('media/com_proclaim/css/podcast.min.css');
		}

		if ($extra === 'modernizr')
		{
			HTMLHelper::script('media/com_proclaim/js/modernizr.min.js',
				[],
				['defer' => 'defer']
			);
		}
	}

	/**
	 * Display a batch widget for the player selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function players(): string
	{
		// Create the batch selector to change the player on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . Text::_('JBS_MED_PLAYER')
			. '::' . Text::_('JBS_MED_PLAYER_DESC') . '">',
			Text::_('JBS_MED_PLAYER'), '</label>', '<select name="batch[player]" class="inputbox" id="batch-player">',
			'<option value="">' . Text::_('JBS_BAT_PLAYER_NOCHANGE') . '</option>',
			HTMLHelper::_('select.options', self::playerlist(), 'value', 'text'), '</select>'
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
	public static function playerList()
	{
		$options   = array();
		$options[] = array('value' => '', 'text' => Text::_('JBS_CMN_USE_GLOBAL'));
		$options[] = array('value' => 0, 'text' => Text::_('JBS_CMN_DIRECT_LINK'));
		$options[] = array('value' => 1, 'text' => Text::_('JBS_CMN_USE_INTERNAL_PLAYER'));
		$options[] = array('value' => 3, 'text' => Text::_('JBS_CMN_USE_AV'));
		$options[] = array('value' => 7, 'text' => Text::_('JBS_CMN_USE_MP3_PLAYER'));
		$options[] = array('value' => 8, 'text' => Text::_('JBS_CMN_USE_EMBED_CODE'));
		$object    = new \stdClass;

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
	public static function link_type(): string
	{
		// Create the batch selector to change the player on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . Text::_('JBS_MED_SHOW_DOWNLOAD_ICON')
			. '::' . Text::_('JBS_MED_SHOW_DOWNLOAD_ICON_DESC') . '">',
			Text::_('JBS_MED_SHOW_DOWNLOAD_ICON'), '</label>',
			'<select name="batch[link_type]" class="inputbox" id="batch-link_type">',
			'<option value="">' . Text::_('JBS_BAT_DOWNLOAD_NOCHANGE') . '</option>',
			HTMLHelper::_('select.options', self::Link_typelist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return   object    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function Link_TypeList()
	{
		$options = array();

		$options[] = array('value' => 0, 'text' => Text::_('JBS_MED_NO_DOWNLOAD_ICON'));
		$options[] = array('value' => 1, 'text' => Text::_('JBS_MED_SHOW_DOWNLOAD_ICON'));
		$options[] = array('value' => 2, 'text' => Text::_('JBS_MED_SHOW_ONLY_DOWNLOAD_ICON'));

		$object = new \stdClass;

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
	public static function popup(): string
	{
		// Create the batch selector to change the popup on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . Text::_('JBS_MED_INTERNAL_POPUP')
			. '::' . Text::_('JBS_MED_INTERNAL_POPUP_DESC') . '">',
			Text::_('JBS_MED_POPUP'), '</label>', '<select name="batch[popup]" class="inputbox" id="batch-popup">',
			'<option value="">' . Text::_('JBS_BAT_POPUP_NOCHANGE') . '</option>',
			HTMLHelper::_('select.options', self::popuplist(), 'value', 'text'), '</select>'
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
	public static function popupList()
	{
		$options   = array();
		$options[] = array('value' => 3, 'text' => Text::_('JBS_CMN_USE_GLOBAL'));
		$options[] = array('value' => 2, 'text' => Text::_('JBS_CMN_INLINE'));
		$options[] = array('value' => 1, 'text' => Text::_('JBS_CMN_POPUP'));

		$object = new \stdClass;

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
	public static function mediaType()
	{
		// Create the batch selector to change the mediatype on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . Text::_('JBS_CMN_IMAGE')
			. '::' . Text::_('JBS_MED_IMAGE_DESC') . '">',
			Text::_('JBS_MED_SELECT_MEDIA_TYPE'), '</label>',
			'<select name="batch[mediatype]" class="inputbox" id="batch-mediatype">',
			'<option value="">' . Text::_('JBS_BAT_MEDIATYPE_NOCHANGE') . '</option>',
			'</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @throws   Exception
	 * @since    1.6
	 */
	public static function MediaTypeList(): ?array
	{
		$options = null;
		$db      = Factory::getContainer()->get('DatabaseDriver');
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
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		return $options;
	}

	/**
	 * Display a batch widget for the teacher selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @throws  Exception
	 * @since   2.5
	 */
	public static function Teacher(): string
	{
		// Create the batch selector to change the teacher on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
			Text::_('JBS_CMN_TEACHER') . '::' . Text::_('JBS_BAT_TEACHER_DESC') . '">',
			Text::_('JBS_CMN_TEACHER'), '</label>',
			'<select name="batch[teacher]" class="inputbox" id="batch-teacher">',
			'<option value="">' . Text::_('JBS_BAT_TEACHER_NOCHANGE') . '</option>',
			HTMLHelper::_('select.options', self::Teacherlist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @throws   Exception
	 * @since    1.6
	 */
	public static function TeacherList(): ?array
	{
		$options = null;
		$db      = Factory::getContainer()->get('DatabaseDriver');
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
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		return $options;
	}

	/**
	 * Display a batch widget for the teacher selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @throws  Exception
	 * @since   2.5
	 */
	public static function MessageType(): string
	{
		// Create the batch selector to change the message type on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
			Text::_('JBS_CMN_MESSAGETYPE') . '::' . Text::_('JBS_BAT_MESSAGETYPE_DESC') . '">',
			Text::_('JBS_CMN_MESSAGETYPE'), '</label>',
			'<select name="batch[messagetype]" class="inputbox" id="batch-messagetype">',
			'<option value="">' . Text::_('JBS_BAT_MESSAGETYPE_NOCHANGE') . '</option>',
			HTMLHelper::_('select.options', self::Messagetypelist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @throws   Exception
	 * @since    1.6
	 */
	public static function MessageTypeList(): ?array
	{
		$options = null;
		$db      = Factory::getContainer()->get('DatabaseDriver');
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
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		return $options;
	}

	/**
	 * Display a batch widget for the teacher selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @throws  Exception
	 * @since   2.5
	 */
	public static function Series(): string
	{
		// Create the batch selector to change the series on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' .
			Text::_('JBS_CMN_SERIES') . '::' . Text::_('JBS_BAT_SERIES_DESC') . '">',
			Text::_('JBS_CMN_SERIES'), '</label>',
			'<select name="batch[series]" class="inputbox" id="batch-series">',
			'<option value="">' . Text::_('JBS_BAT_SERIES_NOCHANGE') . '</option>',
			HTMLHelper::_('select.options', self::Serieslist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @throws   Exception
	 * @since    1.6
	 */
	public static function SeriesList(): ?array
	{
		$options = null;
		$db      = Factory::getContainer()->get('DatabaseDriver');
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
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		return $options;
	}
}

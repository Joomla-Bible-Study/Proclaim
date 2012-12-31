<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
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
	 * Method to get the field options.
	 *
	 * @return   array    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function Link_typelist()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

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
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function Mimetypelist()
	{
		$options = null;
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);

		$query->select('id As value, mimetype As text');
		$query->from('#__bsmsmime_type AS a');
		$query->order('a.mimetype ASC');

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
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
	 *
	 * @since    1.6
	 */
	public static function playerlist()
	{
		$options   = array();
		$options[] = array('value' => 100, 'text' => JText::_('JBS_CMN_USE_GLOBAL'));
		$options[] = array('value' => 0, 'text' => JText::_('JBS_CMN_DIRECT_LINK'));
		$options[] = array('value' => 1, 'text' => JText::_('JBS_CMN_USE_INTERNAL_PLAYER'));
		$options[] = array('value' => 3, 'text' => JText::_('JBS_CMN_USE_AV'));
		$options[] = array('value' => 7, 'text' => JText::_('JBS_CMN_USE_LEGACY_PLAYER'));
		$options[] = array('value' => 8, 'text' => JText::_('JBS_CMN_USE_EMBED_CODE'));
		$object    = new stdClass;

		foreach ($options as $key => $value)
		{
			$object->$key = $value;
		}

		return $object;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return    array    The field option objects.
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
	public static function players()
	{
		// Create the batch selector to change the player on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . JText::_('JBS_MED_PLAYER')
				. '::' . JText::_('JBS_MED_PLAYER_DESC') . '">',
			JText::_('JBS_MED_PLAYER'), '</label>', '<select name="batch[player]" class="inputbox" id="batch-player">',
			'<option value="">' . JText::_('JBS_CMN_PLAYER_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::playerlist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
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
			'<option value="">' . JText::_('JBS_CMN_DOWNLOAD_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::Link_typelist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
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
			'<option value="">' . JText::_('JBS_CMN_POPUP_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::popuplist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
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
		// Create the batch selector to change the player on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . JText::_('JBS_MED_IMAGE')
				. '::' . JText::_('JBS_MED_IMAGE_DESC') . '">',
			JText::_('JBS_MED_SELECT_MEDIA_TYPE'), '</label>',
			'<select name="batch[mediatype]" class="inputbox" id="batch-mediatype">',
			'<option value="">' . JText::_('JBS_CMN_MEDIATYPE_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::Mediatypelist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}

	/**
	 * Display a batch widget for the player selector.
	 *
	 * @return  string  The necessary HTML for the widget.
	 *
	 * @since   2.5
	 */
	public static function mimetype()
	{
		// Create the batch selector to change the player on a selection list.
		$lines = array(
			'<label id="batch-client-lbl" for="batch-client" class="hasTip" title="' . JText::_('JBS_MIMETYPE') . '::' . JText::_('JBS_MIMETYPE_DESC') . '">',
			JText::_('JBS_MIMETYPE'), '</label>',
			'<select name="batch[mimetype]" class="inputbox" id="batch-mimetype">',
			'<option value="">' . JText::_('JBS_CMN__MIMETYPE_NOCHANGE') . '</option>',
			JHtml::_('select.options', self::Mimetypelist(), 'value', 'text'), '</select>'
		);

		return implode("\n", $lines);
	}
}

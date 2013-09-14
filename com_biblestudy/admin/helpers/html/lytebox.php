<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for bPopup JavaScript behaviors
 *
 * @package  BibleStudy.Admin
 * @since    8.1.0
 */
abstract class JHtmlbsLytebox
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  8.1.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the bPopup JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @param   mixed  $debug  Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   8.1.0
	 */
	public static function framework($debug = null)
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
		JHtml::_('script', 'media/com_biblestudy/lytebox/lytebox.js', false, true, false, false, $debug);
		self::loadCss();

		self::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Loads CSS files needed by Bootstrap
	 *
	 * @param   array  $attribs  Optional array of attributes to be passed to JHtml::_('stylesheet')
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadCss($attribs = array())
	{
		JHtml::_('stylesheet', 'media/com_biblestudy/lytebox/lytebox.css', $attribs, true);
	}
}

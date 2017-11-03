<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2017 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */
defined('JPATH_PLATFORM') or die;

/**
 * Utility class for bPopup JavaScript behaviors
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
abstract class JHtmlbsMedia
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
	 * @param   boolean  $noConflict  True to load jQuery in noConflict mode [optional]
	 * @param   mixed    $debug       Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   9.0.0
	 */
	public static function framework($noConflict = true, $debug = null)
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		// Load jQuery
		JHtml::_('jquery.framework');

		self::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Add javascript support for Lytebox
	 *
	 * @param   string  $selector  Common class for the Lytebox.
	 * @param   array   $params    An array of options for the modal.
	 *                             Options for the modal can be:
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function lytebox($selector = 'lytebox', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Attach the carousel to document
			JHtml::_('script', 'media/com_biblestudy/lytebox/lytebox.js', false, true, false, false);

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
		}

		return;
	}

	/**
	 * Loads CSS files needed by Bootstrap
	 *
	 * @param   boolean  $includeMainCss  If true, main bootstrap.css files are loaded
	 * @param   string   $cssName         Name of css to use for view.
	 * @param   string   $cssSet          what css to load
	 * @param   array    $attribs         Optional array of attributes to be passed to JHtml::_('stylesheet')
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadCss($includeMainCss = true, $cssName = null, $cssSet = 'default', $attribs = array())
	{
		// Load Bootstrap main CSS
		if ($includeMainCss)
		{
			if ($cssName != null && $cssName <= "-1")
			{
				JHtml::_('stylesheet', 'media/com_biblestudy/css/biblestudy.css', $attribs, true);
			}
			else
			{
				JHtml::_('stylesheet', 'media/com_biblestudy/css/site/' . $cssName, $attribs, true);
			}

			JHtml::_('stylesheet', 'media/com_biblestudy/css/general.css', $attribs, true);
		}

		if ($cssSet == 'lytebox')
		{
			JHtml::_('stylesheet', 'media/com_biblestudy/lytebox/lytebox.css', $attribs, true);
		}
	}
}

<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for bPopup JavaScript behaviors
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 * @deprecated 10.0.0
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
	public static function framework(bool $noConflict = true, $debug = null): void
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		// Load jQuery
		HTMLHelper::_('jquery.framework');

		self::$loaded[__METHOD__] = true;
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
	public static function lytebox(string $selector = 'lytebox', array $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (!isset(self::$loaded[__METHOD__][$sig]))
		{
			// Attach the carousel to document
			HTMLHelper::_('script', 'media/com_proclaim/lytebox/lytebox.min.js', false, true, false, false);

			// Set static array
			self::$loaded[__METHOD__][$sig] = true;
		}

		return;
	}

	/**
	 * Loads CSS files needed by Bootstrap
	 *
	 * @param   boolean      $includeMainCss  If true, main bootstrap.css files are loaded
	 * @param   string|null  $cssName         Name of css to use for view.
	 * @param   string       $cssSet          what css to load
	 * @param   array        $attribs         Optional array of attributes to be passed to HTMLHelper::_('stylesheet')
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadCss(bool $includeMainCss = true, string $cssName = null, string $cssSet = 'default', array $attribs = array()): void
	{
		// Load Bootstrap main CSS
		if ($includeMainCss)
		{
			if ($cssName != null && $cssName <= "-1")
			{
				HTMLHelper::_('stylesheet', 'media/com_proclaim/css/proclaim.min.css', $attribs, true);
			}
			else
			{
				HTMLHelper::_('stylesheet', 'media/com_proclaim/css/site/' . $cssName, $attribs, true);
			}

			HTMLHelper::_('stylesheet', 'media/com_proclaim/css/general.min.css', $attribs, true);
		}

		if ($cssSet === 'lytebox')
		{
			HTMLHelper::_('stylesheet', 'media/com_proclaim/lytebox/lytebox.min.css', $attribs, true);
		}
	}
}

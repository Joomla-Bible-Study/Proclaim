<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2019 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for Fancybox JavaScript behaviors
 *
 * @package  Proclaim.Admin
 * @since    9.0.0
 */
abstract class Fancybox
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  9.0.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the fancybox JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @param   boolean  $option     Optional looks [optional]
	 * @param   boolean  $mouseweel  To add mouse Well to display [optional]
	 *
	 * @return  void
	 *
	 * @since   9.0.0
	 */
	public static function framework($option = false, $mouseweel = false)
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		JHtml::_('jquery.framework');
		JHtml::_('jwplayer.framework');
		JHtml::script('https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js');
		JHtml::script('media/com_proclaim/js/fancybox.js');

		if ($mouseweel)
		{
			JHtml::script('media/com_proclaim/js/jquery.mousewheel.pack.js');
		}

		self::loadCss($option);

		self::$loaded[__METHOD__] = true;
	}

	/**
	 * Loads CSS files needed by Bootstrap
	 *
	 * @param   boolean  $option  Optional add helpers - button, thumbnail and/or media
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function loadCss($option = false)
	{
		JHtml::stylesheet('https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css');
		JHtml::stylesheet('media/com_proclaim/css/bsms.fancybox.css');
	}
}

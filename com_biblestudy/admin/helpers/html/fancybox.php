<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for Fancybox JavaScript behaviors
 *
 * @package  BibleStudy.Admin
 * @since    9.0.0
 */
abstract class JHtmlFancybox
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
	public static function framework ($option = false, $mouseweel = false)
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		JHtml::_('jquery.framework');
		JHtml::_('jwplayer.framework');
		JHtml::script('media/com_biblestudy/fancybox/jquery.fancybox.pack.js');
		JHtml::script('media/com_biblestudy/js/fancybox.js');

		if ($mouseweel)
		{
			JHtml::script('media/com_biblestudy/js/jquery.mousewheel-3.0.6.pack.js');

		}

		if ($option)
		{
			JHtml::script('media/com_biblestudy/fancybox/helpers/jquery.fancybox-buttons.js');
			JHtml::script('media/com_biblestudy/fancybox/helpers/jquery.fancybox-media.js');
			JHtml::script('media/com_biblestudy/fancybox/helpers/jquery.fancybox-thumbs.js');
		}
		self::loadCss($option);

		self::$loaded[__METHOD__] = true;

		return;
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
	public static function loadCss ($option = false)
	{
		JHtml::stylesheet('media/com_biblestudy/fancybox/jquery.fancybox.css');
		JHtml::stylesheet('media/com_biblestudy/css/bsms.fancybox.css');

		if ($option)
		{
			JHtml::stylesheet('media/com_biblestudy/fancybox/helpers/jquery.fancybox-buttons.css');
			JHtml::stylesheet('media/com_biblestudy/fancybox/helpers/jquery.fancybox-thumbs.css');
		}
	}
}

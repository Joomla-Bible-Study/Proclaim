<?php
/**
 * Part of Joomla Package
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for jQuery JavaScript behaviors
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.0
 */
abstract class JHtmlJwplayer
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the jQuery JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function framework()
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		$doc = JFactory::getDocument();
		$params = JBSMParams::getAdmin()->params;
		$key = $params->get('jwplayer_key');
		$cdn = $params->get('jwplayer_cdn');
		if ($cdn)
		{
			JHtml::script($cdn);
		}
		elseif ($key)
		{
			JHtml::script('media/com_biblestudy/player/jwplayer.js');
			$doc->addScriptDeclaration('jwplayer.key="' . $key . '";');
		}
		else
		{
			JFactory::getApplication()->enqueueMessage('No key for Selft Hosting');
		}

		self::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Render JS for media
	 *
	 * @param   object  $media   Media info
	 * @param   int     $id      ID of media
	 * @param   object  $params  Params from media have to be in object for do to protection.
	 * @param   bool    $popup   If from a popup
	 *
	 * @return  string
	 */
	public static function render($media, $id, $params, $popup = false)
	{
		if (!isset($params->popupimage))
		{
			$params->popupimage = "media/com_biblestudy/images/speaker24.png";
		}
		if (!isset($params->playerposition))
		{
			$params->playerposition = "";
		}
		$render = "";
		if ($popup)
		{
			$render .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:" . $media->playerwidth . "px;\">";
		}
		$render .= " <div id='placeholder" . $id . "'>
						<a href='http://www.adobe.com/go/getflashplayer'><?php echo JText::_('Get flash') ?></a> <?php echo JText::_('to see this player') ?>
					</div>";
		if ($popup)
		{
			$render .= "</div>";
		}
		$render .= "<script language=\"javascript\" type=\"text/javascript\">
						jwplayer('placeholder" . $id . "').setup({
							'file': '" . $media->path1 . "',
							'height': '" . $media->playerheight . "',
							'width': '" . $media->playerwidth . "',
							'image': '" . $params->popupimage . "',
							'autostart': '" . $media->autostart . "',
							'backcolor': '" . $media->backcolor . "',
							'frontcolor': '" . $media->frontcolor . "',
							'lightcolor': '" . $media->lightcolor . "',
							'screencolor': '" . $media->screencolor . "',
							'controlbar.position': '" . $params->playerposition . "',
							'controlbar.idlehide': '" . $media->playeridlehide . "'
						});
					</script>";

		return $render;

	}
}

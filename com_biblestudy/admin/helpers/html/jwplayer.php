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

use Joomla\Registry\Registry;

/**
 * Utility class for JW Player behaviors
 *
 * @package     BibleStudy.Admin
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
		/** @var Joomla\Registry\Registry $params */
		$params = JBSMParams::getAdmin()->params;
		$key = $params->get('jwplayer_key', '8eJ+ik6aOUabfOisJzomcM2Z3h1VZ9+6cufBXQ==');
		$cdn = $params->get('jwplayer_cdn', 'https://content.jwplatform.com/libraries/HPyI6990.js');
		if ($cdn)
		{
			JHtml::script($cdn);
		}
		else
		{
			JHtml::script('media/com_biblestudy/player/jwplayer.js');
			$doc->addScriptDeclaration('jwplayer.key="' . $key . '";');
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
	 * @param   bool    $player  To make player for audio like (MP3, M4A, etc..)
	 *
	 * @return  string
	 */
	public static function render($media, $id, $params, $popup = false, $player = false)
	{
		if (!isset($params->popupimage))
		{
			$params->popupimage = "images/biblestudy/speaker24.png";
		}
		if (!isset($params->playerposition))
		{
			$params->playerposition = "";
		}
		if (!isset($params->playerresponsive))
		{
			$params->playerresponsive = 0;
		}

		// Used to set for MP3 and audio player look
		if ($player == true)
		{
			$media->playerheight = 30;
		}

		// Check to see if file name is for youtube and helps with old converted file names.
		if (strpos($media->path1, 'youtube.com') !== false)
		{
			$media->path1 = 'https://' . strstr($media->path1, 'youtube.com');
		}
		elseif (strpos($media->path1, 'youtu.be') !== false)
		{
			$media->path1 = 'https://' . strstr($media->path1, 'youtu.be');
		}
		$render = "";
		if ($popup)
		{
			if ($params->playerresponsive != 0)
			{
				$media->playerwidth = '100%';
				$render .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:100%;\">";
			}
			else
			{
				$render .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:" . $media->playerwidth . "px;\">";
			}
		}
		$render .= " <div id='placeholder" . $id . "'></div>";
		if ($popup)
		{
			$render .= "</div>";
		}
		$render .= "<script language=\"javascript\" type=\"text/javascript\">
						jwplayer('placeholder" . $id . "').setup({
							'file': '" . $media->path1 . "',
							";
		if ($params->playerresponsive == 0)
		{
			$render .= "'height': '" . $media->playerheight . "',
			";
		}
		else
		{
			$render .= "'aspectratio': '16:9',
			";
		}

			$render .= "'width': '" . $media->playerwidth . "',
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

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
 * Utility class for JWplayer behaviors
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
	 * Method to load the JWplayer JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of JWplayer is included for easier debugging.
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
		$key    = $params->get('jwplayer_key', '8eJ+ik6aOUabfOisJzomcM2Z3h1VZ9+6cufBXQ==');
		$cdn    = $params->get('jwplayer_cdn', '');
		if ($cdn)
		{
			$doc->addScriptDeclaration('jwplayer.key="' . $key . '";');
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
	 * @param   object    $media   Media info
	 * @param   int       $id      ID of media
	 * @param   Registry  $params  Params from media have to be in object for do to protection.
	 * @param   bool      $popup   If from a popup
	 * @param   bool      $player  To make player for audio like (MP3, M4A, etc..)
	 *
	 * @return  string
	 */
	public static function render($media, $id, $params, $popup = false, $player = false)
	{
		// Used to set for MP3 and audio player look
		if ($player == true)
		{
			$media->playerheight = 30;
		}
		else
		{
			$media->playerheight = $params->get('player_hight');
		}

		// Check to see if file name is for youtube and helps with old converted file names.
		if (!isset($media->path1))
		{
			$media->path1 = $media->sparams->get('path') . $params->get('filename');

			if (!substr_count($media->path1, '://') && !substr_count($media->path1, '//'))
			{
				$protocol     = $params->get('protocol', 'http://');
				$media->path1 = $protocol . $media->path1;
			}
		}
		elseif (strpos($media->path1, 'youtube.com') !== false)
		{
			$media->path1 = 'https://' . strstr($media->path1, 'youtube.com');
		}
		elseif (strpos($media->path1, 'youtu.be') !== false)
		{
			$media->path1 = 'https://' . strstr($media->path1, 'youtu.be');
		}

		// Fall back check to see if JWplayer can play the media. if not will try and return a link to the file.
		$acceptedFormats = array('aac', 'm4a', 'f4a', 'mp3', 'ogg', 'oga', 'mp4', 'm4v', 'f4v', 'mov', 'flv', 'webm', 'm3u8', 'mpd', 'DVR');
		if (!in_array(pathinfo($media->path1, PATHINFO_EXTENSION), $acceptedFormats)
			&& !strpos($media->path1, 'youtube.com')
			&& !strpos($media->path1, 'youtu.be')
			&& !strpos($media->path1, 'rtmp://'))
		{
			return '<a href="' . $media->path1 . '" ><img src="' . JUri::root() . $params->get('media_image') . '"/></a>';
		}
		$media->playerwidth  = $params->get('player_width');
		$media->playerheight = $params->get('player_height');

		if ($params->get('playerheight') < 55 && $params->get('playerheight'))
		{
			$media->playerheight = 55;
		}
		elseif ($params->get('playerheight'))
		{
			$media->playerheight = $params->get('playerheight');
		}
		if ($params->get('playerwidth'))
		{
			$media->playerwidth = $params->get('playerwidth');
		}
		if ($params->get('playervars'))
		{
			$media->extraparams = $params->get('playervars');
		}
		if ($params->get('altflashvars'))
		{
			$media->flashvars = $params->get('altflashvars');
		}
		$media->backcolor   = $params->get('backcolor', '0x287585');
		$media->frontcolor  = $params->get('frontcolor', '0xFFFFFF');
		$media->lightcolor  = $params->get('lightcolor', '0x000000');
		$media->screencolor = $params->get('screencolor', '0xFFFFFF');

		if ($params->get('autostart', 1) == 1)
		{
			$media->autostart = 'true';
		}
		else
		{
			$media->autostart = 'false';
		}
		if ($params->get('playeridlehide'))
		{
			$media->playeridlehide = 'true';
		}
		else
		{
			$media->playeridlehide = 'false';
		}
		if ($params->get('autostart') == 1)
		{
			$media->autostart = 'true';
		}
		elseif ($params->get('autostart') == 2)
		{
			$media->autostart = 'false';
		}
		$render = "";
		if ($popup)
		{
			if ($params->get('playerresponsive') != 0)
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
		if ($params->get('playerresponsive') == 0)
		{
			$render .= "'height': '" . $media->playerheight . "',
			";
		}
		else
		{
			$render .= "'aspectratio': '16:9',
			";
		}
		if (isset($media->headertext))($header = $media->headertext); else {$header = $params->get('popuptitle','');}
		$render .= "'width': '" . $media->playerwidth . "',
						'displaytitle': '" . $header . "',
						'image': '" . $params->get('popupimage', 'images/biblestudy/speaker24.png') . "',
						'autostart': '" . $media->autostart . "',
						'backcolor': '" . $media->backcolor . "',
						'frontcolor': '" . $media->frontcolor . "',
						'lightcolor': '" . $media->lightcolor . "',
						'screencolor': '" . $media->screencolor . "',
						'controlbar.position': '" . $params->get('playerposition') . "',
						'controlbar.idlehide': '" . $params->get('playeridlehide') . "'
					});
				</script>";

		return $render;

	}
}

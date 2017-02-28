<?php
/**
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2016 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
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
		$cdn    = $params->get('jwplayer_cdn', 'https://content.jwplatform.com/libraries/HPyI6990.js');

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
	 * Render html for media presentation for JW Player
	 *
	 * @param   object    $media   Media info
	 * @param   Registry  $params  Params from media have to be in object for do to protection.
	 * @param   bool      $popup   If from a popup
	 * @param   object    $player  To make player for audio like (MP3, M4A, etc..)
	 * @param   int       $t       Template id.
	 *
	 * @return  string
	 *
	 * @since 9.0.0
	 */
	public static function render($media, $params, $popup = false, $player = null, $t = null)
	{
		$popupmarg = 0;

		// Used to set for MP3 and audio player look
		if (isset($player->mp3) && $player->mp3 == true)
		{
			$media->playerheight = 30;
		}
		else
		{
			$media->playerheight = $params->get('player_hight');
		}

		$media->path1 = JBSMHelper::MediaBuildUrl($media->sparams->get('path'), $params->get('filename'), $params, true);

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

		if ($params->get('playerheight') < 55 && $params->get('playerheight') && !isset($player->mp3))
		{
			$media->playerheight = 55;
		}
		elseif ($params->get('playerheight') && !isset($player->mp3))
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

			$popupmarg = $params->get('popupmargin', '50');
		}

		$render .= " <div id='placeholder" . $media->id . "'></div>";

		if ($params->get('media_popout_yes', true))
		{
			$popouttext = $params->get('media_popout_text', JText::_('JBS_CMN_POPOUT'));
		}
		else
		{
			$popouttext = '';
		}

		if ($popup || $params->get('pcplaylist'))
		{
			$render .= "</div>";
		}
		else
		{
			// Add space for popup window
			$player->playerwidth  = $player->playerwidth + 20;
			$player->playerheight = $player->playerheight + $popupmarg;
			$render .= "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;player=" . $player->player
				. "&amp;view=popup&amp;t=" . $t . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow', 'width="
				. $player->playerwidth . ",height=" .
				$player->playerheight . "'); return false\">" . $popouttext . "</a>";
		}

		$render .= "<script type=\"text/javascript\">
					var playerInstance" . $media->id . " = jwplayer('placeholder" . $media->id . "');
						playerInstance" . $media->id . ".setup({
							'file': '" . $media->path1 . "',
						";

		if ($params->get('playerresponsive') == 0 && $media->playerheight)
		{
			$render .= "'height': '" . $media->playerheight . "',
			";
		}
		else
		{
			$render .= "'aspectratio': '16:9',
			";
		}

		if (isset($media->headertext))
		{
			$header = $media->headertext;
		}
		elseif ($params->get('pcplaylist'))
		{
			$header = $media->studytitle;
		}
		else
		{
			$header = $params->get('popuptitle', '');
		}

		$render .= "'width': '" . $media->playerwidth . "',
						'title': '" . $header . "',
						'image': '" . $params->get('popupimage', 'images/biblestudy/speaker24.png') . "',
						'autostart': '" . $media->autostart . "',
						'backcolor': '" . $media->backcolor . "',
						'frontcolor': '" . $media->frontcolor . "',
						'lightcolor': '" . $media->lightcolor . "',
						'screencolor': '" . $media->screencolor . "',
						'controlbar.position': '" . $params->get('playerposition') . "',
						'controlbar.idlehide': '" . $params->get('playeridlehide') . "'
					});";

		$render .= "</script>";

		$render .= "<script>
				  function loadVideo(myFile,myImage) { 
				    playerInstance" . $media->id . ".load([{
				      file: myFile,
				      image: myImage
				    }]);
				    playerInstance" . $media->id . ".play();
				  };
				</script>";

		return $render;
	}
}

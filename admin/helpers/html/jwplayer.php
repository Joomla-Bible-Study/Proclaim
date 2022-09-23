<?php
/**
 * @package    Proclaim.Admin
 * @copyright  2007 - 2022 (C) CWM Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.christianwebministries.org
 */

defined('JPATH_PLATFORM') or die;

use CWM\Component\Proclaim\Administrator\Helper\CWMHelper;
use CWM\Component\Proclaim\Administrator\Helper\CWMParams;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Utility class for JWplayer behaviors
 *
 * @package     Proclaim.Admin
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
	 * @throws \Exception
	 * @since   3.0
	 */
	public static function framework()
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		$doc    = Factory::getApplication()->getDocument();
		$params = CWMParams::getAdmin()->params;
		$key    = $params->get('jwplayer_key', '8eJ+ik6aOUabfOisJzomcM2Z3h1VZ9+6cufBXQ==');
		$cdn    = $params->get('jwplayer_cdn', 'https://content.jwplatform.com/libraries/HPyI6990.js');

		if ($cdn)
		{
			$doc->addScriptDeclaration('jwplayer.key="' . $key . '";');
			HtmlHelper::script($cdn);
		}
		else
		{
			HtmlHelper::script('media/com_proclaim/player/jwplayer.js');
			$doc->addScriptDeclaration('jwplayer.key="' . $key . '";');
		}

		self::$loaded[__METHOD__] = true;
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
		if (isset($player->mp3) && $player->mp3 === true)
		{
			$media->playerheight = 30;
		}
		else
		{
			$media->playerheight = $params->get('player_hight');
		}

		$media->path1 = CWMHelper::MediaBuildUrl($media->sparams->get('path'), $params->get('filename'), $params, true);

		// Fall back check to see if JWplayer can play the media. if not will try and return a link to the file.
		$acceptedFormats = array('aac', 'm4a', 'f4a', 'mp3', 'ogg', 'oga', 'mp4', 'm4v', 'f4v', 'mov', 'flv', 'webm', 'm3u8', 'mpd', 'DVR');

		if (!in_array(pathinfo($media->path1, PATHINFO_EXTENSION), $acceptedFormats, true)
			&& !strpos($media->path1, 'youtube.com')
			&& !strpos($media->path1, 'youtu.be')
			&& !strpos($media->path1, 'rtmp://'))
		{
			return '<a href="' . $media->path1 . '" ><img src="' . JUri::root() . $params->get('media_image') . '"/></a>';
		}

		if ($params->get('playerheight') < 55 && $params->get('playerheight') && !isset($player->mp3))
		{
			$media->playerheight = 55;
		}
		elseif ($params->get('playerheight') && !isset($player->mp3))
		{
			$media->playerheight = $params->get('playerheight');
		}

		if ($params->get('playerwidth') && !isset($player->mp3))
		{
			$media->playerwidth = $params->get('playerwidth');
		}
		elseif (isset($player->mp3) && isset($player->playerwidth))
		{
			$media->playerwidth = $player->playerwidth;
		}
		else
		{
			$media->playerwidth = $params->get('player_width');
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

		if ($params->get('autostart', 1) === 1)
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

		if ($params->get('autostart') === 1)
		{
			$media->autostart = 'true';
		}
		elseif ($params->get('autostart') === 2)
		{
			$media->autostart = 'false';
		}

		// Calculate Height base off width for a 16:9 ratio.
		$render = "";
		$rat1   = 16;
		$rat2   = 9;

		$ratio  = $media->playerwidth / $rat1;
		$height = $ratio * $rat2;

		if ($popup)
		{
			if ($params->get('playerresponsive') !== 0)
			{
				$media->playerwidth = '100%';
				$render             .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:100%;\">";
			}
			else
			{
				$render .= "<div class='playeralign' style=\"margin-left: auto; margin-right: auto; width:"
					. $media->playerwidth . "px; height:" . $height . "px;\">";
			}

			$popupmarg = $params->get('popupmargin', '50');
		}

		$render .= " <div id='placeholder" . $media->id . "'  class=\"jbsmplayer\"></div>";

		if ($params->get('media_popout_yes', true))
		{
			$popouttext = $params->get('media_popout_text', Text::_('JBS_CMN_POPOUT'));
		}
		else
		{
			$popouttext = '';
		}

		if ($popup || $params->get('pcplaylist'))
		{
			$render .= "</div>";
		}
		elseif ($popouttext)
		{
			// Add space for popup window
			$player->playerwidth  = $player->playerwidth + 20;
			$player->playerheight = $player->playerheight + $popupmarg;
			$render               .= "<a href=\"#\" onclick=\"window.open('index.php?option=com_proclaim&amp;player=" . $player->player
				. "&amp;view=popup&amp;t=" . $t . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow', 'width="
				. $player->playerwidth . ",height=" .
				$player->playerheight . "'); return false\">" . $popouttext . "</a>";
		}

		$render .= "<script type=\"text/javascript\">
					var playerInstance" . $media->id . " = jwplayer('placeholder" . $media->id . "');
						playerInstance" . $media->id . ".setup({
							'file': '" . $media->path1 . "',
						";
		$render .= "'height': '" . $media->playerheight . "',
		";

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
			$header = str_replace('{{title}}', $media->studytitle, $header);
		}

		$render .= "'width': '" . $media->playerwidth . "',
						'logo': {
							file: '" . $params->get('jwplayer_logo') . "',
							link: '" . $params->get('jwplayer_logolink', JUri::base()) . "',
						 },
						'title': '" . htmlspecialchars($header, ENT_XML1 | ENT_QUOTES, 'UTF-8') . "',
						'image': '" . $params->get('popupimage', 'images/biblestudy/speaker24.png') . "',
						'abouttext': 'Direct Link',
						'aboutlink': '" . $media->path1 . "',
						'autostart': '" . $params->get('autostart') . "',
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
				  }
				</script>";

		return $render;
	}
}

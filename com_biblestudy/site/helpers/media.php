<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  2007 - 2017 (C) Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       https://www.joomlabiblestudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Joomla! Bible Study Media class.
 *
 * @since  7.0.0
 */
class JBSMMedia
{
	/** @type int File Size
	 *
	 * @since    7.0 */
	private $fsize = 0;

	/**
	 * Return Fluid Media row
	 *
	 * @param   Object                    $media     Media info
	 * @param   Joomla\Registry\Registry  $params    Params
	 * @param   TableTemplate             $template  Template Table
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function getFluidMedia($media, $params, $template)
	{
		$mediafile = null;
		$filesize  = null;

		if (isset($media->smedia))
		{
			// Smedia are the media settings for each server
			$registory = new Registry;
			$registory->loadString($media->smedia);
			$media->smedia = $registory;
		}

		// Params are the individual params for the media file record
		$registory = new Registry;
		$registory->loadString($media->params);
		$media->params = $registory;

		// Sparams are the server parameters
		$registory = new Registry;
		$registory->loadString($media->sparams);
		$media->sparams = $registory;

		if ($media->params->get('media_use_button_icon') == -1)
		{
			$imageparams = $media->smedia;
		}
		else
		{
			$imageparams = $media->params;
		}

			if ($imageparams->get('media_use_button_icon') >= 1)
			{
				$image = $this->mediaButton($imageparams);
			}
			else
			{
				$mediaimage = $imageparams->get('media_image');
				$image      = $this->useJImage($mediaimage, $media->params->get('media_button_text', $params->get('download_button_text', 'Audio')));
			}

		// New Podcast Playlist cast Player code override option.
		$player     = self::getPlayerAttributes($params, $media);
		$playercode = self::getPlayerCode($params, $player, $image, $media);
		$downloadlink  = self::getFluidDownloadLink($media, $params, $template);

		if ($params->get('pcplaylist'))
		{
			$link_type = 0;
		}
		elseif ($media->params->get('link_type') == 0 || $media->params->get('link_type'))
		{
			$link_type = $media->params->get('link_type');
		}
		else
		{
			$link_type = $media->smedia->get('link_type');
		}

		if ($params->get('show_filesize') > 0 && isset($media) && $link_type < 2)
		{
				$file_size = $media->params->get('size', '0');

				if (!$file_size)
				{
					$file_size = JBSMHelper::getRemoteFileSize(
						JBSMHelper::MediaBuildUrl($media->sparams->get('path'), $media->params->get('filename'), $params, true)
					);
				}

				switch ($file_size)
				{
					case  $file_size < 1024 :
						$file_size = ' ' . 'Bytes';
						break;
					case $file_size < 1048576 :
						$file_size = $file_size / 1024;
						$file_size = number_format($file_size, 0);
						$file_size = $file_size . ' ' . 'KB';
						break;
					case $file_size < 1073741824 :
						$file_size = $file_size / 1024;
						$file_size = $file_size / 1024;
						$file_size = number_format($file_size, 1);
						$file_size = $file_size . ' ' . 'MB';
						break;
					case $file_size > 1073741824 :
						$file_size = $file_size / 1024;
						$file_size = $file_size / 1024;
						$file_size = $file_size / 1024;
						$file_size = number_format($file_size, 1);
						$file_size = $file_size . ' ' . 'GB';
						break;
				}

			switch ($params->get('show_filesize'))
			{
				case 1:

					break;
				case 2:
					$file_size = $media->comment;
					break;
				case 3:
					if ($media->comment)
					{
						$file_size = $media->comment;
					}
					break;
			}

			$filesize = '<span class="JBSMFilesize" style="font-size: 0.6em;display:inline;padding-left: 5px;">' .
				$file_size . '</span>';
		}

		switch ($link_type)
		{
			case 0:
				$mediafile = $playercode;
				break;

			case 1:
				if ($downloadlink)
				{
					$mediafile = $playercode . '<div style="display:inline;position:relative;">' . $downloadlink . $filesize . '</div>';
				}
				else
				{
					$mediafile = $playercode;
				}
				break;

			case 2:
				$mediafile = $downloadlink;
				break;
		}

		return $mediafile;
	}

	/**
	 * Return download link
	 *
	 * @param   Object                    $media     Media
	 * @param   Joomla\Registry\Registry  $params    Params
	 * @param   TableTemplate             $template  Template ID
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function getFluidDownloadLink($media, $params, $template)
	{
		// Remove download form Youtube links.
		$filename = $media->params->get('filename');
		$link_type = 0;

		if (substr_count($filename, 'youtube') || substr_count($filename, 'youtu.be'))
		{
			return '';
		}

		$downloadlink = '';

		if ($params->get('download_use_button_icon') >= 2)
		{
			$download_image = $this->downloadButton($params);
		}
		elseif ($params->get('default_download_image'))
		{
			$d_image = $params->get('default_download_image');
			$download_image = $this->useJImage($d_image, JText::_('JBS_MED_DOWNLOAD'));
		}
		else
		{
			$d_image = 'media/com_biblestudy/images/download.png';
			$download_image = $this->useJImage($d_image, JText::_('JBS_MED_DOWNLOAD'));
		}

		if ($media->params->get('link_type'))
		{
			$link_type = $media->params->get('link_type');
		}

		if ($media->params->get('download_show') && (!$media->params->get('link_type')))
		{
			$link_type = 2;
		}

		if ($link_type > 0)
		{
			$compat_mode = $params->get('compat_mode');

			if ($compat_mode == 0)
			{
				$downloadlink = '<a href="index.php?option=com_biblestudy&amp;view=sermon&amp;mid=' .
					$media->id . '&amp;task=download">';
			}
			else
			{
				$url = JBSMHelper::MediaBuildUrl($media->sparams->get('path'), $media->params->get('filename'), $params, true);

				if ($media->params->get('size') !== '0')
				{
					$size = JBSMHelper::getRemoteFileSize($url);
				}
				else
				{
					$size = $media->params->get('size');
				}

				$downloadlink = '<a href="http://joomlabiblestudy.org/router.php?file=' .
					$url . '&amp;size=' . $size . '">';
			}

			// Check to see if they want to use a popup

			if ($params->get('useterms') > 0)
			{
				$downloadlink = '<a class="modal" href="index.php?option=com_biblestudy&amp;view=terms&amp;tmpl=component&amp;layout=modal&amp;compat_mode='
					. $compat_mode . '&amp;mid=' . $media->id . '&amp;t=' . $template->id . '" rel="{handler: \'iframe\', size: {x: 640, y: 480}}">';
			}

			$downloadlink .= $download_image . '</a>';
		}

		return $downloadlink;
	}

	/**
	 * Used to obtain the button and/or icon for the image
	 *
	 * @param   Registry  $imageparams  ?
	 *
	 * @return mixed
	 *
	 * @since 9.0.0
	 */
	public function mediaButton($imageparams)
	{
		$mediaimage = null;
		$button = $imageparams->get('media_button_type', 'btn-link');
		$buttontext = $imageparams->get('media_button_text', 'Audio');
		$textsize = $imageparams->get('media_icon_text_size', '24');

		if ($imageparams->get('media_button_color'))
		{
			$color = 'style="background-color:' . $imageparams->get('media_button_color') . ';"';
		}
		else
		{
			$color = '';
		}

		switch ($imageparams->get('media_use_button_icon'))
		{
			case 1:
				// Button only
				$mediaimage = '<div  type="button" class="btn ' . $button . ' title="' . $buttontext . '" ' . $color . '>' . $buttontext . '</div>';
				break;
			case 2:
				// Button and icon
				if ($imageparams->get('media_icon_type') == '1')
				{
					$icon = $imageparams->get('media_custom_icon');
				}
				else
				{
					$icon = $imageparams->get('media_icon_type', 'fa fa-play');
				}

				$mediaimage = '<div  type="button" class="btn ' . $button . '" title="' . $buttontext . '" ' . $color . '><span class="' .
						$icon . '" title="' . $buttontext . '" style="font-size:' . $textsize . 'px;"></span></div>';
				break;
			case 3:
				// Icon only
				if ($imageparams->get('media_icon_type') == 1)
				{
					$icon = $imageparams->get('media_custom_icon');
				}
				else
				{
					$icon = $imageparams->get('media_icon_type', 'fa fa-play');
				}

				$mediaimage = '<span class="' . $icon . '" title="' . $buttontext . '" style="font-size:' . $textsize . 'px;"></span>';
				break;
		}

		return $mediaimage;
	}

	/**
	 * Used to obtain the button and/or icon for the image
	 *
	 * @param   Registry  $download  ?
	 *
	 * @return mixed
	 *
	 * @since 9.0.0
	 */
	public function downloadButton($download)
	{
		$downloadimage = null;
		$button = $download->get('download_button_type', 'btn-link');
		$buttontext = $download->get('download_button_text', 'Audio');
		$textsize = $download->get('download_icon_text_size', '24');

		if ($download->get('download_button_color'))
		{
			$color = 'style="background-color:' . $download->get('download_button_color') . ';"';
		}
		else
		{
			$color = '';
		}

		switch ($download->get('download_use_button_icon'))
		{
			case 2:
				// Button only
				$downloadimage = '<div type="button" class="btn ' . $button . ' title="' . $buttontext . '" ' . $color . '>' . $buttontext . '</div>';
				break;
			case 3:
				// Button and icon
				if ($download->get('download_icon_type') == '1')
				{
					$icon = $download->get('download_custom_icon');
				}
				else
				{
					$icon = $download->get('download_icon_type', 'icon-play');
				}

				$downloadimage = '<div type="button" class="btn ' . $button . '" title="' . $buttontext . '" ' . $color . '><span class="' .
						$icon . '" title="' . $buttontext . '" style="font-size:' . $textsize . 'px;"></span></div>';
				break;
			case 4:
				// Icon only
				if ($download->get('download_icon_type') == 1)
				{
					$icon = $download->get('download_custom_icon');
				}
				else
				{
					$icon = $download->get('download_icon_type', 'icon-play');
				}

				$downloadimage = '<span class="' . $icon . '" title="' . $buttontext . '" style="font-size:' . $textsize . 'px;"></span>';
				break;
		}

		return $downloadimage;
	}

	/**
	 * Use JImage to create images
	 *
	 * @param   string  $path  Path to file
	 * @param   string  $alt   Accessibility string
	 *
	 * @return bool|string
	 *
	 * @since 9.0.0
	 */
	public function useJImage($path, $alt)
	{
		if (!$path)
		{
			return false;
		}

		try
		{
			$return = JImage::getImageFileProperties($path);
		}
		catch (Exception $e)
		{
			return $alt;
		}

		$imagereturn = '<img src="' . JUri::base() . $path . '" alt="' . $alt . '" ' . $return->attributes . ' >';

		return $imagereturn;
	}

	/**
	 * Set up Player Attributes
	 *
	 * @param   Joomla\Registry\Registry  $params  Params
	 * @param   object                    $media   Media info
	 *
	 * @return object
	 *
	 * @since 9.0.0
	 */
	public function getPlayerAttributes($params, $media)
	{
		$player               = new stdClass;
		$player->playerwidth  = $params->get('player_width');
		$player->playerheight = $params->get('player_height');

		if ($params->get('playerheight'))
		{
			$player->playerheight = $params->get('playerheight');
		}

		if ($params->get('playerwidth'))
		{
			$player->playerwidth = $params->get('playerwidth');
		}

		/**
		 * @desc Players - from Template:
		 * First we check to see if in the template the user has set to use the internal player for all media. This can be overridden by itemparams
		 * popuptype = whether AVR should be window or lightbox (handled in avr code)
		 * internal_popup = whether direct or internal player should be popup/new window or inline
		 * From media file:
		 * player 0 = direct, 1 = internal, 2 = AVR, 3 = AV 7 = legacy internal player (from JBS 6.2.2)
		 * internal_popup 0 = inline, 1 = popup, 2 = global settings
		 *
		 * Get the $player->player: 0 = direct, 1 = internal, 2 = AVR (no longer supported),
		 * 3 = All Videos or JPlayer, 4 = Docman, 5 = article, 6 = Virtuemart, 7 = legacy player, 8 = embed code
		 * $player->type 0 = inline, 1 = popup/new window 3 = Use Global Settings (from params)
		 * In 6.2.3 we changed inline = 2
		 */
		$player->player   = 0;
		$item_mediaplayer = $media->params->get('player');

		// Check to see if the item player is set to 100 - that means use global settings which comes from $params
		if ($item_mediaplayer == 100)
		{
			// Player is set from the $params
			$player->player = $params->get('media_player', '0');
		}
		else
		{
			/* In this case the item has a player set for it, so we use that instead. We also need to change the old player
					type of 3 to 2 for all videos reloaded which we don't support */
			if ($params->get('pcplaylist'))
			{
				$player->player = 7;
			}
			elseif ($media->params->get('player', null) !== null)
			{
				$player->player = $media->params->get('player');
			}
			else
			{
				$player->player = $params->get('player', 0);
			}
		}

		if ($player->player == 3)
		{
			$player->player = 2;
		}

		if ($params->get('docMan_id') != 0)
		{
			$player->player = 4;
		}

		if ($params->get('article_id') > 0)
		{
			$player->player = 5;
		}

		if ($params->get('virtueMart_id') > 0)
		{
			$player->player = 6;
		}

		$player->type = 1;

		// This is the global parameter set in Template Display settings
		$param_playertype = $params->get('internal_popup', 1);

		if ($params->get('pcplaylist', false))
		{
			$item_playertype = 2;
		}
		else
		{
			$item_playertype = $params->get('popup');
		}

		if ($param_playertype && !$media->params->get('popup') && !$params->get('pcplaylist', false))
		{
			$player->type = $param_playertype;
		}
		else
		{
			$player->type = $media->params->get('popup');
		}

		switch ($item_playertype)
		{
			case 3:
				$player->type = $param_playertype;
				break;

			case 2:
				$player->type = 2;
				break;

			case 1:
				$player->type = 1;
				break;
		}

		return $player;
	}

	/**
	 * Setup Player Code.
	 *
	 * @param   Joomla\Registry\Registry  $params  Params are the merged of system and items.
	 * @param   object                    $player  Player code
	 * @param   String                    $image   Image info
	 * @param   object                    $media   Media
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function getPlayerCode($params, $player, $image, $media)
	{
		// Merging the item params into the global.
		$params = clone $params;
		$params->merge($media->params);

		$input       = new JInput;
		$template    = $input->getInt('t', '1');

		// Here we get more information about the particular media file
		$filesize = self::getFluidFilesize($media, $params);
		$duration = self::getFluidDuration($media, $params);

		$path = JBSMHelper::MediaBuildUrl($media->sparams->get('path'), $params->get('filename'), $params, true);

		switch ($player->player)
		{
			case 0: // Direct

				switch ($player->type)
				{
					case 2: // New window
						$playercode = '<a href="' . $path . '" onclick="window.open(\'index.php?option=com_biblestudy&amp;view=popup&amp;close=1&amp;mediaid=' .
							$media->id . '\',\'newwindow\',\'width=100, height=100,menubar=no, status=no,location=no,toolbar=no,scrollbars=no\'); return true;" title="' .
							$media->params->get("media_button_text") . ' - ' . $media->comment . ' ' . $duration . ' '
							. $filesize . '" target="' . $params->get('special') . '" class="jbsmplayerlink">' . $image . '</a>';
						break;

					case 3: // Squeezebox view

						return $this->rendersb($media, $params, $player, $image, $path, true);
						break;

					case 1: // Popup window
						$playercode = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;player=" . $params->toObject()->player .
								"&amp;view=popup&amp;t=" . $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" .
								$player->playerwidth . ",height=" . $player->playerheight . "'); return false\"  class=\"jbsmplayerlink\">" . $image . "</a>";
						break;
				}

				/** @var $playercode string */
				return $playercode;
				break;

			case 7:
			case 1: // Internal
				switch ($player->type)
				{
					case 3: // Squeezebox view

						return $this->rendersb($media, $params, $player, $image, $path);
						break;

					case 2: // Inline
						JHtml::_('Jwplayer.framework', true, true);

						if ($player->player == 7)
						{
							$player->playerheight = '40';
							$player->boxplayerheight = '40';
							$player->mp3 = true;

							if ($player->playerwidth <= '259')
							{
								$player->playerwidth = '260';
							}
						}

						$playercode = JHtmlJwplayer::render($media, $params, false, $player, $template);
						break;

					case 1: // Popup
						// Add space for popup window
						$diff = $params->get('player_width') - $params->get('playerwidth');
						$player->playerwidth  = $player->playerwidth + abs($diff) + 10;
						$player->playerheight = $player->playerheight + $params->get('popupmargin', '50');
						$playercode           = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;player=" . $player->player
							. "&amp;view=popup&amp;t=" . $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow', 'width="
							. $player->playerwidth . ", height=" .
							$player->playerheight . "'); return false\" class=\"jbsmplayerlink\">" . $image . "</a>";
						break;
				}

				/** @var $playercode string */
				return $playercode;
				break;

			case 2: // All Videos Reloaded
			case 3:
				switch ($player->type)
				{
					case 1: // This goes to the popup view
						$playercode = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;view=popup&amp;player=3&amp;t=" . $template .
							"&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height="
							. $player->playerheight . "'); return false\"  class=\"jbsmplayerlink\">" . $image . "</a>";
						break;

					case 2: // This plays the video inline
						$mediacode  = $this->getAVmediacode($media->mediacode, $media);
						$playercode = JHtml::_('content.prepare', $mediacode);
						break;
				}

				/** @var $playercode string */

				return $playercode;
				break;

			case 4: // Docman
				$playercode = $this->getDocman($media, $image);

				return $playercode;
				break;

			case 5: // Article
				$playercode = $this->getArticle($media, $image);

				return $playercode;
				break;

			case 6: // Virtuemart
				$playercode = $this->getVirtuemart($media, $image);

				return $playercode;
				break;

			case 8: // Embed code
				$playercode = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;view=popup&amp;player=8&amp;t=" . $template .
					"&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height="
					. $player->playerheight . "'); return false\">" . $image . "</a>";

				return $playercode;
				break;
		}

		return false;
	}

	/**
	 * Render Sqeezbox
	 *
	 * @param   object    $media   Media
	 * @param   Registry  $params  Params.
	 * @param   object    $player  Player settings
	 * @param   string    $image   The image
	 * @param   string    $path    The path to the media
	 * @param   bool      $direct  If coming from Direct
	 *
	 * @return string
	 *
	 * @since version
	 */
	public function rendersb($media, $params, $player, $image, $path, $direct = false)
	{
		JHtml::_('fancybox.framework', true, true);

		if ($player->player == 7 && !$direct)
		{
			$player->playerheight = '40';
		}

		if ($params->get('media_popout_yes', true))
		{
			$popout = $params->get('media_popout_text', JText::_('JBS_CMN_POPOUT'));
		}
		else
		{
			$popout = '';
		}

		$playercode = '<a data-src="' . $path . '" id="linkmedia' . $media->id . '" title="' . $params->get('filename') .
			'" class="fancybox fancybox_jwplayer" potext="' . $popout . '" ptype="' . $player->player .
			'" pwidth="' . $player->playerwidth . '" pheight="' .
			$player->playerheight . '" autostart="' . $params->get('autostart', false) . '" controls="' .
			$params->get('controls') . '"" data-image="' . $params->get('jwplayer_image') . '" data-mute="' .
			$params->get('jwplayer_mute') . '" data-logo="' . $params->get('jwplayer_logo') . '" data-logolink="' .
			$params->get('jwplayer_logolink', JUri::base()) . '">' .
			$image . '</a>';

		return $playercode;
	}

	/**
	 * return $table
	 *
	 * @param   Object                    $media   Media info
	 * @param   Joomla\Registry\Registry  $params  Params
	 *
	 * @return null|string
	 *
	 * @since 9.0.0
	 */
	public function getFluidFilesize($media, $params)
	{
		$filesize = '';

		$file_size = $media->params->get('size', '0');

		if (!$file_size)
		{
			$file_size = JBSMHelper::getRemoteFileSize(JBSMHelper::MediaBuildUrl($media->sparams->get('path'), $params->get('filename'), $params, true));
		}

		if ($file_size)
		{
			switch ($file_size)
			{
				case  $file_size < 1024 :
					$file_size = ' ' . 'Bytes';
					break;
				case $file_size < 1048576 :
					$file_size = $file_size / 1024;
					$file_size = number_format($file_size, 0);
					$file_size = $file_size . ' ' . 'KB';
					break;
				case $file_size < 1073741824 :
					$file_size = $file_size / 1024;
					$file_size = $file_size / 1024;
					$file_size = number_format($file_size, 1);
					$file_size = $file_size . ' ' . 'MB';
					break;
				case $file_size > 1073741824 :
					$file_size = $file_size / 1024;
					$file_size = $file_size / 1024;
					$file_size = $file_size / 1024;
					$file_size = number_format($file_size, 1);
					$file_size = $file_size . ' ' . 'GB';
					break;
			}

			switch ($params->get('show_filesize'))
			{
				case 1:
					$filesize = $file_size;
					break;
				case 2:
					$filesize = $media->comment;
					break;
				case 3:
					if ($media->comment)
					{
						$filesize = $media->comment;
					}
					else
					{
						($filesize = $file_size);
					}
					break;
			}
		}

		$this->fsize = $filesize;

		return $filesize;
	}

	/**
	 * Get duration
	 *
	 * @param   Object                    $row     Table Row info
	 * @param   Joomla\Registry\Registry  $params  Params
	 *
	 * @return null|string
	 *
	 * @since 9.0.0
	 */
	public function getFluidDuration($row, $params)
	{
		$duration = $row->media_hours . $row->media_minutes . $row->media_seconds;

		if (!$duration)
		{
			$duration = null;

			return $duration;
		}

		$duration_type = $params->get('duration_type', 2);
		$hours         = $row->media_hours;
		$minutes       = $row->media_minutes;
		$seconds       = $row->media_seconds;

		switch ($duration_type)
		{
			case 1:
				if (!$hours)
				{
					$duration = $minutes . ' mins ' . $seconds . ' secs';
				}
				else
				{
					$duration = $hours . ' hour(s) ' . $minutes . ' mins ' . $seconds . ' secs';
				}
				break;
			case 2:
				if (!$hours)
				{
					$duration = $minutes . ':' . $seconds;
				}
				else
				{
					$duration = $hours . ':' . $minutes . ':' . $seconds;
				}
				break;
			default:
				$duration = $hours . ':' . $minutes . ':' . $seconds;
				break;
		}

		return $duration;
	}

	/**
	 * Update Hit count for plays.
	 *
	 * @param   int  $id  ID to apply the hit to.
	 *
	 * @return boolean
	 *
	 * @since 9.0.0
	 */
	public function hitPlay($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__bsms_mediafiles')
			->set('plays = plays + 1')
			->where('id = ' . $db->q($id));
		$db->setQuery($query);

		if ($db->execute())
		{
			return true;
		}

		return false;
	}

	/**
	 * Get Media info Row2
	 *
	 * @param   int  $id  ID of Row
	 *
	 * @return object|boolean
	 *
	 * @since 9.0.0
	 */
	public function getMediaRows2($id)
	{
		// We use this for the popup view because it relies on the media file's id rather than the study_id field above
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('#__bsms_mediafiles.*, #__bsms_servers.params AS sparams,'
			. ' s.studyintro, s.media_hours, s.media_minutes, s.series_id,'
			. ' s.media_seconds, s.studytitle, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin,'
			. ' s.verse_end, t.teachername, t.teacher_thumbnail, t.teacher_image, t.thumb, t.image, t.id as tid, s.id as sid, s.studyintro,'
			. ' se.id as seriesid, se.series_text, se.series_thumbnail')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server_id)')
			->leftJoin('#__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id)')
			->leftJoin('#__bsms_teachers AS t ON (t.id = s.teacher_id)')
			->leftJoin('#__bsms_series AS se ON (s.series_id = se.id)')
			->where('#__bsms_mediafiles.id = ' . (int) $id)
			->where('#__bsms_mediafiles.published = ' . 1)
			->where('#__bsms_mediafiles.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->q('*') . ')')
			->order('ordering asc');
		$db->setQuery($query);
		$media = $db->loadObject();

		if ($media)
		{
			$reg = new Registry;
			$reg->loadString($media->sparams);
			$params = $reg->toObject();

			if (isset($params->path))
			{
				$media->spath = $params->path;
			}
			else
			{
				$media->spath = '';
			}

			return $media;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Return AVMedia Code.
	 *
	 * @param   string  $mediacode  Media string
	 * @param   object  $media      Media info
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function getAVmediacode($mediacode, $media)
	{
		$bracketpos   = strpos($mediacode, '}');
		$bracketend   = strpos($mediacode, '{', $bracketpos);
		$dashposition = strpos($mediacode, '-', $bracketpos);
		$isonlydash   = substr_count($mediacode, '}-{');

		if ($isonlydash)
		{
			$mediacode = substr_replace($mediacode, 'http://' . JBSMHelper::MediaBuildUrl($media->spath, $media->filename, null), $dashposition, 1);
		}
		elseif ($dashposition)
		{
			$mediacode = substr_replace($mediacode, JBSMHelper::MediaBuildUrl($media->spath, $media->filename, null), $bracketend - 1, 1);
		}

		return $mediacode;
	}

	/**
	 * Return Docman Media
	 *
	 * @param   object  $media  Media
	 * @param   string  $image  Image
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function getDocman($media, $image)
	{
		$url = 'com_docman';

		$getmenu  = JFactory::getApplication();
		$menuItem = $getmenu->getMenu()->getItems('component', $url, true);
		$Itemid   = $menuItem->id;
		$docman   = '<a href="index.php?option=com_docman&amp;view=document&amp;slug=' .
			$media->docMan_id . '&amp;Itemid=' . $Itemid . '" alt="' . $media->malttext . ' - ' . $media->comment .
			'" target="' . $media->special . '">' . $image . '</a>';

		return $docman;
	}

	/**
	 * Return Articles.
	 *
	 * @param   object  $media  Media
	 * @param   string  $image  Image
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function getArticle($media, $image)
	{
		$article = '<a href="index.php?option=com_content&amp;view=article&amp;id=' . $media->article_id . '"
                 alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '">' . $image . '</a>';

		return $article;
	}

	/**
	 * Set up Virtumart if Vertumart is installed.
	 *
	 * @param   object  $media  Media
	 * @param   string  $image  Image
	 *
	 * @return string
	 *
	 * @since 9.0.0
	 */
	public function getVirtuemart($media, $image)
	{
		$vm = '<a href="index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' . $media->virtueMart_id . '"
                alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '">' . $image . '</a>';

		return $vm;
	}

	/**
	 * List of MimeTypes Supported
	 *
	 * @return array
	 *
	 * @since 9.0.12
	 */
	public function getMimetypes()
	{
		$mimetype = array(
			// Image formats
			'jpg|jpeg|jpe'                 => 'image/jpeg',
			'gif'                          => 'image/gif',
			'png'                          => 'image/png',
			'bmp'                          => 'image/bmp',
			'tif|tiff'                     => 'image/tiff',
			'ico'                          => 'image/x-icon',

			// Video formats
			'asf|asx'                      => 'video/x-ms-asf',
			'wmv'                          => 'video/x-ms-wmv',
			'wmx'                          => 'video/x-ms-wmx',
			'wm'                           => 'video/x-ms-wm',
			'avi'                          => 'video/avi',
			'divx'                         => 'video/divx',
			'flv'                          => 'video/x-flv',
			'mov|qt'                       => 'video/quicktime',
			'mpeg|mpg|mpe'                 => 'video/mpeg',
			'mp4|m4v'                      => 'video/mp4',
			'ogv'                          => 'video/ogg',
			'webm'                         => 'video/webm',
			'mkv'                          => 'video/x-matroska',

			// Text formats
			'txt|asc|c|cc|h'               => 'text/plain',
			'csv'                          => 'text/csv',
			'tsv'                          => 'text/tab-separated-values',
			'ics'                          => 'text/calendar',
			'rtx'                          => 'text/richtext',
			'css'                          => 'text/css',
			'htm|html'                     => 'text/html',

			// Audio formats
			'm4a|m4b'                      => 'audio/mpeg',
			'mp3'                          => 'audio/mp3',
			'ra|ram'                       => 'audio/x-realaudio',
			'wav'                          => 'audio/wav',
			'ogg|oga'                      => 'audio/ogg',
			'mid|midi'                     => 'audio/midi',
			'wma'                          => 'audio/x-ms-wma',
			'wax'                          => 'audio/x-ms-wax',
			'mka'                          => 'audio/x-matroska',

			// Misc application formats
			'rtf'                          => 'application/rtf',
			'js'                           => 'application/javascript',
			'pdf'                          => 'application/pdf',
			'swf'                          => 'application/x-shockwave-flash',
			'class'                        => 'application/java',
			'tar'                          => 'application/x-tar',
			'zip'                          => 'application/zip',
			'gz|gzip'                      => 'application/x-gzip',
			'rar'                          => 'application/rar',
			'7z'                           => 'application/x-7z-compressed',
			'exe'                          => 'application/x-msdownload',

			// MS Office formats
			'doc'                          => 'application/msword',
			'pot|pps|ppt'                  => 'application/vnd.ms-powerpoint',
			'wri'                          => 'application/vnd.ms-write',
			'xla|xls|xlt|xlw'              => 'application/vnd.ms-excel',
			'mdb'                          => 'application/vnd.ms-access',
			'mpp'                          => 'application/vnd.ms-project',
			'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'docm'                         => 'application/vnd.ms-word.document.macroEnabled.12',
			'dotx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'dotm'                         => 'application/vnd.ms-word.template.macroEnabled.12',
			'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xlsm'                         => 'application/vnd.ms-excel.sheet.macroEnabled.12',
			'xlsb'                         => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
			'xltx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'xltm'                         => 'application/vnd.ms-excel.template.macroEnabled.12',
			'xlam'                         => 'application/vnd.ms-excel.addin.macroEnabled.12',
			'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'pptm'                         => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
			'ppsx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'ppsm'                         => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
			'potx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'potm'                         => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
			'ppam'                         => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
			'sldx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'sldm'                         => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
			'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',

			// OpenOffice formats
			'odt'                          => 'application/vnd.oasis.opendocument.text',
			'odp'                          => 'application/vnd.oasis.opendocument.presentation',
			'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet',
			'odg'                         => 'application/vnd.oasis.opendocument.graphics',
			'odc'                          => 'application/vnd.oasis.opendocument.chart',
			'odb'                          => 'application/vnd.oasis.opendocument.database',
			'odf'                          => 'application/vnd.oasis.opendocument.formula',

			// WordPerfect formats
			'wp|wpd'                       => 'application/wordperfect',

			// IWork formats
			'key'                          => 'application/vnd.apple.keynote',
			'numbers'                      => 'application/vnd.apple.numbers',
			'pages'                        => 'application/vnd.apple.pages',
		);

		return $mimetype;
	}
}

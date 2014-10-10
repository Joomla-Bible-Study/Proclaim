<?php
/**
 * Part of Joomla BibleStudy Package
 *
 * @package    BibleStudy.Admin
 * @copyright  (C) 2007 - 2013 Joomla Bible Study Team All rights reserved
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link       http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.defines.php';
JLoader::register('JBSMImages', BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php');
JLoader::register('JBSMHelper', BIBLESTUDY_PATH_ADMIN_HELPERS . '/helper.php');
JLoader::register('JBSMElements', BIBLESTUDY_PATH_ADMIN_HELPERS . '/elements.php');

/**
 * Joomla! Bible Study Media class.
 *
 * @package  BibleStudy.Site
 * @since    7.0.0
 *
 * @todo     this mey not be needed if elements has this all. If not we need to refactor it. tom
 */
class jbsMedia
{

	/**
	 * Return Media Table
	 *
	 * @param   object $row           Table info
	 * @param   object $params        Item Params
	 * @param   object $admin_params  Admin Params
	 *
	 * @return null|string
	 */
	public function getMediaTable($row, $params, $admin_params)
	{
		// First we get some items from GET and instantiate the images class
		$input        = new JInput;
		$template     = $input->get('t', '1', 'int');
		$images       = new JBSMImages;
		$filesize     = null;
		$downloadlink = null;

		// Here we get the administration row from the component, and determine the download image to use

		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__bsms_admin');
		$db->setQuery($query);
		$admin    = $db->loadObject();
		$registry = new JRegistry;
		$registry->loadString($admin->params);
		$admin->params = $registry->toArray();

		if (isset($admin->params['default_download_image']))
		{
			$admin_d_image = $admin->params['default_download_image'];
		}
		else
		{
			$admin_d_image = null;
		}
		$d_image = ($admin_d_image ? $admin_d_image : 'download.png');

		$download_image = $images->getMediaImage($d_image, $media = null);

		$compat_mode = $admin_params->get('compat_mode');

		// Here we get a list of the media ids associated with the study we got from $row

		$mediaids = self::getMediaRows($row->id);

		if (!$mediaids)
		{
			$table = null;

			return $table;
		}

		// Here is where we begin to build the table
		$table = '<table class="table mediatable"><tbody><tr>';

		// Now we look at each mediaid, and get the rest of the media information
		foreach ($mediaids AS $media)
		{
			// Step 1 is to get the media file
			$image = $images->getMediaImage($media->impath, $media->path2);

			// Convert parameter fields to objects.
			$registry = new JRegistry;
			$registry->loadString($media->params);
			$itemparams = $registry;

			// Get the attributes for the player used in this item
			$player     = self::getPlayerAttributes($admin_params, $params, $itemparams, $media);
			$playercode = self::getPlayerCode($params, $itemparams, $player, $image, $media);

			// Now we build the column for each media file
			$table .= '<td>';


			// Check to see if a download link is needed

			$link_type = $media->link_type;

			if ($link_type > 0)
			{

				$width  = $download_image->width;
				$height = $download_image->height;

				if ($compat_mode == 0)
				{
					$downloadlink = '<a href="index.php?option=com_biblestudy&amp;mid=' .
						$media->id . '&amp;view=sermons&amp;task=download">';
				}
				else
				{
					$downloadlink = '<a href="//joomlabiblestudy.org/router.php?file=' .
						$media->spath . $media->fpath . $media->filename . '&amp;size=' . $media->size . '">';
				}

				// Check to see if they want to use a popup
				if ($params->get('useterms') > 0)
				{

					$downloadlink = '<a class="modal" href="index.php?option=com_biblestudy&amp;view=terms&amp;tmpl=component&amp;layout=modal&amp;compat_mode='
						. $compat_mode . '&amp;mid=' . $media->id . '&amp;t=' . $template . '" rel="{handler: \'iframe\', size: {x: 640, y: 480}}">';
				}
				$downloadlink .= '<img src="' . $download_image->path . '" alt="' . JText::_('JBS_MED_DOWNLOAD') . '" height="' .
					$height . '" width="' . $width . '" border="0" title="' . JText::_('JBS_MED_DOWNLOAD') . '" /></a>';
			}
			switch ($link_type)
			{
				case 0:
					$table .= $playercode;
					break;

				case 1:
					$table .= $playercode . $downloadlink;
					break;

				case 2:
					$table .= $downloadlink;
					break;
			}
			// End of the column holding the media image
			$table .= '</td>';

		} // End of foreach mediaids
		// End of row holding media image/link
		$table .= '</tr>';
		$JBSMElements = new JBSMElements;

		// This is the last part of the table where we see if we need to display the file size
		if ($params->get('show_filesize') > 0 && isset($media))
		{
			$table .= '<tr>';

			foreach ($mediaids as $media)
			{
				switch ($params->get('show_filesize'))
				{
					case 1:
						$filesize = $JBSMElements->getFilesize($media->size);
						break;
					case 2:
						$filesize = $media->comment;
						break;
					case 3:
						if ($media->comment ? $filesize = $media->comment : $filesize = $JBSMElements->getFilesize($media->size))
						{
						}
						break;
				}

				$table .= '<td><span class="bsfilesize">' . $filesize . '</span></td>';

			} // End second foreach
			$table .= '</tr>';

		} // End of if show_file size

		$table .= '</tbody></table>';

		return $table;
	}

	/**
	 * Get Media ID
	 *
	 * @param   int $id  ID of media
	 *
	 * @return object
	 */
	public function getMediaid($id)
	{
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('m.id as mid, m.study_id, s.id as sid')
			->from('#__bsms_mediafiles AS m')
			->leftJoin('#__bsms_studies AS s ON (m.study_id = s.id) WHERE s.id = ' . (int) $db->q($id));
		$db->setQuery($query);
		$mediaids = $db->loadObjectList();

		return $mediaids;
	}

	/**
	 * Get Media info Row1
	 *
	 * @param   int $id  ID of media Row
	 *
	 * @return object|boolean
	 */
	public function getMediaRows($id)
	{
		if (!$id)
		{
			return false;
		}
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('#__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath, #__bsms_folders.id AS fid,'
		. ' #__bsms_folders.folderpath AS fpath, #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, '
		. ' #__bsms_media.media_image_name AS imname,'
		. ' #__bsms_media.path2 AS path2, s.studytitle, s.studydate, s.studyintro, s.media_hours, s.media_minutes, s.media_seconds, s.teacher_id,'
		. ' s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin, s.verse_end, t.teachername, t.id as tid, s.id as sid, s.studyintro,'
		. ' #__bsms_media.media_alttext AS malttext, #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext, #__bsms_mimetype.mimetype');

		$query->from('#__bsms_mediafiles');

		$query->leftJoin('#__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)');

		$query->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)');

		$query->leftJoin('#__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)');

		$query->leftJoin('#__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)');

		$query->leftJoin('#__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id)');

		$query->leftJoin('#__bsms_teachers AS t ON (t.id = s.teacher_id)');

		$query->where('#__bsms_mediafiles.study_id = ' . (int) $id);
		$query->where('#__bsms_mediafiles.published = 1');
		$query->order('ordering ASC, #__bsms_media.media_image_name ASC');
		$db->setQuery($query);
		$media = $db->loadObjectList();

		if ($media)
		{
			return $media;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get Media info Row2
	 *
	 * @param   int $id  ID of Row
	 *
	 * @return object|boolean
	 */
	public function getMediaRows2($id)
	{
		// We use this for the popup view because it relies on the media file's id rather than the study_id field above
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('#__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath, #__bsms_folders.id AS fid,'
		. ' #__bsms_folders.folderpath AS fpath, #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath,'
		. ' #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2, s.studyintro, s.media_hours, s.media_minutes, s.series_id,'
		. ' s.media_seconds, s.studytitle, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin,'
		. ' s.verse_end, t.teachername, t.teacher_thumbnail, t.teacher_image, t.thumb, t.image, t.id as tid, s.id as sid, s.studyintro,'
		. ' #__bsms_media.media_alttext AS malttext,'
		. ' se.id as seriesid, se.series_text, se.series_thumbnail,'
		. ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext, #__bsms_mimetype.mimetype')
			->from('#__bsms_mediafiles')
			->leftJoin('#__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)')
			->leftJoin('#__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)')
			->leftJoin('#__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)')
			->leftJoin('#__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)')
			->leftJoin('#__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id)')
			->leftJoin('#__bsms_teachers AS t ON (t.id = s.teacher_id)')
			->leftJoin('#__bsms_series as se ON (s.series_id = se.id)')
			->where('#__bsms_mediafiles.id = ' . (int) $id)->where('#__bsms_mediafiles.published = ' . 1)
			->order('ordering asc, #__bsms_mediafiles.mime_type asc');
		$db->setQuery($query);
		$media = $db->loadObject();

		if ($media)
		{
			return $media;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Set up Player Attributes
	 *
	 * @param   object $admin_params  Admin params
	 * @param   object $params        System params
	 * @param   object $itemparams    Itam Params //@todo Thinking this could be munged into the $params
	 * @param   object $media         Media info
	 *
	 * @return object
	 */
	public function getPlayerAttributes($admin_params, $params, $itemparams, $media)
	{
		$player               = new stdClass;
		$player->playerwidth  = $params->get('player_width');
		$player->playerheight = $params->get('player_height');

		if ($itemparams->get('playerheight'))
		{
			$player->playerheight = $itemparams->get('playerheight');
		}
		if ($itemparams->get('playerwidth'))
		{
			$player->playerwidth = $itemparams->get('playerwidth');
		}

		/**
		 * @desc Players - from Template:
		 *       First we check to see if in the template the user has set to use the internal player for all media. This can be overridden by itemparams
		 *       popuptype = whether AVR should be window or lightbox (handled in avr code)
		 *       internal_popup = whether direct or internal player should be popup/new window or inline
		 *       From media file:
		 *       player 0 = direct, 1 = internal, 2 = AVR, 3 = AV 7 = legacy internal player (from JBS 6.2.2)
		 *       internal_popup 0 = inline, 1 = popup, 2 = global settings
		 *
		 * Get the $player->player: 0 = direct, 1 = internal, 2 = AVR (no longer supported),
		 *      3 = All Videos or JPlayer, 4 = Docman, 5 = article, 6 = Virtuemart, 7 = legacy player, 8 = embed code
		 * $player->type 0 = inline, 1 = popup/new window 3 = Use Global Settings (from params)
		 * In 6.2.3 we changed inline = 2
		 */
		$player->player     = 0;
		$params_mediaplayer = $params->get('media_player');
		$item_mediaplayer   = $media->player;

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

			$player->player = ($media->player) ? $media->player : "0";
		}
		if ($player->player == 3)
		{
			$player->player = 2;
		}

		if ($media->docMan_id > 0)
		{
			$player->player = 4;
		}
		if ($media->article_id > 0)
		{
			$player->player = 5;
		}
		if ($media->virtueMart_id > 0)
		{
			$player->player = 6;
		}

		$player->type = 1;

		// This is the global parameter set in Template Display settings
		$param_playertype = $params->get('internal_popup');

		if (!$param_playertype)
		{
			$param_playertype = 1;
		}
		$item_playertype = $media->popup;

		if ($param_playertype)
		{
			$player->type = $param_playertype;
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
	 * Return Docman Media
	 *
	 * @param   object $media  Media
	 * @param   object $image  Image
	 *
	 * @return string
	 * FIXME ASAP getDuration is not working.
	 */
	public function getDocman($media, $image)
	{
		$src          = JURI::base() . $image->path;
		$height       = $image->height;
		$width        = $image->width;
		$JBSMElements = new JBSMElements;
		$filesize     = $JBSMElements->getFilesize($media->size);
		$docman       = '<a href="index.php?option=com_docman&amp;task=doc_download&amp;gid=' . $media->docMan_id . '"
		 title="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src
			. '" alt="' . $media->malttext . ' ' . $filesize . '" width="' . $width
			. '" height="' . $height . '" border="0" /></a>';


		return $docman;
	}

	/**
	 * Return Articles.
	 *
	 * @param   object $media  Media
	 * @param   object $image  Image
	 *
	 * @return string
	 */
	public function getArticle($media, $image)
	{

		$src     = JURI::base() . $image->path;
		$height  = $image->height;
		$width   = $image->width;
		$article = '<a href="index.php?option=com_content&amp;view=article&amp;id=' . $media->article_id . '"
		 alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
			. '" height="' . $height . '" border="0" /></a>';

		return $article;
	}

	/**
	 * Set up Virtumart if Vertumart is installed.
	 *
	 * @param   object $media   Media
	 * @param   object $params  Item Params
	 * @param   object $image   Image
	 *
	 * @return string
	 */
	public function getVirtuemart($media, $params, $image)
	{
		$src    = JURI::base() . $image->path;
		$height = $image->height;
		$width  = $image->width;
		$vm     = '<a href="index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' . $media->virtueMart_id . '"
		alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
			. '" height="' . $height . '" border="0" /></a>';

		return $vm;
	}

	/**
	 * Setup Player Code.
	 *
	 * @param   object $params      System Params
	 * @param   object $itemparams  Item Params //@todo need to merge with $params
	 * @param   object $player      Player code
	 * @param   object $image       Image info
	 * @param   object $media       Media
	 *
	 * @return string
	 */
	public function getPlayerCode($params, $itemparams, $player, $image, $media)
	{
		$input        = new JInput;
		$src          = JURI::base() . $image->path;
		$height       = $image->height;
		$width        = $image->width;
		$backcolor    = $params->get('backcolor', '0x287585');
		$frontcolor   = $params->get('frontcolor', '0xFFFFFF');
		$lightcolor   = $params->get('lightcolor', '0x000000');
		$screencolor  = $params->get('screencolor', '0xFFFFFF');
		$template     = $input->get('t', '1', 'int');
		$JBSMElements = new JBSMElements;

		// Here we get more information about the particular media file
		$filesize = $JBSMElements->getFilesize($media->size);
		/**
		 * @todo There is no $row referenced to this function so this will fail
		 */

		// This one IS needed
		$duration = $JBSMElements->getDuration($params, $media);

		$mimetype = $media->mimetext;
		$path     = $media->spath . $media->fpath . $media->filename;

		if (!isset($media->malttext))
		{
			$media->malttext = '';
		}
		if (!substr_count($path, '://'))
		{
			$protocol = $params->get('protocol', '//');
			$path     = $protocol . $path;
		}
		switch ($player->player)
		{

			case 0: // Direct
				switch ($player->type)
				{

					case 2: // New window

						$playercode = '<a href="' . $path . '" onclick="window.open(\'index.php?option=com_biblestudy&amp;view=popup&amp;close=1&amp;mediaid=' .
							$media->id . '\',\'newwindow\',\'width=100, height=100,menubar=no, status=no,location=no,toolbar=no,scrollbars=no\');
                                        return true;" title="' . $media->malttext . ' - ' . $media->comment . ' ' . $duration . ' '
							. $filesize . '" target="' .
							$media->special . '"><img src="' . $src . '" alt="' . $media->malttext . ' - ' . $media->comment . ' - ' . $duration .
							' ' . $filesize . '" width="' . $width . '" height="' . $height . '" border="0" /></a>';

						return $playercode;
						break;

					case 1: // Popup window
						$playercode = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;player=0&amp;view=popup&amp;t="
							. $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height=" .
							$player->playerheight . "'); return false\"><img src='" . $src . "' height='" . $height . "' border='0' width='" . $width .
							"' title='" . $mimetype . " " . $duration . " " . $filesize . "' alt='" . $media->malttext . "' /></a>";
						break;
				}

				/** @var $playercode string */

				return $playercode;
				break;

			case 1: // Internal
				switch ($player->type)
				{
					case 2: // Inline
						$base = JUri::base();
						JHtml::script(JURI::base() . 'media/com_biblestudy/player/key.js');
						$playercode = "<div id='placeholder'><a href='//www.adobe.com/go/getflashplayer'>"
							. JText::_('Get flash') . "</a> " . JText::_('to see this player') . "</div>
									<script language=\"javascript\" type=\"text/javascript\">
									    jwplayer('placeholder').setup({
										    'file' : '" . $path . "',
										    'height' : '" . $player->playerheight . "',
										    'width' : '" . $player->playerwidth . "',
									        'image':'" . $params->get('popupimage', 'media/com_biblestudy/images/speaker24.png') . "',
									        'flashplayer':'" . $base . "media/com_biblestudy/player/jwplayer.flash.swf',
									        'backcolor':'" . $backcolor . "',
									        'frontcolor':'" . $frontcolor . "',
									        'lightcolor':'" . $lightcolor . "',
									        'screencolor':'" . $screencolor . "',
									    });
									</script>";
						break;

					case 1: // Popup


						// Add space for popup window
						$player->playerwidth  = $player->playerwidth + 20;
						$player->playerheight = $player->playerheight + $params->get('popupmargin', '50');

						$playercode = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;player=1&amp;view=popup&amp;t="
							. $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow', 'width=" . $player->playerwidth . ",height=" .
							$player->playerheight . "'); return false\"><img src='" . $src . "' height='" . $height . "' width='" . $width .
							"' title='" . $mimetype . " " . $duration . " " . $filesize . "' border='0' alt='" . $media->malttext . "'></a>";
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
							. $player->playerheight . "'); return false\"> <img src='" . $src . "' height='" . $height . "' width='"
							. $width . "' border='0' title='" . $mimetype . " " . $duration . " " . $filesize .
							"' alt='" . $media->malttext . "' /></a>";
						break;

					case 2: // This plays the video inline
						$mediacode  = $this->getAVmediacode($media->mediacode, $media);
						$playercode = JHTML::_('content.prepare', $mediacode);
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
				$playercode = $this->getVirtuemart($media, $params, $image);

				return $playercode;
				break;

			case 7: // Legacy internal player
				switch ($player->type)
				{
					case 2:
						$playercode = '<script type="text/javascript" src="' . JURI::base() . 'media/com_biblestudy/legacyplayer/audio-player.js"></script>
        		            <object type="application/x-shockwave-flash" data="' . JURI::base()
							. 'media/com_biblestudy/legacyplayer/player.swf" id="audioplayer' . $media->id
							. '" border="0" height="24" width="' . $player->playerwidth . '">
				                <param name="movie" value="' . JURI::base() . 'media/com_biblestudy/legacyplayer/player.swf" />
				                <param name="FlashVars" value="playerID=' . $media->id . '&amp;soundFile=' . $path . '" />
				                <param name="quality" value="high" />
				                <param name="menu" value="false" />
				                <param name="wmode" value="transparent" />
				                </object>
				                ';

						return $playercode;
						break;


					case 1:
						$playercode = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;view=popup&amp;player=7&amp;t=" . $template .
							"&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height=" . $player->playerheight
							. "'); return false\"> <img src='" . $src . "' border='0' height='" . $height . "' width='" . $width . "' title='" . $mimetype
							. " " . $duration . " " . $filesize . "' alt='' /></a>";

						return $playercode;
						break;
				}
				break;

			case 8: // Embed code

				$playercode = "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;view=popup&amp;player=8&amp;t=" . $template .
					"&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height="
					. $player->playerheight . "'); return false\"> <img src='" . $src . "' height='" . $height . "' width='" . $width . "' border='0' title='"
					. $mimetype . " " . $duration . " " . $filesize . "' alt='" . $src . "'></a>";

				return $playercode;
				break;
		}

		return false;
	}

	/**
	 * Return AVMedia Code.
	 *
	 * @param   string $mediacode  Media stirng
	 * @param   object $media      Media info
	 *
	 * @return string
	 */
	public function getAVmediacode($mediacode, $media)
	{
		$bracketpos   = strpos($mediacode, '}');
		$bracketend   = strpos($mediacode, '{', $bracketpos);
		$dashposition = strpos($mediacode, '-', $bracketpos);
		$isonlydash   = substr_count($mediacode, '}-{');

		if ($isonlydash)
		{
			$mediacode = substr_replace($mediacode, '//' . $media->spath . $media->fpath . $media->filename, $dashposition, 1);
		}
		elseif ($dashposition)
		{
			$mediacode = substr_replace($mediacode, $media->spath . $media->fpath . $media->filename, $bracketend - 1, 1);
		}

		return $mediacode;
	}

	/**
	 * Update Hit count for playes.
	 *
	 * @param   int $id  ID to apply the hit to.
	 *
	 * @return boolean
	 */
	public function hitPlay($id)
	{
		$db    = JFactory::getDBO();
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

}

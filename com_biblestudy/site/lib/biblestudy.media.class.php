<?php

/**
 * Joomla! Bible Study Media class.
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.admin.class.php');
jimport('joomla.html.parameter');

/**
 * Joomla! Bible Study Media class.
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class jbsMedia {

    /**
     * Return Media Table
     * @param string $row
     * @param string $params
     * @param string $admin_params
     * @return null|string
     */
    function getMediaTable($row, $params, $admin_params) {
        //First we get some items from GET and instantiate the images class

        $template = JRequest::getInt('t', '1', 'get');
        $images = new jbsImages();
        $path1 = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        include_once ($path1 . 'helper.php');

        //Here we get the administration row from the comnponent, and determine the download image to use

        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__bsms_admin');
        $admin = $db->loadObject();
        $registry = new JRegistry;
        $registry->loadJSON($admin->params);
        $admin->params = $registry->toArray();
        if (isset($admin->params['default_download_image'])) {
            $admin_d_image = $admin->params['default_download_image'];
        } else {
            $admin_d_image = null;
        }
        $d_image = ($admin_d_image ? $admin_d_image : 'download.png' );

        $download_tmp = $images->getMediaImage($d_image, $media = NULL);
        $download_image = $download_tmp->path;
        $compat_mode = $admin_params->get('compat_mode');

        //Here we get a list of the media ids associated with the study we got from $row

        $mediaids = $this->getMediaRows($row->id);
        if (!$mediaids) {
            $table = null;
            return $table;
        }
        $rowcount = count($mediaids);
        if ($rowcount < 1) {
            $table = null;
            return $table;
        }

        //Here is where we begin to build the table
        $table = '<div><table class="mediatable"><tbody><tr>';

        //Now we look at each mediaid, and get the rest of the media information
        foreach ($mediaids AS $media) {
            //Step 1 is to get the media file
            $image = $images->getMediaImage($media->impath, $media->path2);
            // Convert parameter fields to objects.
            $registry = new JRegistry;
            $registry->loadJSON($media->params);
            $itemparams = $registry;
            //Get the attributes for the player used in this item
            $player = $this->getPlayerAttributes($admin_params, $params, $itemparams, $media);
            $playercode = $this->getPlayerCode($params, $itemparams, $player, $image, $media);

            //Now we build the column for each media file
            $table .= '<td>';


            //Check to see if a download link is needed

            $link_type = $media->link_type;

            if ($link_type > 0) {

                $width = $download_tmp->width;
                $height = $download_tmp->height;

                if ($compat_mode == 0) {
                    $downloadlink = '<a href="index.php?option=com_biblestudy&amp;mid=' .
                            $media->id . '&amp;view=sermons&amp;task=download">';
                } else {
                    $downloadlink = '<a href="http://joomlabiblestudy.org/router.php?file=' .
                            $media->spath . $media->fpath . $media->filename . '&amp;size=' . $media->size . '">';
                }
                //Check to see if they want to use a popu
                if ($params->get('useterms') > 0) {

                    $downloadlink = '<a class="modal" href="index.php?option=com_biblestudy&amp;view=terms&amp;tmpl=component&amp;layout=modal&amp;compat_mode=' . $compat_mode . '&amp;mid=' . $media->id . '&amp;t=' . $template . '" rel="{handler: \'iframe\', size: {x: 640, y: 480}}">';
                }
                $downloadlink .= '<img src="' . $download_image . '" alt="' . JText::_('JBS_MED_DOWNLOAD') . '" height="' .
                        $height . '" width="' . $width . '" border="0" title="' . JText::_('JBS_MED_DOWNLOAD') . '" /></a>';
            }
            switch ($link_type) {
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
            //End of the column holding the media image
            $table .= '</td>';
        } // end of foreach mediaids
        //End of row holding media image/link
        $table .= '</tr>';

        // This is the last part of the table where we see if we need to display the filesize
        if ($params->get('show_filesize') > 0 && isset($media)) {
            $table .= '<tr>';
            foreach ($mediaids as $media) {
                switch ($params->get('show_filesize')) {
                    case 1:
                        $filesize = getFilesize($media->size);
                        break;
                    case 2:
                        $filesize = $media->comment;
                        break;
                    case 3:
                        if ($media->comment ? $filesize = $media->comment : $filesize = getFilesize($media->size))
                            ;
                        break;
                }

                $table .= '<td><span class="bsfilesize">' . $filesize . '</span></td>';
            } //end second foreach
            $table .= '</tr>';
        } // end of if show_filesize


        $table .='</tbody></table></div>';

        return $table;
    }

    /**
     * Get Media ID
     * @param int $id
     * @return object
     */
    function getMediaid($id) {
        $db = JFactory::getDBO();
        $query = 'SELECT m.id as mid, m.study_id, s.id as sid FROM #__bsms_mediafiles AS m
         LEFT JOIN #__bsms_studies AS s ON (m.study_id = s.id) WHERE s.id = ' . $id;
        $db->setQuery($query);
        $mediaids = $db->loadObjectList();
        return $mediaids;
    }

    /**
     * Get Media info Row1
     * @param type $id
     * @return boolean
     */
    function getMediaRows($id) {
        if (!$id) {
            return false;
        }
        $db = JFactory::getDBO();
        $query = 'SELECT #__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath, #__bsms_folders.id AS fid,'
                . ' #__bsms_folders.folderpath AS fpath, #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname,'
                . ' #__bsms_media.path2 AS path2, s.studytitle, s.studydate, s.studyintro, s.media_hours, s.media_minutes, s.media_seconds, s.teacher_id,'
                . ' s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin, s.verse_end, t.teachername, t.id as tid, s.id as sid, s.studyintro,'
                . ' #__bsms_media.media_alttext AS malttext, #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext, #__bsms_mimetype.mimetype FROM #__bsms_mediafiles'
                . ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image) LEFT JOIN #__bsms_servers'
                . ' ON (#__bsms_servers.id = #__bsms_mediafiles.server) LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
                . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type) LEFT JOIN #__bsms_studies AS s'
                . ' ON (s.id = #__bsms_mediafiles.study_id) LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
                . ' WHERE #__bsms_mediafiles.study_id = ' . $id . ' AND #__bsms_mediafiles.published = 1 ORDER BY ordering ASC, #__bsms_media.media_image_name ASC';
        $db->setQuery($query);
        if ($media = $db->loadObjectList()) {
            return $media;
        } else {
            $error = $db->getErrorMsg();
            return false;
        }
    }

    /**
     * Get Media info Row2
     * @param int $id
     * @return boolean
     */
    function getMediaRows2($id) {
        //We use this for the popup view because it relies on the media file's id rather than the study_id field above
        $db = JFactory::getDBO();
        $query = 'SELECT #__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath, #__bsms_folders.id AS fid,'
                . ' #__bsms_folders.folderpath AS fpath, #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath,'
                . ' #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2, s.studyintro, s.media_hours, s.media_minutes, s.series_id,'
                . ' s.media_seconds, s.studytitle, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin,'
                . ' s.verse_end, t.teachername, t.teacher_thumbnail, t.teacher_image, t.thumb, t.image, t.id as tid, s.id as sid, s.studyintro,  #__bsms_media.media_alttext AS malttext,'
                . ' se.id as seriesid, se.series_text, se.series_thumbnail,'
                . ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext, #__bsms_mimetype.mimetype FROM #__bsms_mediafiles'
                . ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
                . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
                . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
                . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
                . ' LEFT JOIN #__bsms_studies AS s ON (s.id = #__bsms_mediafiles.study_id)'
                . ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
                . ' LEFT JOIN #__bsms_series as se ON (s.series_id = se.id)'
                . ' WHERE #__bsms_mediafiles.id = ' . (int) $id . ' AND #__bsms_mediafiles.published = 1'
                . ' ORDER BY ordering ASC, #__bsms_mediafiles.mime_type ASC';
        $db->setQuery($query);
        if ($media = $db->loadObject()) {
            return $media;
        } else {
            $error = $db->getErrorMsg();
            return false;
        }
    }

    /**
     * Return Admin DB
     * @return object
     */
    function getAdmin() {
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__bsms_admin WHERE id = 1');
        //$db->query();
        $admin = $db->loadObjectList();
        return $admin;
    }

    /**
     * Set up Player Attributes
     * @param type $admin_params
     * @param type $params
     * @param type $itemparams
     * @param type $media
     * @return string
     */
    function getPlayerAttributes($admin_params, $params, $itemparams, $media) {
        $player = new stdClass();
        $player->playerwidth = $params->get('player_width');
        $player->playerheight = $params->get('player_height');

        if ($itemparams->get('playerheight')) {
            $player->playerheight = $itemparams->get('playerheight');
        }
        if ($itemparams->get('playerwidth')) {
            $player->playerwidth = $itemparams->get('playerwidth');
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
         * Get the $player->player: 0 = direct, 1 = internal, 2 = AVR (no longer supported), 3 = All Videos or JPlayer, 4 = Docman, 5 = article, 6 = Virtuemart, 7 = legacy player, 8 = embed code
         * $player->type 0 = inline, 1 = popup/new window 3 = Use Global Settings (from params)
         * In 6.2.3 we changed inline = 2
         */
        $player->player = 0;
        $params_mediaplayer = $params->get('media_player');
        $item_mediaplayer = $media->player;

        //check to see if the item player is set to 100 - that means use global settings which comes from $params
        if ($item_mediaplayer == 100) {
            //Player is set from the $params
            $player->player = $params->get('media_player', '0');
        } else {
            //In this case the item has a player set for it, so we use that instead. We also need to change the old player type of 3 to 2 for all videos reloaded which we dont support

            $player->player = ($media->player) ? $media->player : "0";
        }
        if ($player->player == 3) {
            $player->player = 2;
        }

        if ($media->docMan_id > 0) {
            $player->player = 4;
        }
        if ($media->article_id > 0) {
            $player->player = 5;
        }
        if ($media->virtueMart_id > 0) {
            $player->player = 6;
        }

        $player->type = 1;
        //This is the global parameter set in Template Display settings
        $param_playertype = $params->get('internal_popup');
        if (!$param_playertype) {
            $param_playertype = 1;
        }
        $item_playertype = $media->popup;
        if ($param_playertype) {
            $player->type = $param_playertype;
        }

        switch ($item_playertype) {
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
     * @param type $media
     * @param type $image
     * @return string
     */
    function getDocman($media, $image) {
        $src = JURI::base() . $image->path;
        $height = $image->height;
        $width = $image->width;
        $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        include_once($path1 . 'filesize.php');
        include_once($path1 . 'duration.php');
        $filesize = getFilesize($media->size);
        $duration = getDuration($params, $row);
        $docman = '<a href="index.php?option=com_docman&amp;task=doc_download&amp;gid=' . $media->docMan_id . '"
		 title="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src
                . '" alt="' . $media->malttext . ' ' . $duration . ' ' . $filesize . '" width="' . $width
                . '" height="' . $height . '" border="0" /></a>';


        return $docman;
    }

    /**
     * Return Articles.
     * @param type $media
     * @param type $image
     * @return string
     */
    function getArticle($media, $image) {

        $src = JURI::base() . $image->path;
        $height = $image->height;
        $width = $image->width;
        $article = '<a href="index.php?option=com_content&amp;view=article&amp;id=' . $media->article_id . '"
		 alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
                . '" height="' . $height . '" border="0" /></a>';

        return $article;
    }

    /**
     * Set up Virtumart if Vertumart is installed.
     * @param type $media
     * @param type $params
     * @param type $image
     * @return string
     */
    function getVirtuemart($media, $params, $image) {
        $src = JURI::base() . $image->path;
        $height = $image->height;
        $width = $image->width;
        $vm = '<a href="index.php?option=com_virtuemart&amp;view=productdetails&amp;virtuemart_product_id=' . $media->virtueMart_id . '"
		alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
                . '" height="' . $height . '" border="0" /></a>';

        return $vm;
    }

    /**
     * Setup Player Code.
     * @param type $params
     * @param type $itemparams
     * @param type $player
     * @param type $image
     * @param type $media
     * @return string
     */
    function getPlayerCode($params, $itemparams, $player, $image, $media) {
        $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
        include_once($path1 . 'filesize.php');
        include_once($path1 . 'duration.php');
        $src = JURI::base() . $image->path;
        $height = $image->height;
        $width = $image->width;
        $backcolor = $params->get('backcolor', '0x287585');
        $frontcolor = $params->get('frontcolor', '0xFFFFFF');
        $lightcolor = $params->get('lightcolor', '0x000000');
        $screencolor = $params->get('screencolor', '0xFFFFFF');
        $template = JRequest::getInt('t', '1', 'get');
        //Here we get more information about the particular media file
        $filesize = getFilesize($media->size);
        /**
         * @todo There is no $row referenced to this function so this will fail
         */
        //$duration = getDuration($params, $row); //This one IS needed
        $duration = '';
        $mimetype = $media->mimetext;
        $path = $media->spath . $media->fpath . $media->filename;
        if (!isset($media->malttext))
            $media->malttext = '';
        if (!substr_count($path, '://')) {
            $protocol = $params->get('protocol', 'http://');
            $path = $protocol . $path;
        }
        switch ($player->player) {
            case 0: //Direct
                switch ($player->type) {
                    case 2: //new window
                        $playercode =
                                '<a href="' . $path . '" onclick="window.open(\'index.php?option=com_biblestudy&amp;view=popup&amp;close=1&amp;mediaid=' .
                                $media->id . '\',\'newwindow\',\'width=100, height=100,menubar=no, status=no,location=no,toolbar=no,scrollbars=no\');
                     return true;" title="' . $media->malttext . ' - ' . $media->comment . ' ' . $duration . ' ' . $filesize . '" target="' .
                                $media->special . '"><img src="' . $src . '" alt="' . $media->malttext . ' - ' . $media->comment . ' - ' . $duration .
                                ' ' . $filesize . '" width="' . $width . '" height="' . $height . '" border="0" /></a>';
                        return $playercode;
                        break;

                    case 1: //Popup window

                        $playercode =
                                "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;player=0&amp;view=popup&amp;t=" . $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height=" .
                                $player->playerheight . "'); return false\"><img src='" . $src . "' height='" . $height . "' border='0' width='" . $width .
                                "' title='" . $mimetype . " " . $duration . " " . $filesize . "' alt='" . $media->malttext . "' /></a>";
                        break;
                }
                return $playercode;
                break;

            case 1: //Internal
                switch ($player->type) {
                    case 2: //Inline
                        $playercode = "<video height='" . $player->playerheight . "' poster='" . JURI::base() . $params->get('popupimage', 'media/com_biblestudy/images/speaker24.png') . "'
                        width='" . $player->playerwidth . "' id='placeholder'> <source src='" . $path . "' /><a href='http://www.adobe.com/go/getflashplayer'>" . JText::_('Get flash') . "</a> " . JText::_('to see this player') . "</video>
			<script type='text/javascript'>
			jwplayer('placeholder').setup({
                                flashplayer: '" . JURI::base() . "media/com_biblestudy/player/player.swf',
                            });
			</script>";
                        break;

                    case 1: //popup
                        // Add space for popup window
                        $player->playerwidth = $player->playerwidth + 20;
                        $player->playerheight = $player->playerheight + $params->get('popupmargin', '50');

                        $playercode =
                                "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;player=1&amp;view=popup&amp;t=" . $template . "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height=" .
                                $player->playerheight . "'); return false\"><img src='" . $src . "' height='" . $height . "' width='" . $width .
                                "' title='" . $mimetype . " " . $duration . " " . $filesize . "' border='0' alt='" . $media->malttext . "'></a>";
                        break;
                }
                return $playercode;
                break;

            case 2: //All Videos Reloaded
            case 3:
                switch ($player->type) {
                    case 1: //This goes to the popup view
                        $playercode =
                                "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;view=popup&amp;player=3&amp;t=" . $template .
                                "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height=" . $player->playerheight . "'); return false\">
                                <img src='" . $src . "' height='" . $height . "' width='" . $width . "' border='0' title='" . $mimetype . " " . $duration . " " . $filesize .
                                "' alt='" . $media->malttext . "' /></a>";
                        break;

                    case 2: // This plays the video inline
                        $mediacode = $this->getAVmediacode($media->mediacode);
                        $playercode = JHTML::_('content.prepare', $mediacode);
                        break;
                }
                return $playercode;
                break;

            case 4: //Docman
                $playercode = $this->getDocman($media, $image);
                return $playercode;
                break;

            case 5: //article
                $playercode = $this->getArticle($media, $image);
                return $playercode;
                break;

            case 6: //Virtuemart
                $playercode = $this->getVirtuemart($media, $params, $image);
                return $playercode;
                break;

            case 7: //Legacy internal player
                switch ($player->type) {
                    case 2:
                        $playercode = '<script type="text/javascript" src="' . JURI::base() . 'media/com_biblestudy/legacyplayer/audio-player.js"></script>
        		<object type="application/x-shockwave-flash" data="' . JURI::base() . 'media/com_biblestudy/legacyplayer/player.swf" id="audioplayer' . $media->id . '" border="0" height="24" width="' . $player->playerwidth . '">
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
                        $playercode =
                                "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;view=popup&amp;player=7&amp;t=" . $template .
                                "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height=" . $player->playerheight . "'); return false\">
                                <img src='" . $src . "' border='0' height='" . $height . "' width='" . $width . "' title='" . $mimetype . " " . $duration . " " . $filesize .
                                "' alt='' /></a>";
                        return $playercode;
                        break;
                }
            case 8: //Embed code


                $playercode =
                        "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&amp;view=popup&amp;player=8&amp;t=" . $template .
                        "&amp;mediaid=" . $media->id . "&amp;tmpl=component', 'newwindow','width=" . $player->playerwidth . ",height=" . $player->playerheight . "'); return false\">
                        <img src='" . $src . "' height='" . $height . "' width='" . $width . "' border='0' title='" . $mimetype . " " . $duration . " " . $filesize .
                        "' alt='" . $src . "'></a>";

                return $playercode;
                break;
        }
    }

    /**
     * Return AVMedia Code.
     * @param type $mediacode
     * @param type $media
     * @return sting
     */
    function getAVmediacode($mediacode, $media) {
        $bracketpos = strpos($mediacode, '}');
        $bracketend = strpos($mediacode, '{', $bracketpos);
        $dashposition = strpos($mediacode, '-', $bracketpos);
        $isonlydash = substr_count($mediacode, '}-{');
        if ($isonlydash) {
            $mediacode = substr_replace($mediacode, 'http://' . $media->spath . $media->fpath . $media->filename, $dashposition, 1);
        } elseif ($dashposition) {
            $mediacode = substr_replace($mediacode, $media->spath . $media->fpath . $media->filename, $bracketend - 1, 1);
        }
        return $mediacode;
    }

    /**
     * Update Hit count for playes.
     * @param type $id
     * @return boolean
     */
    function hitPlay($id) {
        $db = JFactory::getDBO();
        $query = 'UPDATE #__bsms_mediafiles SET plays = plays + 1 WHERE id = ' . $id;
        $db->setQuery('UPDATE ' . $db->nameQuote('#__bsms_mediafiles') . 'SET ' . $db->nameQuote('plays') . ' = ' . $db->nameQuote('plays') . ' + 1 ' . ' 	WHERE id = ' . $id);
        $db->query();
        return true;
    }

}

// End of class

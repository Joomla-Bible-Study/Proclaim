<?php

/**
 * @version $Id: mediatable.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');

function getMediatable($params, $row, $admin_params) {
    jimport('joomla.html.parameter');
    $getMedia = new jbsMedia();
    jimport('joomla.application.component.helper');
    if (!$row->id) {
        return FALSE;
    }
    $mainframe = & JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $database = & JFactory::getDBO();
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'filesize.php');
    include_once($path1 . 'filepath.php');
    include_once($path1 . 'duration.php');
    include_once($path1 . 'image.php');
    include_once ($path1 . 'helper.php');
    $database->setQuery('SELECT * FROM #__bsms_admin WHERE id = 1');
    $database->query();
    $admin = $database->loadObjectList();

    $d_image = ($admin[0]->params->default_download_image ? '/' . $admin[0]->params->default_download_image : '/download.png');

    $images = new jbsImages();
    $download_tmp = $images->getMediaImage($admin[0]->params->default_download_image, $media = NULL);

    $download_image = $download_tmp->path;


    $query = 'SELECT #__bsms_mediafiles.*,'
            . ' #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath,'
            . ' #__bsms_folders.id AS fid, #__bsms_folders.folderpath AS fpath,'
            . ' #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname, #__bsms_media.path2 AS path2,'
            . ' #__bsms_media.media_alttext AS malttext,'
            . ' #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext'
            . ' FROM #__bsms_mediafiles'
            . ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image)'
            . ' LEFT JOIN #__bsms_servers ON (#__bsms_servers.id = #__bsms_mediafiles.server)'
            . ' LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
            . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type)'
            . ' WHERE #__bsms_mediafiles.study_id = ' . $row->id . ' AND #__bsms_mediafiles.published = 1 ORDER BY ordering ASC, #__bsms_mediafiles.mime_type ASC';
    $database->setQuery($query);
    $media1 = $database->loadObjectList('id');
    $rows2 = count($media1);


    $compat_mode = $admin_params->get('compat_mode');
    if ($rows2 < 1) {
        $mediatable = null;
        return $mediatable;
    }


    $mediatable = '<div><table class="mediatable"><tbody><tr>';
    $row_count = 0;

    foreach ($media1 as $media) {

        $row_count = $row_count + 1;
        //Load the parameters
        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($media->params);
        $itemparams = $registry;

        $Itemid = JRequest::getInt('Itemid', '1', 'get');
        $template = JRequest::getInt('t', '1', 'get');
        $images = new jbsImages();
        $image = $images->getMediaImage($media->path2, $media->impath);


        $mediatable .= '<td>';


        //todo - not sure how much of this is needed
        $idfield = '#__bsms_mediafiles.id';
        $filesize = getFilesize($media->size);
        $duration = getDuration($params, $row); //This one IS needed
        $mimetype = $media->mimetext;
        $src = JURI::base() . $image->path;
        $height = $image->height;
        $width = $image->width;
        $ispath = 0;
        $mime = '';
        $path1 = $media->spath . $media->fpath . $media->filename;
        if (!eregi('http://', $path1)) {
            $path1 = 'http://' . $path1;
        }
        $playerwidth = $params->get('player_width');
        $playerheight = $params->get('player_height');
        if ($itemparams->get('playerheight')) {
            $playerheight = $itemparams->get('playerheight');
        }
        if ($itemparams->get('playerwidth')) {
            $playerwidth = $itemparams->get('playerwidth');
        }
        $playerwidth = $playerwidth + 20;
        $playerheight = $playerheight + $params->get('popupmargin', '50');

        // Players - from Template:
        // media_player = internal player for all files
        // useravr = use avr for all files
        // useav = use All Videos plugin for all files
        // popuptype = whether AVR should be window or lightbox (handled in avr code)
        // media_player = use internal player for all files
        // internal_popup = whether direct or internal player should be popup or inline
        // From media file:
        // player 0 = direct, 1 = internal, 2 = AVR, 3 = AV
        // internal_popup 0 = inline 1 = popup, 2 = global settings

        $playertype = 0;
        if ($params->get('media_player') == 1 || $itemparams->get('player') == 1) {
            $playertype = 1;
        }
        if ($params->get('useavr') == 1 || $itemparams->get('player') == 2) {
            $playertype = 2;
        }
        if ($params->get('useav') == 1 || $itemparams->get('player') == 3) {
            $playertype = 3;
        }
//$item comes from the individual media file 0 = inline, 1 = popup, 3 = use global settings
        $item = $itemparams->get('internal_popup');
        $internal_popup = $params->get('internal_popup', 0);

        if ($item < 3) {
            $type = $internal_popup;
        } else {
            $type = $item;
        }
        switch ($playertype) {
            case 0:

                if ($params->get('direct_internal', 0) == 1) {
                    $media1_link =
                            "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&view=popup&Itemid=" . $Itemid . "&template=" . $template . "&mediaid=" . $media->id . "', 'newwindow','width=" . $playerwidth . ",height=" . $playerheight . "'); return false\"\"><img src='" . $src . "' height='" . $height . "' width='" . $width . "' title='" . $mimetype . " " . $duration . " " . $filesize . "' alt='" . $src . "'></a>";


                    if ($type == 0) {
                        $media1_link = $getMedia->getInternalLink($media, $width, $height, $src, $params, $image, $row_count, $path1);
                    }
                } else {
                    $media1_link = '<a href="' . $path1 . '" title="' . $media->malttext . ' - ' . $media->comment . ' ' . $duration . ' '
                            . $filesize . '" target="' . $media->special . '"><img src="' . $src
                            . '" alt="' . $media->malttext . ' - ' . $media->comment . ' - ' . $duration . ' ' . $filesize . '" width="' . $width
                            . '" height="' . $height . '" border="0" /></a>';
                }

                $media1_link .= '<a href="' . $path1 . '" onclick="window.open(\'index.php?option=com_biblestudy&view=popup&close=1&mediaid=' . $media->id . '\',\'newwindow\',\'width=100, height=100,menubar=no, status=no,location=no,toolbar=no,scrollbars=no\'); return false;" title="' . $media->malttext . ' - ' . $media->comment . ' ' . $duration . ' ' . $filesize . '" target="' . $media->special . '"><img src="' . $src . '" alt="' . $media->malttext . ' - ' . $media->comment . ' - ' . $duration . ' ' . $filesize . '" width="' . $width . '" height="' . $height . '" border="0" /></a>';

                break;

            case 1:

                if ($type == 1) {
                    $media1_link =
                            "<a href=\"#\" onclick=\"window.open('index.php?option=com_biblestudy&player=2&view=popup&Itemid=" . $Itemid . "&template=" . $template . "&mediaid=" . $media->id . "&tmpl=component', 'newwindow','width=" . $playerwidth . ",height=" . $playerheight . "'); return false\"\"><img src='" . $src . "' height='" . $height . "' width='" . $width . "' title='" . $mimetype . " " . $duration . " " . $filesize . "' alt='" . $src . "'></a>";
                } else {
                    $media1_link = $getMedia->getInternalLink($media, $width, $height, $src, $params, $image, $row_count, $path1);
                }

                break;

            case 2:
                $media1_link = $getMedia->getAVRLink($media, $width, $height, $src, $params, $image, $Itemid);
                break;

            case 3:
                echo '<div>' . JHTML::_('content.prepare', $media->mediacode) . '</div>';
                break;
        }


        if ($media->docMan_id > 0) {
            $media1_link = getDocman($media, $width, $height, $src, $duration, $filesize);
        }
        if ($media->article_id > 0) {
            $media1_link = getArticle($media, $width, $height, $src);
        }
        if ($media->virtueMart_id > 0) {
            $media1_link = getVirtuemart($media, $width, $height, $src, $params);
        }

        // Here is where we begin to build the mediatable variable
        //Here we test to see if docMan or article is used

        $link_type = $media->link_type;


        if ($link_type > 0) {
            $width = $download_tmp->width;
            $height = $download_tmp->height;

            if ($compat_mode == 0) {
                $downloadlink = '<a href="index.php?option=com_biblestudy&id=' . $media->id . '&view=sermons&controller=sermons&task=download">';
            } else {
                $downloadlink = '<a href="http://joomlabiblestudy.org/router.php?file=' . $media->spath . $media->fpath . $media->filename . '&size=' . $media->size . '">';
            }
            $downloadlink .= '<img src="' . $download_image . '" alt="' . JText::_('JBS_MED_DOWNLOAD') . '" height="' . $height . '" width="' . $width . '" title="' . JText::_('JBS_MED_DOWNLOAD') . '" /></a>';
        }
        switch ($link_type) {
            case 0:
                $mediatable .= $media1_link;
                break;

            case 1:
                $mediatable .= $media1_link . $downloadlink;
                break;

            case 2:
                $mediatable = '<div><table class="mediatable"><tbody><tr><td>' . $downloadlink .'</td></tr></tbody></table></div>';
                break;
        }
        $mediatable .= '</td>';
    } //end of foreach of media results

    $mediatable .= '</tr>';

    if ($params->get('show_filesize') > 0) {
        $mediatable .= '<tr>';
        foreach ($media1 as $media) {
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

            $mediatable .= '<td><span class="bsfilesize">' . $filesize . '</span></td>';
        } //end second foreach
        $mediatable .= '</tr>';
    } // end of if show_filesize

    $mediatable .='</td></tr></tbody></table></div>';
    return $mediatable;
}

function getDocman($media, $width, $height, $src, $duration, $filesize) {
    $docman = '<a href="index.php?option=com_docman&task=doc_download&gid=' . $media->docMan_id . '"
		 title="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src
            . '" alt="' . $media->malttext . ' ' . $duration . ' ' . $filesize . '" width="' . $width
            . '" height="' . $height . '" border="0" /></a>';


    return $docman;
}

function getArticle($media, $width, $height, $src) {
    $article = '<a href="index.php?option=com_content&view=article&id=' . $media->article_id . '"
		 alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
            . '" height="' . $height . '" border="0" /></a>';

    return $article;
}

function getVirtuemart($media, $width, $height, $src, $params) {

    $vm = '<a href="index.php?option=com_virtuemart&page=shop.product_details&flypage=' . $params->get('store_page', 'flypage.tpl') . '&product_id=' . $media->virtueMart_id . '"
		alt="' . $media->malttext . ' - ' . $media->comment . '" target="' . $media->special . '"><img src="' . $src . '" width="' . $width
            . '" height="' . $height . '" border="0" /></a>';

    return $vm;
}

function getMediaRows($study_id) {
    $query = 'SELECT #_bsms_mediafiles.*,'
            . ' #_bsms_servers.id AS ssid, #_bsms_servers.server_path AS spath,'
            . ' #_bsms_folders.id AS fid, #_bsms_folders.folderpath AS fpath,'
            . ' #_bsms_media.id AS mid, #_bsms_media.media_image_path AS impath, #_bsms_media.media_image_name AS imname, #_bsms_media.path2 AS path2,'
            . ' #_bsms_media.media_alttext AS malttext,'
            . ' #_bsms_mimetype.id AS mtid, #_bsms_mimetype.mimetext'
            . ' FROM #_bsms_mediafiles'
            . ' LEFT JOIN #_bsms_media ON (#_bsms_media.id = #_bsms_mediafiles.media_image)'
            . ' LEFT JOIN #_bsms_servers ON (#_bsms_servers.id = #_bsms_mediafiles.server)'
            . ' LEFT JOIN #_bsms_folders ON (#_bsms_folders.id = #_bsms_mediafiles.path)'
            . ' LEFT JOIN #_bsms_mimetype ON (#_bsms_mimetype.id = #_bsms_mediafiles.mime_type)'
            . ' WHERE #_bsms_mediafiles.study_id = ' . $study_id . ' AND #_bsms_mediafiles.published = 1 ORDER BY ordering ASC, #_bsms_mediafiles.mime_type ASC;';

    $database = & JFactory::getDBO();
    $database->setQuery($query);
    $database->query();
    $mediaRows = $database->loadObjectList();
    return $mediaRows;
}
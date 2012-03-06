<?php

/*
 * Class to build page elements in use by custom template files
 * @since 7.0.1
 * @author Tom Fuller
 */
//No Direct Access
defined('_JEXEC') or die;
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'scripture.php');
    include_once($path1 . 'duration.php');
    include_once($path1 . 'date.php');
    include_once($path1 . 'filesize.php');
    include_once($path1 . 'image.php');
jimport('joomla.html.parameter');
class JBSPagebuilder
{
    
    function buildPage($item, $params, $admin_params)
    {
        $images = new jbsImages();
        //media files image, links, download
        $mids = $item->mids;
        $page->media = $this->mediaBuilder($mids, $params, $admin_params);
        //scripture1
        $esv = 0;
        $scripturerow = 1;
        $page->scripture1 = getScripture($params, $item, $esv, $scripturerow); 
        //scripture 2
        $esv = 0;
        $scripturerow = 2;
        $page->scripture2 = getScripture($params, $item, $esv, $scripturerow);
        //duration
        $page->duration = getDuration($params, $item);
        $page->studydate = getstudyDate($params, $item->studydate);
        if (substr_count($item->topics_text, ',')) 
                {
                $topics = explode(',', $item->topics_text);
                foreach ($topics as $key => $value) 
                    {
                    $topics[$key] = JText::_($value);
                    }
                $page->topics = implode(', ', $topics);
                } 
        else {$page->topics = JText::_($item->topics_text);}
        if ($item->thumbnailm)
        {
            $image = $images->getStudyThumbnail($item->thumbnailm);
            $page->study_thumbnail = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $item->studytitle . '">';
        }
        if ($item->series_thumbnail) 
            {
              $image = $images->getSeriesThumbnail($row->series_thumbnail);
              $page->series_thumbnail = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $item->series_text . '">';
            }
        $page->detailslink = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $item->slug . '&t=' . $params->get('detailstemplateid'));    
        return $page;
    }
    
    function mediaBuilder($mediaids, $params, $admin_params)
    {
        $images = new jbsImages();
        $mediaelements = new jbsMedia();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('media.*');
        $query->from('#__bsms_mediafiles as media');
        $query->select('server.id as serverid, server.server_path as spath');
        $query->join('LEFT','#__bsms_servers AS server ON server.id = media.server');
        $query->select('folder.folderpath as fpath');
        $query->join('LEFT','#__bsms_folders as folder ON folder.id = media.path');
        $query->select('image.media_image_path AS impath, image.media_image_name as imname, image.path2');
        $query->join('LEFT','#__bsms_media as image ON image.id = media.media_image');
        $query->select('study.media_hours, study.media_minutes, study.media_seconds');
        $query->join('LEFT','#__bsms_studies AS study ON study.id = media.study_id');
        $query->select('mime.mimetext');
        $query->join('LEFT','#__bsms_mimetype as mime ON mime.id = media.mime_type');
        $query->where('media.id IN ('.$mediaids.')');
        $query->where('media.published = 1');
        $query->order('media.ordering, image.media_image_name ASC');
        $db->setQuery($query);
	$results = $db->loadObjectList();
        $mediareturns = array();
        foreach ($results as $media)
        { 
            $link_type = $media->link_type;
            $registry = new JRegistry;
            $registry->loadJSON($media->params);
            $itemparams = $registry;
            $mediaid = $media->id;
            $image = $images->getMediaImage($media->impath, $media->path2);
            $player = $mediaelements->getPlayerAttributes($admin_params, $params, $itemparams, $media);
            $playercode = $mediaelements->getPlayerCode($params, $itemparams, $player, $image, $media);
            //set the download image
            $d_image = ($admin_params->get('default_download_image') ? $admin_params->get('default_download_image') : 'download.png' );
            $download_tmp = $images->getMediaImage($d_image, $media = NULL);
            $download_image = $download_tmp->path;
            $compat_mode = $admin_params->get('compat_mode');
            if ($link_type > 0) 
                {
                    $width = $download_tmp->width;
                    $height = $download_tmp->height;

                    if ($compat_mode == 0) {
                        $downloadlink = '<a href="index.php?option=com_biblestudy&mid=' .
                              (int)$mediaid . '&view=sermons&task=download">';
                    } else {
                        $downloadlink = '<a href="http://joomlabiblestudy.org/router.php?file=' .
                                $media->spath . $media->fpath . $media->filename . '&size=' . $media->size . '">';
                    }
                    $downloadlink .= '<img src="' . $download_image . '" alt="' . JText::_('JBS_MED_DOWNLOAD') . '" height="' .
                    $height . '" width="' . $width . '" border="0" title="' . JText::_('JBS_MED_DOWNLOAD') . '" /></a>';
                }
            switch ($link_type) 
            {
                case 0:
                    $mediareturns[] = $playercode;
                    break;

                case 1:
                    $mediareturns[] = $playercode . $downloadlink;echo $mediareturn;
                    break;

                case 2:
                    $mediareturns[] = $downloadlink;
                    break;
            }
      }
       $mediareturn = implode('',$mediareturns);
       return $mediareturn;
    }
    
    
}
?>

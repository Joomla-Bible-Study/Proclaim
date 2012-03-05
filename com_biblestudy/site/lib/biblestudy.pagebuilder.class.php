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
jimport('joomla.html.parameter');
class JBSPagebuilder
{
    function mediaBuilder($mediaids, $params, $admin_params)
    {
        $images = new jbsImages();
        $mediaelements = new jbsMedia();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('media.*');
        $query->from('#__bsms_mediafiles as media');
        $query->select('server.id as serverid, server.server_path');
        $query->join('LEFT','#__bsms_servers AS server ON server.id = media.server');
        $query->select('folder.folderpath');
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
       // $mediareturn = array();
        foreach ($results as $media)
        {
            $link_type = $media->link_type;
            $registry = new JRegistry;
            $registry->loadJSON($media->params);
            $itemparams = $registry;
            
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
                                $media->id . '&view=sermons&task=download">';
                    } else {
                        $downloadlink = '<a href="http://joomlabiblestudy.org/router.php?file=' .
                                $media->server_path . $media->folder_path . $media->filename . '&size=' . $media->size . '">';
                    }
                    $downloadlink .= '<img src="' . $download_image . '" alt="' . JText::_('JBS_MED_DOWNLOAD') . '" height="' .
                    $height . '" width="' . $width . '" border="0" title="' . JText::_('JBS_MED_DOWNLOAD') . '" /></a>';
                }
            
            $image = $images->getMediaImage($media->impath, $media->path2);
            $player = $mediaelements->getPlayerAttributes($admin_params, $params, $itemparams, $media);
            $playercode = $mediaelements->getPlayerCode($params, $itemparams, $player, $image, $media);
             print_r($playercode); 
            switch ($link_type) 
            {
                case 0:
                    $mediareturn = $playercode;
                    break;

                case 1:
                    $mediareturn = $playercode . $downloadlink;
                    break;

                case 2:
                    $mediareturn = $downloadlink;
                    break;
            }
            //echo $player;
            //print_r($playercode);
          //  echo $result->params;
           
           
        }
      //  print_r($results);
    }
    
    
}
?>

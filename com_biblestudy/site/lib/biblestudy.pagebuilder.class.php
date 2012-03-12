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
       if ($mids){ $page->media = $this->mediaBuilder($mids, $params, $admin_params);
           }
       
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
              //  $page->topics = implode(', ', $topics);
                } 
        else {$page->topics = JText::_($item->topics_text);}
        if ($item->thumbnailm)
        {
            $image = $images->getStudyThumbnail($item->thumbnailm);
            $page->study_thumbnail = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $item->studytitle . '">';
        }
        if ($item->series_thumbnail) 
            {
              $image = $images->getSeriesThumbnail($item->series_thumbnail);
              $page->series_thumbnail = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $item->series_text . '">';
            }
        $page->detailslink = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $item->slug . '&t=' . $params->get('detailstemplateid'));    
        return $page;
        $teacherimage = $images->getTeacherImage($item->image, $item->thumb);
        $page->teacherimage = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $item->teachername . '">';
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
        $db->setQuery($query); //print_r($querym);
	$medias = $db->loadObjectList();
        $mediareturns = array();
        foreach ($medias as $media)
        { 
            $link_type = $media->link_type;
            $registry = new JRegistry;
            $registry->loadJSON($media->params);
            $itemparams = $registry;
            $mediaid = $media->id;
            $image = $images->getMediaImage($media->impath, $media->path2);
            $player = $mediaelements->getPlayerAttributes($admin_params, $params, $itemparams, $media);
            $playercode = $mediaelements->getPlayerCode($params, $itemparams, $player, $image, $media);
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
    
    function studyBuilder($whereitem, $wherefield, $params, $admin_params, $limit, $order)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
		        study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.topics_id, study.studyintro,
		        study.teacher_id, study.secondary_reference, study.booknumber2, study.location_id, study.media_hours, study.media_minutes,
		        study.media_seconds, study.series_id, study.thumbnailm, study.thumbhm, study.thumbwm, study.access, study.user_name, 
                        study.user_id, study.studynumber, CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', 
                        study.id, study.alias) ELSE study.id END as slug ');
        $query->from('#__bsms_studies AS study');

        //Join over Message Types
        $query->select('messageType.message_type AS message_type');
        $query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

        //Join over Teachers
        $query->select('teacher.teachername AS teachername, teacher.title as teachertitle, teacher.thumb, teacher.thumbh, teacher.thumbw');
        $query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

        //Join over Series
        $query->select('series.series_text, series.series_thumbnail, series.description as sdescription');
        $query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

        //Join over Books
        $query->select('book.bookname');
        $query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

        //Join over Plays/Downloads
        $query->select('SUM(mediafile.plays) AS totalplays, SUM(mediafile.downloads) as totaldownloads, mediafile.study_id');
        $query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');

        //Join over Locations
        $query->select('locations.location_text');
        $query->join('LEFT', '#__bsms_locations AS locations ON study.location_id = locations.id');

        //Join over topics

        $query->select('GROUP_CONCAT(DISTINCT st.topic_id)');
        $query->join('LEFT', '#__bsms_studytopics AS st ON study.id = st.study_id');
        $query->select('GROUP_CONCAT(DISTINCT t.id), GROUP_CONCAT(DISTINCT t.topic_text) as topics_text, GROUP_CONCAT(DISTINCT t.params)');
        $query->join('LEFT', '#__bsms_topics AS t ON t.id = st.topic_id');

        //Join over users
        $query->select('users.name as submitted');
        $query->join('LEFT', '#__users as users on study.user_id = users.id');

        $query->select('GROUP_CONCAT(DISTINCT m.id) as mids');
        $query->join('LEFT','#__bsms_mediafiles as m ON study.id = m.study_id');
        
        $query->group('study.id');

        $query->select('GROUP_CONCAT(DISTINCT media.id) as mids');
        $query->join('LEFT','#__bsms_mediafiles as media ON study.id = media.study_id');
        $query->where('study.published = 1');
        $query->where($wherefield.' = '. $whereitem);
        if (!$order){$order = 'DESC';}
        $query->order('studydate '.$order);
        if (!$limit){$limit = 10;}
        $db->setQuery($query,0,$limit);
      //  $db->setQuery($query->__toString());
     //   print_r ($query);
	//$studies = $db->loadObjectList();
        $studies = $db->loadObjectList();
        //Remove items user is not authorized to see
     /*   $user = JFactory::getUser();
        $groups = $user->getAuthorisedViewLevels();
        $count = count($studies);
        for ($i = 0; $i < $count; $i++) {

            if ($studies[$i]->access > 1) {
                if (!in_array($studies[$i]->access, $groups)) {
                    unset($studies[$i]);
                }
            }
        } */
        foreach($studies as $study)    
        {$pelements = $this->buildPage($study, $params, $admin_params);
            $study->scripture1 = $pelements->scripture1;
            $study->scripture2 = $pelements->scripture2;
            $study->media = $pelements->media;
            $study->duration = $pelements->duration;
            $study->studydate = $pelements->studydate;
            $study->topics = $pelements->topics;
            $study->study_thumbnail = $pelements->study_thumbnail;
            $study->series_thumbnail = $pelements->series_thumbnail;
            $study->detailslink = $pelements->detailslink; }
        
        return $studies;
    }
}
?>

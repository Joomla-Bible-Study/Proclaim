<?php

/*
 * Class to build page elements in use by custom template files
 * @since 7.0.1
 * @author Tom Fuller
 */
//No Direct Access
defined('_JEXEC') or die;

class JBSPagebuilder
{
    function mediaBuilder($media, $params)
    {
        //$mediaarray = explode(',',$media);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('media.*');
        $query->from('#__bsms_mediafiles as media');
        $query->select('server.id as serverid, server.server_path');
        $query->join('LEFT','#__bsms_servers AS server ON server.id = media.server');
        $query->select('folder.folderpath');
        $query->join('LEFT','#__bsms_folders as folder ON folder.id = media.path');
        $query->select('image.media_image_path AS impath, image.media_image_name as imname');
        $query->join('LEFT','#__bsms_media as image ON image.id = media.media_image');
        $query->select('study.media_hours, study.media_minutes, study.media_seconds');
        $query->join('LEFT','#__bsms_studies AS study ON study.id = media.study_id');
        $query->select('mime.mimetext');
        $query->join('LEFT','#__bsms_mimetype as mime ON mime.id = media.mime_type');
        
        $query->where('media.id IN ('.$media.')');
        $query->where('media.published = 1');
        $query->order('media.ordering, media.media_image_name ASC');
        $db->setQuery($query);
	$results = $db->loadObject();
        $query = 'SELECT #__bsms_mediafiles.*, #__bsms_servers.id AS ssid, #__bsms_servers.server_path AS spath, #__bsms_folders.id AS fid,'
        . ' #__bsms_folders.folderpath AS fpath, #__bsms_media.id AS mid, #__bsms_media.media_image_path AS impath, #__bsms_media.media_image_name AS imname,'
        . ' #__bsms_media.path2 AS path2, s.studytitle, s.studydate, s.studyintro, s.media_hours, s.media_minutes, s.media_seconds, s.teacher_id,'
        . ' s.booknumber, s.chapter_begin, s.chapter_end, s.verse_begin, s.verse_end, t.teachername, t.id as tid, s.id as sid, s.studyintro,'
        . ' #__bsms_media.media_alttext AS malttext, #__bsms_mimetype.id AS mtid, #__bsms_mimetype.mimetext FROM #__bsms_mediafiles'
        . ' LEFT JOIN #__bsms_media ON (#__bsms_media.id = #__bsms_mediafiles.media_image) LEFT JOIN #__bsms_servers'
        . ' ON (#__bsms_servers.id = #__bsms_mediafiles.server) LEFT JOIN #__bsms_folders ON (#__bsms_folders.id = #__bsms_mediafiles.path)'
        . ' LEFT JOIN #__bsms_mimetype ON (#__bsms_mimetype.id = #__bsms_mediafiles.mime_type) LEFT JOIN #__bsms_studies AS s'
        . ' ON (s.id = #__bsms_mediafiles.study_id) LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
        . ' WHERE #__bsms_mediafiles.study_id = ' . $id . ' AND #__bsms_mediafiles.published = 1 ORDER BY ordering ASC, #__bsms_media.media_image_name ASC';

        //$mediaids = implode(' OR ',$mediaarray);
        foreach ($results as $result)
        {
            
        }
        print_r($results);
    }
}
?>

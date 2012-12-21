<?php

/**
 * Elements Helper
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;
/**
 * @todo change to JLoader::register
 */
require_once(JPATH_ADMINISTRATOR . '/components/com_biblestudy/lib/biblestudy.defines.php');
//require_once (BIBLESTUDY_PATH_LIB . '/biblestudy.images.class.php');
JLoader::register('jbsImages', dirname(__FILE__) . '/lib/biblestudy.defines.php');
//require_once (BIBLESTUDY_PATH_LIB . '/biblestudy.media.class.php');
JLoader::register('jbsMedia', dirname(__FILE__) . '/lib/biblestudy.media.class.php');
require_once (BIBLESTUDY_PATH_ADMIN_HELPERS . '/image.php');


/**
 * Get Elementid
 * @param int $rowid
 * @param object $row
 * @param JRegistry $params
 * @param object $admin_params
 * @param int $templateid
 * @todo Redo to MVC Standers under a class
 * @return object
 */
function getElementid($rowid, $row, $params, $admin_params, $templateid) {
    $elementid = new stdClass();
    $path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
    include_once($path1 . 'scripture.php');
    include_once($path1 . 'duration.php');
    include_once($path1 . 'date.php');
    include_once($path1 . 'filesize.php');
    include_once($path1 . 'textlink.php');
    include_once($path1 . 'mediatable.php');
    include_once($path1 . 'store.php');
    include_once($path1 . 'filepath.php');
    include_once($path1 . 'custom.php');

    $db = JFactory::getDBO();
    switch ($rowid) {
        case 1:
            $elementid->id = 'scripture1';
            $elementid->headertext = JText::_('JBS_CMN_SCRIPTURE');
            $esv = 0;
            $scripturerow = 1;
            $elementid->element = getScripture($params, $row, $esv, $scripturerow);
            break;
        case 2:
            $elementid->id = 'scripture2';
            $elementid->headertext = JText::_('JBS_CMN_SCRIPTURE');
            $esv = 0;
            $scripturerow = 2;
            $elementid->element = getScripture($params, $row, $esv, $scripturerow);
            break;
        case 3:
            $elementid->id = 'secondary';
            $elementid->headertext = JText::_('JBS_CMN_SECONDARY_REFERENCES');
            $elementid->element = $row->secondary_reference;
            break;
        case 4:
            $elementid->id = 'duration';
            $elementid->headertext = JText::_('JBS_CMN_DURATION');
            $elementid->element = getDuration($params, $row);
            break;
        case 5:
            $elementid->id = 'title';
            $elementid->headertext = JText::_('JBS_CMN_TITLE');
            if (isset($row->studytitle)):
                $elementid->element = $row->studytitle;
            else:
                $elementid->element = '';
            endif;
            break;
        case 6:
            $elementid->id = 'studyintro';
            $elementid->headertext = JText::_('JBS_CMN_INTRODUCTION');
            if (isset($row->studyintro)):
                $elementid->element = $row->studyintro;
            else:
                $elementid->element = '';
            endif;
            break;
        case 7:
            $elementid->id = 'teacher';
            $elementid->headertext = JText::_('JBS_CMN_TEACHER');
            $elementid->element = $row->teachername;
            break;
        case 8:
            $elementid->id = 'teacher';
            $elementid->headertext = JText::_('JBS_CMN_TEACHER');
            $elementid->element = $row->teachertitle . ' ' . $row->teachername;
            break;
        case 9:
            $elementid->id = 'series';
            $elementid->headertext = JText::_('JBS_CMN_SERIES');
            $elementid->element = $row->series_text;
            break;
        case 10:
            $elementid->id = 'date';
            $elementid->headertext = JText::_('JBS_CMN_STUDY_DATE');
            $elementid->element = $row->studydate;
            break;
        case 11:
            $elementid->id = 'submitted';
            $elementid->headertext = JText::_('JBS_CMN_SUBMITTED_BY');
            $elementid->element = $row->submitted;
            break;
        case 12:
            $elementid->id = 'hits';
            $elementid->headertext = JText::_('JBS_CMN_VIEWS');
            $elementid->element = JText::_('JBS_CMN_HITS') . ' ' . $row->hits;
            break;
        case 13:
            $elementid->id = 'studynumber';
            $elementid->headertext = JText::_('JBS_CMN_STUDYNUMBER');
            $elementid->element = $row->studynumber;
            break;
        case 14:
            $elementid->id = 'topic';
            $elementid->headertext = JText::_('JBS_CMN_TOPIC');

            if (substr_count($row->topics_text, ',')) {
                $topics = explode(',', $row->topics_text);
                foreach ($topics as $key => $value) {
                    $topics[$key] = JText::_($value);
                }
                $elementid->element = implode(', ', $topics);
            } else {
                $elementid->element = JText::_($row->topics_text);
            }
            break;
        case 15:
            $elementid->id = 'location';
            $elementid->headertext = JText::_('JBS_CMN_LOCATION');
            $elementid->element = $row->location_text;
            break;
        case 16:
            $elementid->id = 'messagetype';
            $elementid->headertext = JText::_('JBS_CMN_MESSAGE_TYPE');
            $elementid->element = $row->message_type;
            break;
        case 17:
            $elementid->id = 'details';
            $elementid->headertext = JText::_('JBS_CMN_DETAILS');
            $textorpdf = 'text';
            $elementid->element = getTextlink($params, $row, $textorpdf, $admin_params, $templateid);
            break;
        case 18:
            $elementid->id = 'details';
            $elementid->headertext = JText::_('JBS_CMN_DETAILS');
            $textorpdf = 'text';
            $elementid->element = '<table class="detailstable"><tbody><tr><td>';
            $elementid->element .= getTextlink($params, $row, $textorpdf, $admin_params, $templateid) . '</td><td>';
            $textorpdf = 'pdf';
            $elementid->element .= getTextlink($params, $row, $textorpdf, $admin_params, $templateid) . '</td></tr></table>';
            break;
        case 19:
            $elementid->id = 'details';
            $elementid->headertext = JText::_('JBS_CMN_DETAILS');
            $textorpdf = 'pdf';
            $elementid->element = getTextlink($params, $row, $textorpdf, $admin_params, $templateid);
            break;
        case 20:
            $mediaclass = new jbsMedia();
            $elementid->id = 'jbsmedia';
            $elementid->headertext = JText::_('JBS_CMN_MEDIA');
            $elementid->element = $mediaclass->getMediaTable($row, $params, $admin_params);
            break;
        case 22:
            $elementid->id = 'store';
            $elementid->headertext = JText::_('JBS_CMN_STORE');
            $elementid->element = getStore($params, $row);
            break;
        case 23:
            $elementid->id = 'filesize';
            $elementid->headertext = JText::_('JBS_CMN_FILESIZE');
            $query_media1 = 'SELECT #__bsms_mediafiles.id AS mid, #__bsms_mediafiles.size, #__bsms_mediafiles.published, #__bsms_mediafiles.study_id'
                    . ' FROM #__bsms_mediafiles'
                    . ' WHERE #__bsms_mediafiles.study_id = ' . $row->id . ' AND #__bsms_mediafiles.published = 1 ORDER BY ordering, #__bsms_mediafiles.id ASC LIMIT 1';

            $db->setQuery($query_media1);
            $media1 = $db->loadObject();
            $elementid->element = getFilesize($media1->size);
            break;
        case 25:
            $elementid->id = 'thumbnail';
            $elementid->headertext = JText::_('JBS_CMN_THUMBNAIL');

            if ($row->thumbnailm) {
                $images = new jbsImages();
                $image = $images->getStudyThumbnail($row->thumbnailm);
                $elementid->element = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $row->studytitle . '">';
            } else {
                $elementid->element = '';
            }
            break;
        case 26:
            $elementid->id = 'series_thumbnail';
            $elementid->headertext = JText::_('JBS_CMN_THUMBNAIL');
            if ($row->series_thumbnail) {
                $images = new jbsImages();
                $image = $images->getSeriesThumbnail($row->series_thumbnail);
                $elementid->element = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $row->series_text . '">';
            } else {
                $elementid->element = '';
            }
            break;
        case 27:
            $elementid->id = 'series_description';
            $elementid->headertext = JText::_('JBS_CMN_DESCRIPTION');
            $elementid->element = $row->sdescription;
            break;
        case 28:
            $elementid->id = 'plays';
            $elementid->headertext = JText::_('JBS_CMN_PLAYS');
            $elementid->element = $row->totalplays;
            break;
        case 29:
            $elementid->id = 'downloads';
            $elementid->headertext = JText::_('JBS_CMN_DOWNLOADS');
            $elementid->element = $row->totaldownloads;
            break;
        case 30:
            $timages = new jbsImages();
	        $elementid->id = 'teacher-image';
	        $elementid->headetext = JText::_('JBS_CMN_TEACHER_IMAGE');
            $query = "SELECT thumb, teacher_thumbnail FROM #__bsms_teachers WHERE id = $row->teacher_id";
            $db->setQuery($query);
            $thumb = $db->loadObject();
            if ($thumb->teacher_thumbnail) {
                $timage = $timages->getTeacherImage($thumb->teacher_thumbnail);
            } else {
                $timage = $timage = $timages->getTeacherImage($thumb->thumb);
            }
            $elementid->element = '<img src="' . $timage->path . '" width="' . $timage->width . '" height="' . $timage->height . '" />';
            break;
        case 100:
            $elementid->id = '';
            $elementid->headertext = '';
            $elementid->element = '';
            break;
    }
    return $elementid;
}
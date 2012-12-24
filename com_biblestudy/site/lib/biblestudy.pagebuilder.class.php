<?php

/**
 * BibleStudy Page Builder class
 * @package BibleStudy.Site
 * @copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
// No Direct Access
defined('_JEXEC') or die;
//@todo dos not include from core controller need to look into why it is thinking it is apart of the com_content controller
require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.defines.php');
//require_once(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');
JLoader::register('JBSMTranslated', JPATH_ADMINISTRATOR . '/helpers/translated.php');
//require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.images.class.php');
JLoader::register('jbsImages', JPATH_COMPONENT . '/lib/biblestudy.images.class.php');
//require_once (JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'biblestudy.media.class.php');
JLoader::register('jbsMedia', JPATH_COMPONENT . '/lib/biblestudy.media.class.php');
$path1 = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;
include_once($path1 . 'scripture.php');
include_once($path1 . 'duration.php');
include_once($path1 . 'date.php');
include_once($path1 . 'filesize.php');
include_once(BIBLESTUDY_PATH_ADMIN_HELPERS . DIRECTORY_SEPARATOR . 'image.php');
include_once(BIBLESTUDY_PATH_ADMIN_HELPERS . DIRECTORY_SEPARATOR . 'translated.php');
jimport('joomla.html.parameter');

/**
 * Class to build page elements in use by custom template files
 *
 * @package BibleStudy.Site
 * @since 7.0.1
 */
class JBSPagebuilder {
	public $event;

    /**
     * Build Page
     *
     * @param object $item
     * @param object $params
     * @param object $admin_params
     * @return string
     */
    function buildPage($item, $params, $admin_params) {

        $item->tp_id = '1';
        $images = new jbsImages();
        //media files image, links, download
        $mids = $item->mids;
        $page = new stdClass();
        if ($mids) {
            $page->media = $this->mediaBuilder($mids, $params, $admin_params);
        } else {
            $page->media = '';
        }
        //scripture1
        $esv = 0;
        $scripturerow = 1;
        if ($item->chapter_begin) {
            $page->scripture1 = getScripture($params, $item, $esv, $scripturerow);
        } else {
            $page->scripture1 = '';
        }
        if (!$item->secondary_reference)
        {
            $item->secondary_reference = '';
        }
        //scripture 2
        $esv = 0;
        $scripturerow = 2;
        if (isset($item->chapter_begin2) && $item->booknumber2 >= 1) {
            $page->scripture2 = getScripture($params, $item, $esv, $scripturerow);
        } else {
            $page->scripture2 = '';
        }
        //duration
        $page->duration = getDuration($params, $item);
        $page->studydate = getstudyDate($params, $item->studydate);
        //@todo need to look at why i have to do this hear.
        $item->topics_text = JBSMTranslated::getConcatTopicItemTranslated($item);
        if (isset($item->topics_text) && (substr_count($item->topics_text, ',') > 0)) {
            $topics = explode(',', $item->topics_text);
            foreach ($topics as $key => $value) {
                $topics[$key] = JText::_($value);
            }
            $page->topics = implode(', ', $topics);
        } else {
            $page->topics = JText::_($item->topics_text);
        }
        if ($item->thumbnailm) {
            $image = $images->getStudyThumbnail($item->thumbnailm);
            $page->study_thumbnail = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $item->studytitle . '" />';
        } else {
            $page->study_thumbnail = '';
        }
        if ($item->series_thumbnail) {
            $image = $images->getSeriesThumbnail($item->series_thumbnail);
            $page->series_thumbnail = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $item->series_text . '" />';
        } else {
            $page->series_thumnail = '';
        }
        $page->detailslink = JRoute::_('index.php?option=com_biblestudy&view=sermon&id=' . $item->slug . '&t=' . $params->get('detailstemplateid'));
        if (!isset($item->image)):
            $item->image = '';
        endif;
        if (!isset($item->thumb)):
            $item->thumb = '';
        endif;
        if ($item->image || $item->thumb) {
            $image = $images->getTeacherImage($item->image, $item->thumb);
            $page->teacherimage = '<img src="' . JURI::base() . $image->path . '" width="' . $image->width . '" height="' . $image->height . '" alt="' . $item->teachername . '" />';
        } else {
            $page->teacherimage = '';
        }
        //studytext
        if (!isset($item->studytext))
        {
            $item->studytext = '';
        }
        if (!isset($item->secondary_reference))
        {
            $item->secondary_reference = '';
        }
        if (!isset($item->sdescription))
        {
            $item->sdescription = '';
        }
        if($params->get('show_scripture_link') == 0){return $page;}
        else
            {
                //set the item for the plugin to $item->text //run content plugins
                if ($page->scripture1)
                {
                    $item->text = $page->scripture1;
                    $item = $this->runContentPlugins($item, $params);
                    $page->scripture1 = $item->text;
                }
                if ($page->scripture2)
                {
                    $item->text = $page->scripture2;
                    $item = $this->runContentPlugins($item, $params);
                    $page->scripture2 = $item->text;
                }
                if ($item->studyintro)
                {
                    $item->text = $item->studyintro;
                    $item = $this->runContentPlugins($item, $params);
                    $page->studyintro = $item->text;
                }
                if ($item->studytext)
                {
                    $item->text = $item->studytext;
                    $item = $this->runContentPlugins($item, $params);
                    $page->studytext = $item->text;
                }
                if ($item->secondary_reference)
                {
                    $item->text = $item->secondary_reference;
                    $item = $this->runContentPlugins($item, $params);
                    $page->secondary_reference = $item->text;
                }
                if ($item->sdescription)
                {
                    $item->text = $item->sdescription;
                    $item = $this->runContentPlugins($item, $params);
                    $page->sdescription = $item->text;
                }
                return $page;
            }


    }

    /**
     * Media Builder
     *
     * @param array $mediaids
     * @param object $params
     * @param object $admin_params
     * @return string
     */
    function mediaBuilder($mediaids, $params, $admin_params) {
        $images = new jbsImages();
        $mediaelements = new jbsMedia();

        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('media.*');
        $query->from('#__bsms_mediafiles as media');
        $query->select('server.id as serverid, server.server_path as spath');
        $query->join('LEFT', '#__bsms_servers AS server ON server.id = media.server');
        $query->select('folder.folderpath as fpath');
        $query->join('LEFT', '#__bsms_folders as folder ON folder.id = media.path');
        $query->select('image.media_image_path AS impath, image.media_image_name as imname, image.path2');
        $query->join('LEFT', '#__bsms_media as image ON image.id = media.media_image');
        $query->select('study.media_hours, study.media_minutes, study.media_seconds');
        $query->join('LEFT', '#__bsms_studies AS study ON study.id = media.study_id');
        $query->select('mime.mimetext');
        $query->join('LEFT', '#__bsms_mimetype as mime ON mime.id = media.mime_type');
        $query->where('media.id IN (' . $mediaids . ')');
        $query->where('media.published = 1');
        $query->order('media.ordering, image.media_image_name ASC');
        $db->setQuery($query); //print_r($querym);
        $medias = $db->loadObjectList();
        $mediareturns = array();
        foreach ($medias as $media) {
            $link_type = $media->link_type;
            $registry = new JRegistry;
            $registry->loadString($media->params);
            $itemparams = $registry;
            $mediaid = $media->id;
            $image = $images->getMediaImage($media->impath, $media->path2);
            $player = $mediaelements->getPlayerAttributes($admin_params, $params, $itemparams, $media);
            $playercode = $mediaelements->getPlayerCode($params, $itemparams, $player, $image, $media);
            $d_image = ($admin_params->get('default_download_image') ? $admin_params->get('default_download_image') : 'download.png' );
            $download_tmp = $images->getMediaImage($d_image, NULL);
            $download_image = $download_tmp->path;
            $compat_mode = $admin_params->get('compat_mode');
			$downloadlink = null;
            if ($link_type > 0) {
                $width = $download_tmp->width;
                $height = $download_tmp->height;

                if ($compat_mode == 0) {
                    $downloadlink = '<a href="index.php?option=com_biblestudy&mid=' .
                            (int) $mediaid . '&view=sermons&task=download">';
                } else {
                    $downloadlink = '<a href="http://joomlabiblestudy.org/router.php?file=' .
                            $media->spath . $media->fpath . $media->filename . '&size=' . $media->size . '">';
                }
                $downloadlink .= '<img src="' . $download_image . '" alt="' . JText::_('JBS_MED_DOWNLOAD') . '" height="' .
                        $height . '" width="' . $width . '" border="0" title="' . JText::_('JBS_MED_DOWNLOAD') . '" /></a>';
            }
            switch ($link_type) {
                case 0:
                    $mediareturns[] = $playercode;
                    break;

                case 1:
                    $mediareturns[] = $playercode . $downloadlink;
                    break;

                case 2:
                    $mediareturns[] = $downloadlink;
                    break;
            }
        }
        $mediareturn = implode('', $mediareturns);
        return $mediareturn;
    }

    /**
     * Study Builder
     * @param string $whereitem
     * @param string $wherefield
     * @param object $params
     * @param object $admin_params
     * @param int $limit
     * @param string $order
     * @return object
     */
    function studyBuilder($whereitem, $wherefield, $params, $admin_params, $limit, $order) {
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $menu = $app->getMenu();
        $item = $menu->getActive();
        if ($item) {
            $language = $db->quote($item->language) . ',' . $db->quote('*');
            if ($language != '*') {
                $langlink = '&filter.languages=' . $language;
            }
        } else {
            $language = $db->quote('*');
            $langlink = '';
        }

        // Compute view access permissions.
        $user = JFactory::getUser();
        //$groups = $user->getAuthorisedViewLevels();
        $groups = implode(',', $user->getAuthorisedViewLevels());

        $query = $db->getQuery(true);
        $query->select('study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
		        study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.topics_id, study.studyintro,
		        study.teacher_id, study.secondary_reference, study.booknumber2, study.chapter_begin2, study.verse_begin2, study.chapter_end2,
                        study.verse_end2, study.location_id, study.media_hours, study.media_minutes, study.media_seconds, study.series_id,
                        study.thumbnailm, study.thumbhm, study.thumbwm, study.access, study.user_name,
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
        $query->select('series.series_text, series.series_thumbnail, series.description as sdescription, series.access');
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
        $query->select('GROUP_CONCAT(DISTINCT t.id), GROUP_CONCAT(DISTINCT t.topic_text) as topic_text, GROUP_CONCAT(DISTINCT t.params) as topic_params');
        $query->join('LEFT', '#__bsms_topics AS t ON t.id = st.topic_id');

        //Join over users
        $query->select('users.name as submitted');
        $query->join('LEFT', '#__users as users on study.user_id = users.id');

        $query->select('GROUP_CONCAT(DISTINCT m.id) as mids');
        $query->join('LEFT', '#__bsms_mediafiles as m ON study.id = m.study_id');

        $query->group('study.id');

        $query->select('GROUP_CONCAT(DISTINCT media.id) as mids');
        $query->join('LEFT', '#__bsms_mediafiles as media ON study.id = media.study_id');
        $query->where('study.published = 1');
        $query->where($wherefield . ' = ' . $whereitem);
        $query->where('study.language in (' . $language . ')');
        if (!$order) {
            $order = 'DESC';
        }
        $query->order('studydate ' . $order);
        if (!$limit) {
            $limit = 10;
        }

        //filter only for authorized view
        $query->where('(series.access IN (' . $groups . ') or study.series_id <= 0)');
        $query->where('study.access IN (' . $groups . ')');

        $db->setQuery($query, 0, $limit);
        $studies = $db->loadObjectList();

        return $studies;
    }
/**
     * Run Content Plugins
     * @param object $params
     * @param object $item
     * @return object
     */
function runContentPlugins($item, $params)
    {
        $offset = ''; //We don't need offset but it is a required argument for the plugin dispatcher
        JPluginHelper::importPlugin('content');

        //Run content plugins
		if(version_compare(JVERSION, '3.0', 'ge'))
		{
        $dispatcher	= JEventDispatcher::getInstance();
		} else {
			$dispatcher = JDispatcher::getInstance();
		}
        $results = $dispatcher->trigger('onContentPrepare', array('com_biblestudy.sermon', & $item, & $params, $offset));

        $item->event = new stdClass;

        $results = $dispatcher->trigger('onContentAfterTitle', array('com_biblestudy.sermon', &$item, &$params, $offset));
        $item->event->afterDisplayTitle = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentBeforeDisplay', array('com_biblestudy.sermon', &$item, &$params, $offset));
        $item->event->beforeDisplayContent = trim(implode("\n", $results));

        $results = $dispatcher->trigger('onContentAfterDisplay', array('com_biblestudy.sermon', &$item, &$params, $offset));
        $item->event->afterDisplayContent = trim(implode("\n", $results));

        return $item;
    }
}
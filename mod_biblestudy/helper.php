<?php

/**
 * @version $Id: helper.php 1 $
 * @package mod_biblestudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
defined('_JEXEC') or die;

if (class_exists('modbiblestudyhelper')) {
    return;
}

class modBiblestudyHelper {

    var $_template;
    var $_admin;

    function getLatest($params) {

        $items = $params->get('locations', 1);

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $teacher = $params->get('teacher_id');
        $topic = $params->get('topic_id');
        $book = $params->get('booknumber');
        $series = $params->get('series_id');
        $locations = $params->get('locations');
        $condition = $params->get('condition');
        $messagetype_menu = $params->get('messagetype');
        $year = $params->get('year');
        $orderparam = $params->get('order', '1');
        if ($orderparam == 2) {
            $order = "ASC";
        } else {
            $order = "DESC";
        }
        if ($condition > 0) {
            $condition = ' AND ';
        } else {
            $condition = ' OR ';
        }

        $query->from('#__bsms_studies AS study');

        $query->select('study.id, study.published, study.studydate, study.studytitle, study.booknumber, study.chapter_begin,
                        study.verse_begin, study.chapter_end, study.verse_end, study.hits, study.alias, study.topics_id, study.studyintro,
                        study.teacher_id, study.secondary_reference, study.booknumber2, study.location_id, study.media_hours, study.media_minutes,
                        study.media_seconds, study.series_id, study.thumbnailm, study.thumbhm, study.thumbwm, study.access, study.user_name,
                        study.user_id, study.studynumber,'
                . ' CASE WHEN CHAR_LENGTH(study.alias) THEN CONCAT_WS(\':\', study.id, study.alias) ELSE study.id END as slug ');



        //Join over Message Types
        $query->select('messageType.message_type AS messageType');
        $query->join('LEFT', '#__bsms_message_type AS messageType ON messageType.id = study.messagetype');

        //Join over Teachers
        $query->select('teacher.teachername AS teachername, teacher.id AS tid');
        $query->join('LEFT', '#__bsms_teachers AS teacher ON teacher.id = study.teacher_id');

        //Join over Series
        $query->select('series.series_text');
        $query->join('LEFT', '#__bsms_series AS series ON series.id = study.series_id');

        //Join over Books
        $query->select('book.bookname');
        $query->join('LEFT', '#__bsms_books AS book ON book.booknumber = study.booknumber');

        //Join over Plays/Downloads
        $query->select('SUM(mediafile.plays) AS totalplays, SUM(mediafile.downloads) as totaldownloads, mediafile.study_id');
        $query->join('LEFT', '#__bsms_mediafiles AS mediafile ON mediafile.study_id = study.id');
        $query->group('study.id');

        //filter over teazchers
        $filters = $teacher;
        if (count($filters) > 1) {
            $where2 = array();
            $subquery = '(';
            foreach ($filters as $filter) {
                $where2[] = 'study.teacher_id = ' . (int) $filter;
            }
            $subquery .= implode(' OR ', $where2);
            $subquery .= ')';

            $query->where($subquery);
        } else {
            foreach ($filters as $filter) {
                if ($filter != -1) {
                    $query->where('study.teacher_id = ' . (int) $filter, $condition);
                }
            }
        }
        //filter locations
        $filters = $locations;
        if (count($filters) > 1) {
            $where2 = array();
            $subquery = '(';
            foreach ($filters as $filter) {
                $where2[] = 'study.location_id = ' . (int) $filter;
            }
            $subquery .= implode(' OR ', $where2);
            $subquery .= ')';

            $query->where($subquery);
        } else {
            foreach ($filters AS $filter) {
                if ($filter != -1) {
                    $query->where('study.location_id = ' . (int) $filter, $condition);
                }
            }
        }
        //filter over books
        $filters = $book;
        if (count($filters) > 1) {
            $where2 = array();
            $subquery = '(';
            foreach ($filters as $filter) {
                $where2[] = 'study.booknumber = ' . (int) $filter;
            }
            $subquery .= implode(' OR ', $where2);
            $subquery .= ')';

            $query->where($subquery);
        } else {
            foreach ($filters AS $filter) {
                if ($filter != -1) {
                    $query->where('study.booknumber = ' . (int) $filter, $condition);
                }
            }
        }
        $filters = $series;
        if (count($filters) > 1) {
            $where2 = array();
            $subquery = '(';
            foreach ($filters as $filter) {
                $where2[] = 'study.series_id = ' . (int) $filter;
            }
            $subquery .= implode(' OR ', $where2);
            $subquery .= ')';

            $query->where($subquery);
        } else {
            foreach ($filters AS $filter) {
                if ($filter != -1) {
                    $query->where('study.series_id = ' . (int) $filter, $condition);
                }
            }
        }
        $filters = $topic;
        if (count($filters) > 1) {
            $where2 = array();
            $subquery = '(';
            foreach ($filters as $filter) {
                $where2[] = 'study.topics_id = ' . (int) $filter;
            }
            $subquery .= implode(' OR ', $where2);
            $subquery .= ')';

            $query->where($subquery);
        } else {
            foreach ($filters AS $filter) {
                if ($filter != -1) {
                    $query->where('study.topics_id = ' . (int) $filter, $condition);
                }
            }
        }
        $filters = $messagetype_menu;
        if (count($filters) > 1) {
            $where2 = array();
            $subquery = '(';
            foreach ($filters as $filter) {
                $where2[] = 'study.messagetype = ' . (int) $filter;
            }
            $subquery .= implode(' OR ', $where2);
            $subquery .= ')';

            $query->where($subquery);
        } else {
            foreach ($filters AS $filter) {
                if ($filter != -1) {
                    $query->where('study.messagetype = ' . (int) $filter, $condition);
                }
            }
        }
        $filters = $year;
        if (count($filters) > 1) {
            $where2 = array();
            $subquery = '(';
            foreach ($filters as $filter) {
                $where2[] = 'YEAR(study.studydate) = ' . (int) $filter;
            }
            $subquery .= implode(' OR ', $where2);
            $subquery .= ')';

            $query->where($subquery);
        } else {
            foreach ($filters AS $filter) {
                if ($filter != -1) {
                    $query->where('YEAR(study.studydate) = ' . (int) $filter, $condition);
                }
            }
        }
        $query->order('studydate ' . $order);
        $db->setQuery((string) $query, 0, $params->get('moduleitems', '5'));
        $rows = $db->loadObjectList();
        return $rows;
    }

    function _buildContentWhere() {

    }

    function getTemplate($params) {
        $db = JFactory::getDBO();
        $templateid = $params->get('modulemenuid', 1);
        $query = 'SELECT *'
                . ' FROM #__bsms_templates'
                . ' WHERE published = 1 AND id = ' . $templateid;
        $db->setQuery($query);
        $template = $db->loadObjectList();
        return $template;
    }

    function getAdmin() {
        $db = JFactory::getDBO();
        $query = 'SELECT *'
                . ' FROM #__bsms_admin'
                . ' WHERE id = 1';
        $db->setQuery($query);
        $admin = $db->loadObjectList();
        return $admin;
    }

    function renderStudy(&$study, &$params) {
        require(JModuleHelper::getLayoutPath('mod_biblestudy', '_study'));
    }

}
<?php

/**
 * @version     $Id: commentslist.php 2025 2011-08-28 04:08:06Z genu $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

abstract class modelClass extends JModelList {

}

class biblestudyModelcommentslist extends modelClass {

   

    /**
     * @since   7.0
     */
    protected function populateState($ordering = null, $direction = null) {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);
        parent::populateState('comment.comment_date', 'DESC');
    }

    /**
     *
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(
                $this->getState(
                        'list.select', 'comment.*'));
        $query->from('#__bsms_comments AS comment');

        // Filter by published state
        $published = $this->getState('filter.published');
        if (is_numeric($published)) {
            $query->where('comment.published = ' . (int) $published);
        } else if ($published === '') {
            $query->where('(comment.published = 0 OR comment.published = 1)');
        }

        //Join over Studies
        $query->select('study.studytitle AS studytitle, study.chapter_begin, study.studydate');
        $query->join('LEFT', '#__bsms_studies AS study ON study.id = comment.study_id');

        //Join over books
        $query->select('book.bookname as bookname');
        $query->join('LEFT', '#__bsms_books as book ON book.booknumber = study.booknumber');


        //Add the list ordering clause
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->getEscaped($orderCol . ' ' . $orderDirn));
        return $query;
    }

}
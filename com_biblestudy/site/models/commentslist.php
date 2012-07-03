<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyModelcommentslist extends JModelList {

    /**
     * @since   7.0.0
     */
    protected function populateState($ordering = null, $direction = null) {

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $studytitle = $this->getUserStateFromRequest($this->context . '.filter.studytitle', 'filter_studytitle');
        $this->setState('filter.studytitle', $studytitle);

        $date = $this->getUserStateFromRequest($this->context . '.filter.studydate', 'filter_studydate', '');
        $this->setState('filter.studydate', $date);

        $state = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state');
        $this->setState('filter.state', $state);

        parent::populateState('comment.comment_date', 'DESC');
    }

    /**
     *
     * @since   7.0.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select(
                $this->getState(
                        'list.select', 'comment.*'));
        $query->from('#__bsms_comments AS comment');

        //Filter by state
        $state = $this->getState('filter.state');
        if (empty($state))
            $query->where('comment.published = 0 OR comment.published = 1');
        else
            $query->where('comment.published = ' . (int) $state);

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
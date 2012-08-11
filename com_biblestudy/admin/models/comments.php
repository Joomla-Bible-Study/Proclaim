<?php

/**
 * Comments Model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Comments model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelComments extends JModelList {

    /**
     * Constructer
     * @param string $config
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'comment.id',
                'published', 'comment.published',
                'ordering', 'comment.ordering',
                'studytitle', 'comment.studytitle',
                'bookname', 'comment.studytitle',
                'createdate', 'comment.createdate',
                'language', 'comment.language'
            );
        }

        parent::__construct($config);
    }

    /**
     * Get Stored ID
     * @param string $id   A prefix for the store id
     * @since 7.0
     */
    protected function getStoreId($id = '') {

        // Compile the store id.
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Populate State
     * @param null $ordering
     * @param null $direction
     *
     * @since 7.0
     */
    protected function populateState($ordering = null, $direction = null) {
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }
        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);
        parent::populateState('comment.comment_date', 'DESC');
    }

    /**
     * List Query
     * @since   7.0
     */
    protected function getListQuery() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(
                $this->getState(
                        'list.select', 'comment.*'));
        $query->from('#__bsms_comments AS comment');

        // Join over the language
        $query->select('l.title AS language_title');
        $query->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = comment.language');

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
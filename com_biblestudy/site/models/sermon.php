<?php

/**
 * @version $Id: sermon.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

class BiblestudyModelSermon extends JModelItem {

    /**
     * Model context string.
     *
     * @var		string
     */
    protected $_context = 'com_biblestudy.sermon';

    /**
     * Constructor that retrieves the ID from the request
     *
     * @access	public
     * @return	void
     */
    var $_template;
    var $_admin;

    function __construct() {
        parent::__construct();

        $this->hit($pk = 0);
    }

    /**
     * Method to increment the hit counter for the study
     *
     * @access	public
     * @return	boolean	True on success
     * @since	1.5
     */
    function hit($pk = null) {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('study.id');
        $db = JFactory::getDBO();
        $db->setQuery('UPDATE ' . $db->nameQuote('#__bsms_studies') . 'SET ' . $db->nameQuote('hits') . ' = ' . $db->nameQuote('hits') . ' + 1 ' . ' WHERE id = ' . (int) $pk);
        $db->query();
        return true;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        $app = JFactory::getApplication('site');

        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState('study.id', $pk);

        $offset = JRequest::getUInt('limitstart');
        $this->setState('list.offset', $offset);

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        // TODO: Tune these values based on other permissions.
        $user = JFactory::getUser();
        if ((!$user->authorise('core.edit.state', 'com_biblestudy')) && (!$user->authorise('core.edit', 'com_biblestudy'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }
    }

    /**
     * Method to get study data.
     *
     * @param	integer	The id of the study.
     * @since 7.1.0
     * @return	mixed	Menu item data object on success, false on failure.
     */
    public function &getItem($pk = null) {
        // Initialise variables.
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('study.id');
        if (!isset($this->_item[$pk])) {

            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select($this->getState(
                                'item.select', 's.*,CASE WHEN CHAR_LENGTH(s.alias) THEN CONCAT_WS(\':\', s.id, s.alias) ELSE s.id END as slug')
                );
                $query->from('#__bsms_studies AS s');
                //join over teachers
                $query->select('t.id AS tid, t.teachername AS teachername, t.title AS teachertitle, t.image, t.imagew, t.imageh, t.thumb, t.thumbw, t.thumbh');
                $query->join('LEFT', '#__bsms_teachers as t on s.teacher_id = t.id');
                //join over series
                $query->select('se.id AS sid, se.series_text, se.description as sdescription');
                $query->join('LEFT', '#__bsms_series as se on s.series_id = se.id');
                //join over message type
                $query->select('mt.id as mid, mt.message_type');
                $query->join('LEFT', '#__bsms_message_type as mt on s.messagetype = mt.id');
                //join over books
                $query->select('b.bookname as bname');
                $query->join('LEFT', '#__bsms_books as b on s.booknumber = b.booknumber');
                //join over locations
                $query->select('l.id as lid, l.location_text');
                $query->join('LEFT', '#__bsms_locations as l on s.location_id = l.id');
                //join over topics
                $query->select('group_concat(stp.id separator ", ") AS tp_id, group_concat(stp.topic_text separator ", ") as topic_text, group_concat(stp.params separator ", ") as topic_params');
                $query->join('LEFT', '#__bsms_studytopics as tp on s.id = tp.study_id');
                $query->join('LEFT', '#__bsms_topics as stp on stp.id = tp.topic_id');

                //join over media files
                $query->select('sum(m.plays) AS totalplays, sum(m.downloads) AS totaldownloads, m.id');
                $query->select('GROUP_CONCAT(DISTINCT m.id) as mids');
                $query->join('LEFT', '#__bsms_mediafiles AS m on s.id = m.study_id');

                // $query->join('LEFT','#__bsms_mediafiles as m ON study.id = m.study_id');
                $query->group('s.id');
                $query->where('s.id = ' . (int) $pk);
                $db->setQuery($query);
                $data = $db->loadObject();
                if ($error = $db->getErrorMsg()) {
                    throw new Exception($error);
                }

                if (empty($data)) {
                    return JError::raiseError(404, JText::_('JBS_CMN_STUDY_NOT_FOUND'));
                }
                // concat topic_text and concat topic_params do not fit, so translate individually
                $topic_text = getConcatTopicItemTranslated($data);
                $data->topic_text = $topic_text;
                $data->bname = JText::_($data->bname);
                $this->_item[$pk] = $data;
            } catch (JException $e) {
                if ($e->getCode() == 404) {
                    // Need to go thru the error handler to allow Redirect to work.
                    JError::raiseError(404, $e->getMessage());
                } else {
                    $this->setError($e);
                    $this->_item[$pk] = false;
                }
            }
        }
        return $this->_item[$pk];
    }

    /*
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success */

    protected function storecomment() {
        $row = $this->getTable('commentsedit');

        $data = JRequest::get('post');
        $data['comment_text'] = JRequest::getVar('comment_text', '', 'post', 'string', JREQUEST_ALLOWRAW);
        // Bind the form fields to the table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Store the table to the database
        if (!$row->store()) {
            $this->setError($row->getErrorMsg());
            return false;
        }

        return true;
    }

    public function getTemplate() {
        if (empty($this->_template)) {
            $templateid = JRequest::getVar('t', 1, 'get', 'int');
            $query = 'SELECT *'
                    . ' FROM #__bsms_templates'
                    . ' WHERE published = 1 AND id = ' . $templateid;
            $this->_template = $this->_getList($query);
        }
        return $this->_template;
    }

    public function getAdmin() {
        if (empty($this->_admin)) {
            $query = 'SELECT *'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_admin = $this->_getList($query);
        }
        return $this->_admin;
    }

//end class
}
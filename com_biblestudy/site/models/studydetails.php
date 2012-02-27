<?php

/**
 * @version $Id: studydetails.php 1 $
 * @package BibleStudy
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

class biblestudyModelstudydetails extends JModelItem {
/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_biblestudy.studydetails';
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
        $mainframe = JFactory::getApplication();
        $id = JRequest::getVar('id', 0, 'GET', 'INT');

        //end added from single view off of menu
        $array = JRequest::getVar('id', 0, '', 'array');
        $this->setId((int) $array[0]);

        ////set the default view search path
        $this->addTablePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'tables');
        $params = $mainframe->getPageParameters();
        $t = JRequest::getInt('t', 'get');
        if (!$t) {
            $t = 1;
        }
        jimport('joomla.html.parameter');
        $this->_id = $id;
        $template = $this->getTemplate();

        // Convert parameter fields to objects.
        $registry = new JRegistry;
        $registry->loadJSON($template[0]->params);
        $params = $registry;

        $this->hit();
    }

    function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
    }

    /**
     * Method to increment the hit counter for the study
     *
     * @access	public
     * @return	boolean	True on success
     * @since	1.5
     */
    function hit() {
        $db = JFactory::getDBO();
        $db->setQuery('UPDATE ' . $db->nameQuote('#__bsms_studies') . 'SET ' . $db->nameQuote('hits') . ' = ' . $db->nameQuote('hits') . ' + 1 ' . ' WHERE id = ' . $this->_id);
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
	protected function populateState()
	{
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
		$user		= JFactory::getUser();
		if ((!$user->authorise('core.edit.state', 'com_biblestudy')) &&  (!$user->authorise('core.edit', 'com_biblestudy'))){
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
    function &getItem($pk = null)
    {
        // Initialise variables.
	$pk = (!empty($pk)) ? $pk : (int) $this->getState('study.id');
        if (!isset($this->_item[$pk])) {

            try {
                    $db = $this->getDbo();
                    $query = $db->getQuery(true);
                    	$query->select($this->getState(
				'item.select', 's.*,CASE WHEN CHAR_LENGTH(#__bsms_studies.alias) THEN CONCAT_WS(\':\', s.id, s.alias) ELSE s.id END as slug, ')
                                );
                        $query->from('#__bsms_studies AS s');
                        //join over teachers
                        $query->select('t.id AS tid, t.teachername AS teachername, t.title AS teachertitle, t.image, t.imagew, t.imageh, t.thumb, t.thumbw, t.thumbh');
                        $query->join('LEFT','#__bsms_teachers as t on s.teacher_id = t.id');
                        //join over series
                        $query->select('se.id AS sid, se.series_text, se.description as sdescription');
                        $query->join('LEFT','s.series_id = se.id');
                        //join over message type
                        $query->select('mt.id as mid, mt.message_type');
                        $query->join('LEFT','#__bsms_messagetype as mt on s.messagetype = mt.id');
                        //join over books
                        $query->select('b.bookname as bname');
                        $query->join('LEFT','#__bsms_books s.booknumber = b.booknumber' );
                        //join over locations
                        $query->select('l.id as lid, l.location_text');
                        $query->join('LEFT','#__bsms_locations as l on s.location_id = l.id');
                        //join over topics
                        $query->select('group_concat(stp.id separator ", ") AS tp_id, group_concat(stp.topic_text separator ", ") as topic_text, group_concat(stp.params separator ", ") as topic_params');
                        $query->join('LEFT','#__bsms_studytopics as tp on s.id = tp.study_id');
                        $query->join('LEFT','#__bsms_topics as stp on stp.id = tp.topic_id');
                        //join over media files
                        $query->select('sum(m.plays) AS totalplays, sum(m.downloads) AS totaldownloads, m.id');
                        $query->join('LEFT','#__bsms_mediafiles AS m on s.id = m.study_id');
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
                       $this->_item[$pk] = $data;
                    }
                catch (JException $e)
			{
				if ($e->getCode() == 404) {
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage());
				}
				else {
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
                    
            }
        return $this->_item[$pk];
    }
    
    function &getData() {
        // Load the data
        if (empty($this->_data)) {

            $id = JRequest::getVar('id', 0, 'GET', 'INT');
            $query = 'SELECT #__bsms_studies.*, CASE WHEN CHAR_LENGTH(#__bsms_studies.alias) THEN CONCAT_WS(\':\', #__bsms_studies.id, #__bsms_studies.alias) ELSE #__bsms_studies.id END as slug, '
                    . ' #__bsms_teachers.id AS tid, #__bsms_teachers.teachername AS teachername, '
                    . ' #__bsms_teachers.title AS teachertitle, '
                    . ' #__bsms_teachers.image, #__bsms_teachers.imagew, #__bsms_teachers.imageh, #__bsms_teachers.thumb, '
                    . ' #__bsms_teachers.thumbw, #__bsms_teachers.thumbh,'
                    . ' #__bsms_series.id AS sid, #__bsms_series.series_text AS series_text, #__bsms_series.description AS sdescription, '
                    . ' #__bsms_message_type.id AS mid, #__bsms_message_type.message_type AS message_type, '
                    . ' #__bsms_books.bookname AS bname, #__bsms_locations.id as lid, #__bsms_locations.location_text,'
                    . ' group_concat(#__bsms_topics.id separator ", ") AS tp_id, group_concat(#__bsms_topics.topic_text separator ", ") as topic_text, group_concat(#__bsms_topics.params separator ", ") as topic_params,'
                    . ' sum(#__bsms_mediafiles.plays) AS totalplays, sum(#__bsms_mediafiles.downloads) AS totaldownloads, #__bsms_mediafiles.study_id'
                    . ' FROM #__bsms_studies'
                    . ' LEFT JOIN #__bsms_books ON (#__bsms_studies.booknumber = #__bsms_books.booknumber)'
                    . ' LEFT JOIN #__bsms_teachers ON (#__bsms_studies.teacher_id = #__bsms_teachers.id)'
                    . ' LEFT JOIN #__bsms_series ON (#__bsms_studies.series_id = #__bsms_series.id)'
                    . ' LEFT JOIN #__bsms_message_type ON (#__bsms_studies.messagetype = #__bsms_message_type.id)'
                    . ' LEFT JOIN #__bsms_studytopics ON (#__bsms_studies.id = #__bsms_studytopics.study_id)'
                    . ' LEFT JOIN #__bsms_topics ON (#__bsms_topics.id = #__bsms_studytopics.topic_id)'
                    . ' LEFT JOIN #__bsms_locations ON (#__bsms_studies.location_id = #__bsms_locations.id)'
                    . ' LEFT JOIN #__bsms_mediafiles ON (#__bsms_studies.id = #__bsms_mediafiles.study_id)'
                    . '  WHERE #__bsms_studies.id = ' . $id
                    . ' GROUP BY #__bsms_studies.id';
            //.$this->_id.;
            $this->_db->setQuery($query);
            $result = $this->_db->loadObject();

            // concat topic_text and concat topic_params do not fit, so translate individually
            $topic_text = getConcatTopicItemTranslated($result);
            $result->topic_text = $topic_text;
            $result->bname = JText::_($result->bname);

            $this->_data = $result;
        }
        return $this->_data;
    }

    /*
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success */

    function storecomment() {
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

    function getTemplate() {
        if (empty($this->_template)) {
            $templateid = JRequest::getVar('t', 1, 'get', 'int');
            $query = 'SELECT *'
                    . ' FROM #__bsms_templates'
                    . ' WHERE published = 1 AND id = ' . $templateid;
            $this->_template = $this->_getList($query);
        }
        return $this->_template;
    }

    function getAdmin() {
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
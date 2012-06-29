<?php

/**
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * class biblestudyModelmessage extends JModelAdmin
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');
jimport('joomla.html.parameter');
require_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php';
include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

//require_once JPATH_ADMINISTRATOR.'/components/com_biblestudy/models/message.php';
/**
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyModelmessage extends JModelAdmin {

    var $_admin;

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @since	1.6
     */
    protected function populateState() {
        $app = JFactory::getApplication('site');
        // Adjust the context to support modal layouts.
        if ($layout = JRequest::getVar('layout')) {
            $this->context .= '.' . $layout;
        }
        // Load state from the request. We use a_id to avoid collisions with the router
        $pks = JRequest::getInt('a_id');
        $this->pks = $pks;
        $this->setState('message.id', $pks);
    }

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param       array   $data   An array of input data.
     * @param       string  $key    The name of the key for the primary key.
     *
     * @return      boolean
     * @since       1.6
     */
    protected function allowEdit($data = array(), $key = 'id') {
        // Check specific edit permission then general edit permission.
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.studiesedit.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     *
     * @param type $study_id
     * @param type $topic_id
     * @return boolean
     */
    function isDuplicate($study_id, $topic_id) {
        $db = & JFactory::getDBO();
        $query = 'select * from #__bsms_studytopics where study_id = ' . $study_id . ' and topic_id = ' . $topic_id;

        $db->setQuery($query);

        $tresult = $db->loadObject();

        if (empty($tresult)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Method to delete record(s)
     *
     * @access	public
     * @return	boolean	True on success
     */
    function delete() {
        $cids = JRequest::getVar('cid', array(0), 'post', 'array');

        $row = & $this->getTable();

        if (count($cids)) {
            foreach ($cids as $cid) {
                if (!$row->delete($cid)) {
                    $this->setError($row->getErrorMsg());
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Gets all the topics associated with a particular study
     *
     * @return type JSON Object containing the topics
     * @since 7.0.1
     */
    function getTopics() {
        // do search in case of present study only, suppress otherwise
        $translatedList = array();
        if (JRequest::getVar('id', 0, null, 'int') > 0) {
            $db = $this->getDbo();
            $query = $db->getQuery(true);

            $query->select('topic.id, topic.topic_text, topic.params AS topic_params');
            $query->from('#__bsms_studytopics AS studytopics');

            $query->join('LEFT', '#__bsms_topics AS topic ON topic.id = studytopics.topic_id');
            $query->where('studytopics.study_id = ' . JRequest::getVar('id', 0, null, 'int'));

            $db->setQuery($query->__toString());
            $topics = $db->loadObjectList();
            if ($topics) {
                foreach ($topics as $topic) {
                    $text = getTopicItemTranslated($topic);
                    $translatedList[] = array('id' => $topic->id, 'name' => $text);
                }
            }
        }
        return json_encode($translatedList);
    }

    /**
     * Gets all topics available
     *
     * @return type JSON Object containing the topics
     * @since 7.0.1
     */
    function getAlltopics() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('topic.id, topic.topic_text, topic.params AS topic_params');
        $query->from('#__bsms_topics AS topic');

        $db->setQuery($query->__toString());
        $topics = $db->loadObjectList();
        $translatedList = array();
        if ($topics) {
            foreach ($topics as $topic) {
                $text = getTopicItemTranslated($topic);
                $translatedList[] = array('id' => $topic->id, 'name' => $text);
            }
        }
        return json_encode($translatedList);
    }

    /**
     * Method to get Admin settings
     *
     * @return array
     */
    function getAdmin() {
        if (empty($this->_admin)) {
            $query = 'SELECT *'
                    . ' FROM #__bsms_admin'
                    . ' WHERE id = 1';
            $this->_admin = $this->_getList($query);
        }
        return $this->_admin;
    }

    /**
     * Returns a list of mediafiles associated with this study
     *
     * @since   7.0
     */
    public function getMediaFiles() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('mediafile.id, mediafile.filename, mediafile.createdate');
        $query->from('#__bsms_mediafiles AS mediafile');
        $query->where('mediafile.study_id = ' . (int) $this->getItem()->id);
        $query->order('mediafile.createdate DESC');

        $db->setQuery($query->__toString());
        return $db->loadObjectList();
    }

    /**
     * Overrides the JModelAdmin save routine to save the topics(tags)
     * @param type $data
     * @since 7.0.1
     * @todo This may need to be optimized
     */
    public function save($data) {
        $pks = JRequest::getInt('a_id'); 
        if ($pks) {
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->clear();
            $query->update('#__bsms_studies');
            $query->set(' studydate = '.$db->Quote($data['studydate']));
            $query->set(' teacher_id = '.$db->Quote($data['teacher_id']));
            $query->set(' booknumber = '.$db->Quote($data['booknumber']));
            $query->set(' chapter_begin = '.$db->Quote($data['chapter_begin']));
            $query->set(' chapter_end = '.$db->Quote($data['chapter_end']));
            $query->set(' verse_begin = '.$db->Quote($data['verse_begin']));
            $query->set(' secondary_reference = '.$db->Quote($data['secondary_reference']));
            $query->set(' booknumber2 = '.$db->Quote($data['booknumber2']));
            $query->set(' chapter_begin2 = '.$db->Quote($data['chapter_begin2']));
            $query->set(' chapter_end2 = '.$db->Quote($data['chapter_end2']));
            $query->set(' verse_begin2 = '.$db->Quote($data['verse_begin2']));
            $query->set(' prod_dvd = '.$db->Quote($data['prod_dvd']));
            $query->set(' prod_cd = '.$db->Quote($data['prod_cd']));
            $query->set(' server_dvd = '.$db->Quote($data['server_dvd']));
            $query->set(' server_cd = '.$db->Quote($data['server_cd']));
            $query->set(' image_cd = '.$db->Quote($data['image_cd']));
            $query->set(' image_dvd = '.$db->Quote($data['image_dvd']));
            $query->set(' studytext2 = '.$db->Quote($data['studytext2']));
            $query->set(' comments = '.$db->Quote($data['comments']));
            $query->set(' hits = '.$db->Quote($data['hits']));
            $query->set(' user_id = '.$db->Quote($data['user_id']));
            $query->set(' user_name = '.$db->Quote($data['user_name']));
            $query->set(' show_level = '.$db->Quote($data['show_level']));
            $query->set(' location_id = '.$db->Quote($data['location_id']));
            $query->set(' studytitle = '.$db->Quote($data['studytitle']));
            $query->set(' alias = '.$db->Quote($data['alias']));
            $query->set(' studyintro = '.$db->Quote($data['studyintro']));
            $query->set(' media_hours = '.$db->Quote($data['media_hours']));
            $query->set(' media_minutes = '.$db->Quote($data['media_minutes']));
            $query->set(' media_seconds = '.$db->Quote($data['media_seconds']));
            $query->set(' messagetype = '.$db->Quote($data['messagetype']));
            $query->set(' series_id = '.$db->Quote($data['series_id']));
            $query->set(' topics_id = '.$db->Quote($data['topics_id']));
            $query->set(' studytext = '.$db->Quote($data['studytext']));
            $query->set(' thumbnailm = '.$db->Quote($data['thumbnailm']));
            $query->set(' thumbhm = '.$db->Quote($data['thumbhm']));
            $query->set(' thumbwm = '.$db->Quote($data['thumbwm']));
            $query->set(' params = '.$db->Quote($data['params']));
            $query->set(' published = '.$db->Quote($data['published']));
            $query->set(' asset_id = '.$db->Quote($data['asset_id']));
            $query->set(' access = '.$db->Quote($data['access']));
            $query->set(' ordering = '.$db->Quote($data['ordering']));
            $query->set(' language = '.$db->Quote($data['language']));
            $query->where(' id ='. (int)$pks.' LIMIT 1');
            $db->setQuery((string)$query); 
            if (!$db->query()) {
            JError::raiseError(500, $db->getErrorMsg());
        	return false;
        } else {
            $this->setTopics($pks, $data);	
            return true;
		} 
        }
        return parent::save($data);
    }

    /**
     * Routine to save the topics(tags)
     * @param type $data from post
     * @param type $pks is the id of the record being saved
     * @since 7.0.2
     * @todo This may need to be optimized
     */
    public function setTopics($pks, $data) {

        if (empty($pks)) {
            $this->setError(JText::_('JBS_STY_ERROR_TOPICS_UPDATE'));
            return false;
        }

        $db = $this->getDbo();
        $query = $db->getQuery(true);

        //Clear the tags first
        $query->delete();
        $query->from('#__bsms_studytopics');
        $query->where('study_id = ' . $pks);
        $db->setQuery($query->__toString());
        if (!$db->query()) {
            throw new Exception($db->getErrorMsg());
        }
        $query->clear();

        //Add all the tags back
        if ($data['topics']) {
            $topics = explode(",", $data['topics']);
            $topics_sql = array();
            foreach ($topics as $topic)
                $topics_sql[] = '(' . $topic . ', ' . $pks . ')';
            $query->insert('#__bsms_studytopics (topic_id, study_id) VALUES ' . implode(',', $topics_sql));
            $db->setQuery($query->__toString());
            if (!$db->query()) {
                throw new Exception($db->getErrorMsg());
            }
        }
    }

    /**
     * Get the form data
     *
     * @param <Array> $data
     * @param <Boolean> $loadData
     * @return <type>
     * @since 7.0
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_biblestudy.message', 'message', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     *
     * @return array
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.message.data', array());
        if (empty($data))
            $data = $this->getItem();

        return $data;
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	1.6
     */
    public function getTable($type = 'Message', $prefix = 'Table', $config = array()) {
        JTable::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'tables');
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param	JTable	$table
     *
     * @return	void
     * @since	1.6
     */
    protected function prepareTable(&$table) {
        jimport('joomla.filter.output');
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        $table->studytitle = htmlspecialchars_decode($table->studytitle, ENT_QUOTES);
        $table->alias = JApplication::stringURLSafe($table->alias);

        if (empty($table->alias)) {
            $table->alias = JApplication::stringURLSafe($table->studytitle);
        }

        if (empty($table->id)) {
            // Set the values
            //$table->created	= $date->toMySQL();
            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__bsms_studies');
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        } else {
            // Set the values
            //$table->modified	= $date->toMySQL();
            //$table->modified_by	= $user->get('id');
        }
    }

    /**
     * Method to get article data.
     *
     * @param	integer	The id of the article.
     *
     * @return	mixed	Content item data object on success, false on failure.
     */
    public function getItem($itemId = null) {
        // Initialise variables.
        $itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('message.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($itemId);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }
        $properties = $table->getProperties(1);
        $value = JArrayHelper::toObject($properties, 'JObject');
        return $value;
    }

}
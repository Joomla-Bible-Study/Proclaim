<?php

/**
 * Message model
 * @package BibleStudy.Admin
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

require_once JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'biblestudy.php';
include_once (JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'translated.php');

/**
 * Message model class
 * @package BibleStudy.Admin
 * @since 7.0.0
 */
class BiblestudyModelMessage extends JModelAdmin {

    /**
     * Admin
     * @var string
     */
    var $_admin;

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
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.message.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
     */
    public function store() {
        // fix up special html fields

        $row = & $this->getTable();

        $data = JRequest::get('post');

        //Allows HTML content to come through to the database row
        $data['studytext'] = JRequest::getVar('studytext', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $data['studyintro'] = str_replace('"', "'", $data['studyintro']);
        $data['studynumber'] = str_replace('"', "'", $data['studynumber']);
        $data['secondary_reference'] = str_replace('"', "'", $data['secondary_reference']);

        foreach ($data['scripture'] as $scripture) {
            if (!$data['text'][key($data['scripture'])] == '') {
                $scriptures[] = $scripture . ' ' . $data['text'][key($data['scripture'])];
            }
            next($data['scripture']);
        }
        $data['scripture'] = implode(';', $scriptures);

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
        //Checks to make sure a valid date field has been entered
        if (!$row->studydate) {
            $row->studydate = date('Y-m-d H:i:s');
        }
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        //Get Tags
        $vTags = JRequest::getVar('topic_tags', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $iTags = explode(",", $vTags);

        JTable::addIncludePath(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_biblestudy' . DIRECTORY_SEPARATOR . 'tables');

        foreach ($iTags as $aTag) {
            if (is_numeric($aTag)) {
                //It's an existing tag.  Add it
                if ($aTag != "") {

                    $tagRow = & JTable::getInstance('studytopics', 'Table');

                    $isDup = $this->isDuplicate($row->id, $aTag);

                    if (!$isDup) {
                        $tagRow->study_id = $row->id;
                        $tagRow->topic_id = $aTag;

                        if (!$tagRow->store()) {
                            $this->setError($this->_db->getErrorMsg());
                            return false;
                        }
                    }
                }
            } else {
                //It's a new tag.  Gotta insert it into the Topics table.
                if ($aTag != "") {
                    $topicRow = & JTable::getInstance('topic', 'Table');
                    $tempText = $aTag;
                    $tempText = str_replace("0_", "", $tempText);
                    $topicRow->topic_text = $tempText;
                    $topicRow->published = 1;
                    if (!$topicRow->store()) {
                        $this->setError($this->_db->getErrorMsg());
                        return false;
                    }

                    //Gotta somehow make sure this isn't a duplicate...
                    $tagRow = & JTable::getInstance('studytopics', 'Table');
                    $tagRow->study_id = $row->id;
                    $tagRow->topic_id = $topicRow->id;

                    $isDup = $this->isDuplicate($row->id, $aTag);

                    if (!$isDup) {
                        if (!$tagRow->store()) {
                            $this->setError($this->_db->getErrorMsg());
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Duplicate Check
     *
     * @param int $study_id
     * @param int $topic_id
     * @return boolean
     */
    public function isDuplicate($study_id, $topic_id) {
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
     * Gets all the topics associated with a particular study
     *
     * @return type JSON Object containing the topics
     * @since 7.0.1
     */
    public function getTopics() {
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
    public function getAlltopics() {
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
     * Get admin info
     * @return object
     */
    public function getAdmin() {
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
     * @return object
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
     * @param string $data
     * @since 7.0.1
     * @todo This may need to be optimized
     */
    public function save($data) {
        if (parent::save($data)) {
            $this->setTopics($this->getState($this->getName() . '.id'), $data);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Routine to save the topics(tags)
     * @param int $pks is the id of the record being saved
     * @param string $data from post
     * @since 7.0.2
     * @todo This may need to be optimized
     */
    public function setTopics($pks, $data) {

        if (empty($pks)) {
            $this->setError(JText::_('COM_CONTENT_NO_ITEM_SELECTED'));
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
     * @param array $data
     * @param boolean $loadData
     * @return string
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
     * Method to get the data that should be injected in the form.
     *
     * @return  array    The default data is an empty array.
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

            // Set ordering to the last item if not set
            if (empty($table->ordering)) {
                $db = JFactory::getDbo();
                $db->setQuery('SELECT MAX(ordering) FROM #__bsms_studies');
                $max = $db->loadResult();

                $table->ordering = $max + 1;
            }
        }
    }

    /**
     * Custom clean the cache of com_biblestudy and biblestudy modules
     * @param string $group
     * @param int $client_id
     *
     * @since	1.6
     */
    protected function cleanCache($group = null, $client_id = 0) {
        parent::cleanCache('com_biblestudy');
        parent::cleanCache('mod_biblestudy');
    }

}
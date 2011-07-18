<?php

/**
 * @version     $Id: studiesedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

jimport('joomla.application.component.modeladmin');

require_once JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'biblestudy.php';
include_once (JPATH_COMPONENT_ADMINISTRATOR . DS . 'helpers' . DS . 'translated.php');

class biblestudyModelstudiesedit extends JModelAdmin {

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
        return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.studiesedit.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    function __construct() {
        parent::__construct();

        $admin = $this->getAdmin();
        $this->_admin_params = new JParameter($admin[0]->params);
        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int) $array[0]);
    }

    function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
        $this->_admin = null;
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
     */
    function store() {
        //$post           = JRequest::get( 'post' );
        // fix up special html fields

        $row = & $this->getTable();

        $data = JRequest::get('post');

        //Allows HTML content to come through to the database row
        $data['studytext'] = JRequest::getVar('studytext', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $data['studyintro'] = str_replace('"', "'", $data['studyintro']);
        $data['studynumber'] = str_replace('"', "'", $data['studynumber']);
        $data['secondary_reference'] = str_replace('"', "'", $data['secondary_reference']);
        //  $data['studytext'] = JRequest::getVar('studytext', '', 'post', 'string', JREQUEST_ALLOWRAW);
        //	$msg = $data['show_level'];

        foreach ($data['scripture'] as $scripture) {
            if (!$data['text'][key($data['scripture'])] == '') {
                $scriptures[] = $scripture . ' ' . $data['text'][key($data['scripture'])];
            }
            next($data['scripture']);
        }
        $data['scripture'] = implode(';', $scriptures);
        // Added since Joomla 1.6 to implode show_level if array
        if (is_array($data['show_level'])) {
            // $data['show_level'] = implode(",", $data['show_level']);
        }
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
        if (!$row->studydate)
            $row->studydate = date('Y-m-d H:i:s');
        //if ($row->description) { $row->description = str_replace('"',"'",$row->description); }
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
            //$this->setError( $row->getErrorMsg() );
            //return false;
        }

        //Get Tags
        $vTags = JRequest::getVar('topic_tags', '', 'post', 'string', JREQUEST_ALLOWRAW);
        $iTags = explode(",", $vTags);

        JTable::addIncludePath(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_biblestudy' . DS . 'tables');

        //$tagRow->load( 1 );

        foreach ($iTags as $aTag) {
            if (is_numeric($aTag)) {
                //It's an existing tag.  Add it
                if ($aTag != "") {

                    $tagRow = & JTable::getInstance('studytopics', 'Table');

                    //dump ($isDup, "D");

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
                    $topicRow = & JTable::getInstance('topicsedit', 'Table');
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
     * @return type JSON Object containinng the topics
     * @since 7.0.1
     */
    function getTopics() {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('topic.id, topic.topic_text AS name');
        $query->from('#__bsms_studytopics AS studytopics');

        $query->join('LEFT', '#__bsms_topics AS topic ON topic.id = studytopics.topic_id');
        $query->where('studytopics.study_id = '.JRequest::getVar('id', 0, null, 'int'));
        
        $db->setQuery($query->__toString());
        return json_encode($db->loadAssocList());
    }
    function getBooks() {
        die('biblestudyModelstudiesedit.getBooks is no more used');
//        $query = 'SELECT booknumber AS value, bookname AS text'
//                . ' FROM #__bsms_books'
//                . ' WHERE published = 1'
//                . ' ORDER BY booknumber';
//        $this->_db->setQuery($query);
//        $books = getTopicItemsTranslated($this->_getList($query));  // santon wrong call??
//        return $books;
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
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        //Clear the tags first
        $query->delete();
        $query->from('#__bsms_studytopics');
        $query->where('study_id = '.JRequest::getInt('id', 0));
        $db->setQuery($query->__toString());
        $db->query();
        $query->clear();
        
        //Add all the tags back
        $topics = explode(",", $data['topics']);
        $topics_sql = array();
        foreach ($topics as $topic)
            $topics_sql[] = '('.$topic.', '.  JRequest::getInt ('id', 0).')';
        
        $query->insert('#__bsms_studytopics (topic_id, study_id) VALUES '.  implode(',', $topics_sql));
        
        $db->setQuery($query->__toString());
        $db->query();
        return parent::save($data);
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
        $form = $this->loadForm('com_biblestudy.studiesedit', 'studiesedit', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }

        return $form;
    }


    /**
     *
     * @return <type>
     * @since   7.0
     */
    protected function loadFormData() {
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.studiesedit.data', array());
        if (empty($data))
            $data = $this->getItem();
        //  $data->show_level = explode(',', $data->show_level);

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
    public function getTable($type = 'studiesedit', $prefix = 'Table', $config = array()) {
        return JTable::getInstance($type, $prefix, $config);
    }

}

?>

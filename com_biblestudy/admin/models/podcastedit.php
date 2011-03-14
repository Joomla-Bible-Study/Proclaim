<?php

/**
 * @version     $Id: podcastedit.php 1466 2011-01-31 23:13:03Z bcordis $
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();

    jimport('joomla.application.component.modeladmin');

    abstract class modelClass extends JModelAdmin {
        
    }

class biblestudyModelPodcastedit extends modelClass {

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

        $array = JRequest::getVar('cid', 0, '', 'array');
        $this->setId((int) $array[0]);
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
        protected function allowEdit($data = array(), $key = 'id')
        {
                // Check specific edit permission then general edit permission.
                return JFactory::getUser()->authorise('core.edit', 'com_biblestudy.podcastedit.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
        }

    function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
        $this->_episodes = null;
        $this->_podinfo = null;
        $this->_admin = null;
    }

    function &getData() {
        // Load the data
        $admin = $this->getAdmin();
        if (empty($this->_data)) {
            $query = ' SELECT * FROM #__bsms_podcast ' .
                    '  WHERE id = ' . $this->_id;
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            $this->_data->title = null;
            $this->_data->website = null;
            $this->_data->description = null;
            $this->_data->image = null;
            $this->_data->imageh = null;
            $this->_data->imagew = null;
            $this->_data->author = null;
            $this->_data->podcastimage = null;
            $this->_data->podcastsummary = null;
            $this->_data->podcastsubtitle = null;
            $this->_data->podcastsearch = null;
            $this->_data->filename = null;
            $this->_data->language = null;
            $this->_data->podcastname = null;
            $this->_data->editor_name = null;
            $this->_data->editor_email = null;
            $this->_data->podcastlimit = null;
            $this->_data->episodetitle = null;
            $this->_data->custom = null;
            $this->_data->detailstemplateid = null;
            $this->_data->published = 1;
        }
        return $this->_data;
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
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

    function store() {
        $row = & $this->getTable();

        $data = JRequest::get('post');

        // Bind the form fields to the hello table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Store the web link table to the database
        if (!$row->store()) {
            $this->setError($this->_db->getErrorMsg());
//			$this->setError( $row->getErrorMsg() );
            return false;
        }

        return true;
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

    function legacyPublish($cid = array(), $publish = 1) {

        if (count($cid)) {
            $cids = implode(',', $cid);

            $query = 'UPDATE #__bsms_podcast'
                    . ' SET published = ' . intval($publish)
                    . ' WHERE id IN ( ' . $cids . ' )'

            ;
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
    }

    function podinfo($cid = array()) {
        if (empty($this->_podinfo)) {
            $query = 'SELECT * FROM #__bsms_podcast WHERE #__bsms_podcast.id = ' . $this->_id;
            $this->_db->setQuery($query);
            $this->_podinfo = $this->_db->loadObject();
        }
        if (!$this->_podinfo) {
            $this->_podinfo = new stdClass();
            $this->_podinfo->id = 0;
            $this->_podinfo->title = null;
            $this->_podinfo->website = null;
            $this->_podinfo->description = null;
            $this->_podinfo->image = null;
            $this->_podinfo->imageh = null;
            $this->_podinfo->imagew = null;
            $this->_podinfo->author = null;
            $this->_podinfo->podcastimage = null;
            $this->_podinfo->podcastsummary = null;
            $this->_podinfo->podcastsubtitle = null;
            $this->_podinfo->podcastsearch = null;
            $this->_podinfo->filename = null;
            $this->_podinfo->language = null;
            $this->_podinfo->podcastname = null;
            $this->_podinfo->editor_name = null;
            $this->_podinfo->editor_email = null;
            $this->_podinfo->podcastlimit = null;
            $this->_podinfo->published = 0;
            $this->_podinfo->detailstemplateid = null;
        }
        return $this->_podinfo;
    }

    function &episodes($cid = array()) {
        //$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );
        $podcastlimit = JRequest::getVar('podcastslimit');
        if (empty($this->_episodes)) {
            $query = 'SELECT p.id AS pid, p.podcastlimit,'
                    . ' mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id, mf.published AS mfpub, mf.createdate,'
                    . ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin, s.chapter_end, s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
                    . ' s.media_hours, s.media_minutes, s.media_seconds,'
                    . ' sr.id AS srid, sr.server_path,'
                    . ' f.id AS fid, f.folderpath,'
                    . ' t.id AS tid, t.teachername,'
                    . ' b.id AS bid, b.booknumber AS bnumber, b.bookname,'
                    . ' mt.id AS mtid, mt.mimetype'
                    . ' FROM #__bsms_mediafiles AS mf'
                    . ' LEFT JOIN #__bsms_studies AS s ON (s.id = mf.study_id)'
                    . ' LEFT JOIN #__bsms_servers AS sr ON (sr.id = mf.server)'
                    . ' LEFT JOIN #__bsms_folders AS f ON (f.id = mf.path)'
                    . ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
                    . ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
                    . ' LEFT JOIN #__bsms_mimetype AS mt ON (mt.id = mf.mime_type)'
                    . ' LEFT JOIN #__bsms_podcast AS p ON (p.id = mf.podcast_id)'
                    . ' WHERE mf.podcast_id = ' . $this->_id . ' ORDER BY createdate DESC'; // LIMIT '.$podcastlimit;
            $this->_db->setQuery($query);
            $this->_episodes = $this->_db->loadObject();
        }
        if (!$this->_episodes) {
            $this->_episodes = new stdClass();
            $this->_episodes->id = 0;
            $this->_episodes->study_id = null;
            $this->_episodes->media_image = null;
            $this->_episodes->server = null;
            $this->_episodes->path = null;
            $this->_episodes->special = null;
            $this->_episodes->filename = null;
            $this->_episodes->size = null;
            $this->_episodes->mime_type = null;
            $this->_episodes->podcast_id = null;
            $this->_episodes->internal_viewer = null;
            $this->_episodes->ordering = null;
            $this->_episodes->createdate = null;
            $this->_episodes->published = 0;
        }
        return $this->_episodes;
    }

    function getTemplate() {
        if (empty($this->_template)) {
            //$templateid = JRequest::getVar('templatemenuid',1,'get', 'int');
            //dump ($templateid, 'templateid: ');
            $query = 'SELECT id as value, title as text, published'
                    . ' FROM #__bsms_templates'
                    . ' WHERE published = 1 ORDER BY id ASC';
            $this->_template = $this->_getList($query);
            //dump ($this->_template, 'this->_template');
        }
        return $this->_template;
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
        $form = $this->loadForm('com_biblestudy.podcastedit', 'podcastedit', array('control' => 'jform', 'load_data' => $loadData));
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
        $data = JFactory::getApplication()->getUserState('com_biblestudy.edit.podcastedit.data', array());
        if (empty($data))
            $data = $this->getItem();
        
        return $data;
    }

}

?>

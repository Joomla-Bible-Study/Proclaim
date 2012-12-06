<?php

/**
 * MediaPlayer Model
 * @package BibleStudy.Site
 * @Copyright (C) 2007 - 2011 Joomla Bible Study Team All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.JoomlaBibleStudy.org
 * */
//No Direct Access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Model class for MediaPlayer
 * @package BibleStudy.Site
 * @since 7.0.0
 */
class biblestudyModelmediaplayer extends JModelLegacy {

    /**
     * Constructor
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     *
     * @since   11.1
     */
    public function __construct($config = array()) {
        parent::__construct($config);
        $input = new JInput;
        $array = $input->get('cid', 0, 'array');
        $this->setId((int) $array[0]);
    }

    /**
     * Set ID
     * @param int $id
     */
    public function setId($id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_data = null;
    }

    /**
     * Get Data
     * @return object
     */
    public function &getData() {
        // Load the data
        if (empty($this->_data)) {
            $query = ' SELECT mf.id AS mfid, mf.study_id, mf.server, mf.path, mf.filename, mf.size, mf.mime_type, mf.podcast_id, mf.published AS mfpub, mf.createdate,'
                    . ' s.id AS sid, s.studydate, s.teacher_id, s.booknumber, s.chapter_begin, s.verse_begin, s.chapter_end, s.verse_end, s.studytitle, s.studyintro, s.published AS spub,'
                    . ' s.media_hours, s.media_minutes, s.media_seconds, s.series_id, s.studynumber, s.studytext, s.booknumber2, s.chapter_begin2, s.chapter_end2, s.verse_begin2, s.verse_end2,'
                    . ' sr.id AS srid, sr.server_path,'
                    . ' f.id AS fid, f.folderpath,'
                    . ' t.id AS tid, t.teachername,'
                    . ' b.id AS bid, b.booknumber AS bnumber, b.bookname,'
                    . ' st.id AS stid, st.series_text AS stext,'
                    . ' mt.id AS mtid, mt.mimetype'
                    . ' FROM #__bsms_mediafiles AS mf'
                    . ' LEFT JOIN #__bsms_studies AS s ON (s.id = mf.study_id)'
                    . ' LEFT JOIN #__bsms_servers AS sr ON (sr.id = mf.server)'
                    . ' LEFT JOIN #__bsms_folders AS f ON (f.id = mf.path)'
                    . ' LEFT JOIN #__bsms_books AS b ON (b.booknumber = s.booknumber)'
                    . ' LEFT JOIN #__bsms_teachers AS t ON (t.id = s.teacher_id)'
                    . ' LEFT JOIN #__bsms_mimetype AS mt ON (mt.id = mf.mime_type)'
                    . ' LEFT JOIN #__bsms_series AS st ON (st.id = s.series_id)'
                    . ' WHERE mf.id = ' . $this->_id;

            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            //TF added these
            $this->_data->published = 0;
            $this->_data->media_image = null;
            $this->_data->server = null;
            $this->_data->path = null;
            $this->_data->special = null;
            $this->_data->filename = null;
            $this->_data->size = null;
            $this->_data->podcast_id = null;
            $this->_data->internal_viewer = null;
            $this->_data->mediacode = null;
            $this->_data->ordering = null;
            $this->_data->study_id = null;
            $this->_data->createdate = null;
        }
        return $this->_data;
    }

    /**
     * Method to store a record
     *
     * @access	public
     * @return	boolean	True on success
     */
    public function store() {
        $row = $this->getTable();
        $input = new JInput;
        $data = $input->post;

        // Bind the form fields to the  table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Make sure the  record is valid
        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Store the table to the database
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
    public function delete() {
        $input = new JInput;
        $cids = $input('cid', array(0), 'array');

        $row = $this->getTable();

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
     * Publish record
     * @param int $cid
     * @param int $publish
     * @return boolean
     */
    public function publish($cid = array(), $publish = 1) {

        if (count($cid)) {
            $cids = implode(',', $cid);

            $query = 'UPDATE #__bsms_mediafiles'
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

    /**
     * Method to move a mediafile listing
     *
     * @param string $direction
     *
     * @access	public
     * @return	boolean	True on success
     * @since	1.5
     */
    public function move($direction) {
        $row = $this->getTable();
        if (!$row->load($this->_id)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!$row->move($direction, ' study_id = ' . (int) $row->study_id . ' AND published >= 0 ')) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    /**
     * Method to move a mediafile listing
     *
     * @param array $cid
     * @param string $order
     *
     * @access	public
     * @return	boolean	True on success
     * @since	1.5
     */
    public function saveorder($cid = array(), $order) {
        $row = $this->getTable();
        $groupings = array();

        // update ordering values
        for ($i = 0; $i < count($cid); $i++) {
            $row->load((int) $cid[$i]);
            // track categories
            $groupings[] = $row->study_id;

            if ($row->ordering != $order[$i]) {
                $row->ordering = $order[$i];
                if (!$row->store()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }
        }

        // execute updateOrder for each parent group
        $groupings = array_unique($groupings);
        foreach ($groupings as $group) {
            $row->reorder('study_id = ' . (int) $group);
        }

        return true;
    }

}
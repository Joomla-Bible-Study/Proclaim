<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class biblestudyModelmediafilesedit extends JModel {

	var $_id;
	var $_data;

	function __construct() {
		parent::__construct();

		$this->setId(JRequest::getInt('cid', 0));
	}

	function setId($id) {
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	function getStudy() {
		$query = 'SELECT id, studytitle, studydate FROM #__bsms_studies ORDER BY id DESC LIMIT 1';
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}

	function getStudies() {
		$query = "SELECT id AS value, CONCAT(studytitle,' - ', date_format(studydate, '%a %b %e %Y'), ' - ', studynumber) AS text FROM #__bsms_studies ORDER BY studydate DESC";
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getServers() {
		$query = 'SELECT id AS value, server_path AS text, published'
		. ' FROM #__bsms_servers'
		. ' WHERE published = 1'
		. ' ORDER BY server_path';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getFolders() {
		$query = 'SELECT id AS value, folderpath AS text, published'
		. ' FROM #__bsms_folders'
		. ' WHERE published = 1'
		. ' ORDER BY folderpath';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getPodcasts() {
		$query = 'SELECT id AS value, title AS text FROM #__bsms_podcast WHERE published = 1 ORDER BY title ASC';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getMediaImages() {
		$query = 'SELECT id AS value, media_image_name AS text, published'
		. ' FROM #__bsms_media'
		. ' WHERE published = 1'
		. ' ORDER BY media_image_name';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getMimeTypes() {
		$query = 'SELECT id AS value, mimetext AS text, published FROM #__bsms_mimetype WHERE published = 1 ORDER BY id ASC';
		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	function getOrdering() {
		$query = 'SELECT ordering AS value, ordering AS text'
		. ' FROM #__bsms_mediafiles'
		. ' WHERE study_id = '.$this->_id
		. ' ORDER BY ordering'
		;
		return $query;
	}
	
	function &getData() {
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT * FROM #__bsms_mediafiles '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
			//TF added these
			$this->_data->published = 1;
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
			$this->_data->link_type = null;
			$this->_date->hits = null;
			$this->_data->mime_type = null;
		}
		return $this->_data;
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store($data)
	{
		$row =& $this->getTable();
		//This checks to see if the user has uploaded a file instead of just entered one in the box. It replaces the filename with the name of the uploaded file
		/*$file = JRequest::getVar('file', null, 'files', 'array' );
		 $filename_upload = strtolower($file['name']);
		 if (isset($filename_upload)){
			if (!$filename_upload) {
			$data['filename'] = $filename_upload;
			}
			}*/
		$data['mediacode'] = str_replace('"',"'",$data['mediacode']);
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
	function delete()
	{
		$cids = JRequest::getVar( 'cid', array(0), 'post', 'array' );

		$row =& $this->getTable();

		if (count( $cids ))
		{
			foreach($cids as $cid) {
				if (!$row->delete( $cid )) {
					$this->setError( $row->getErrorMsg() );
					return false;
				}
			}
		}
		return true;
	}
	function publish($cid = array(), $publish = 1)
	{

		if (count( $cid ))
		{
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__bsms_mediafiles'
			. ' SET published = ' . intval( $publish )
			. ' WHERE id IN ( '.$cids.' )'
				
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
	}
	/**
	 * Method to move a mediafile listing
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function move($direction)
	{
		$row =& $this->getTable();
		if (!$row->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move( $direction, ' study_id = '.(int) $row->study_id.' AND published >= 0 ' )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to move a mediafile listing
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($cid = array(), $order)
	{
		$row =& $this->getTable();
		$groupings = array();

		// update ordering values
		for( $i=0; $i < count($cid); $i++ )
		{
			$row->load( (int) $cid[$i] );
			// track categories
			$groupings[] = $row->study_id;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		// execute updateOrder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder('study_id = '.(int) $group);
		}

		return true;
	}
}
?>

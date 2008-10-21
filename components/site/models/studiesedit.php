<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');


class biblestudyModelstudiesedit extends JModel
{
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}

	
	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}


	
	function &getData()
	{
		// Load the data
		if (empty( $this->_data )) {
			$query = ' SELECT * FROM #__bsms_studies '.
					'  WHERE id = '.$this->_id;
			$this->_db->setQuery( $query );
			$this->_data = $this->_db->loadObject();
		}
		if (!$this->_data) {
			$this->_data = new stdClass();
			$this->_data->id = 0;
			//TF added these
			$this->_data->published = 1;
			$this->_data->studydate = null;
			$this->_data->teacher_id = null;
			$this->_data->studynumber = null;
			$this->_data->booknumber = null;
			$this->_data->chapter_begin = null;
			$this->_data->chapter_end = null;
			$this->_data->verse_begin = null;
			$this->_data->verse_end = null;
			$this->_data->studytitle = null;
			$this->_data->studyintro = null;
			$this->_data->media_hours = null;
			$this->_data->media_minutes = null;
			$this->_data->media_seconds = null;
			$this->_data->messagetype = null;
			$this->_data->series_id = null;
			$this->_data->studytext = null;
			$this->_data->topics_id = null;
			$this->_data->secondary_reference = null;
			$this->_data->prod_cd = null;
			$this->_data->prod_dvd = null;
			$this->_data->server_cd = null;
			$this->_data->server_dvd = null;
			$this->_data->image_cd = null;
			$this->_data->image_dvd = null;
			$this->_data->booknumber2 = null;
			$this->_data->chapter_begin2 = null;
			$this->_data->chapter_end2 = null;
			$this->_data->verse_begin2 = null;
			$this->_data->verse_end2 = null;
			$this->_data->comments = null;
			$this->_data->hits = null;
			$this->_data->user_id = null;
			$this->_data->user_name = null;
			$this->_data->show_level = null;
		}
		return $this->_data;
	}

	/**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store()
	{
		//$post           = JRequest::get( 'post' );

		// fix up special html fields
		
		$row =& $this->getTable();

		$data = JRequest::get( 'post' );
		//Allows HTML content to come through to the database row
		$data['studytext'] = JRequest::getVar( 'studytext', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$data['studyintro'] = str_replace('"',"'",$data['studyintro']);
		$data['studynumber'] = str_replace('"',"'",$data['studynumber']);
		$data['secondary_reference'] = str_replace('"',"'",$data['secondary_reference']);
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
			$row->studydate = date( 'Y-m-d H:i:s' );
		//if ($row->description) { $row->description = str_replace('"',"'",$row->description); }
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
			//$this->setError( $row->getErrorMsg() );
			//return false;
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

			$query = 'UPDATE #__bsms_studies'
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

}
?>

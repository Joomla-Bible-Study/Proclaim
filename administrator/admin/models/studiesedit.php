<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class biblestudyModelstudiesedit extends JModel {
	/**
	 * Constructor that retrieves the ID from the request
	 *
	 * @access	public
	 * @return	void
	 */
	 
     	protected function populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		

		$state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $state);

	

		$language = $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_biblestudy');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('studytitle', 'asc');
	}
     var $_admin;
	 
	function __construct() {
		parent::__construct();

		$admin = $this->getAdmin();
		$this->_admin_params = new JParameter($admin[0]->params);
		$array = JRequest::getVar('cid',  0, '', 'array');
		$this->setId((int)$array[0]);
	}


	function setId($id)
	{
		// Set id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
		$this->_admin = null;
	}



	function &getData()
	{
		// Load the data
		$admin = $this->getAdmin();
		//dump ($admin, 'admin: ');
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
			$today = date("Y-m-d H:i:s");
			$this->_data->studydate = $today;
			$this->_data->teacher_id = ($this->_admin_params->get('teacher_id') > 0 ? $this->_admin_params->get('teacher_id') : null);
			$this->_data->studynumber = null;
			$this->_data->booknumber = ($this->_admin_params->get('booknumber') > 0 ? $this->_admin_params->get('booknumber') : null);
			$this->_data->chapter_begin = null;
			$this->_data->chapter_end = null;
			$this->_data->verse_begin = null;
			$this->_data->verse_end = null;
			$this->_data->studytitle = null;
			$this->_data->studyintro = null;
			$this->_data->media_hours = null;
			$this->_data->media_minutes = null;
			$this->_data->media_seconds = null;
			$this->_data->messagetype = ($this->_admin_params->get('messagetype') > 0 ? $this->_admin_params->get('messagetype') : null);
			$this->_data->series_id = ($this->_admin_params->get('series_id') > 0 ? $this->_admin_params->get('series_id') : null);
			$this->_data->studytext = null;
			$this->_data->topics_id = ($this->_admin_params->get('topic_id') > 0 ? $this->_admin_params->get('topic_id') : null);
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
			$this->_data->comments = 1;
			$this->_data->hits = null;
			$this->_data->user_id = null;
			$this->_data->user_name = null;
			$this->_data->show_level = null;
			$this->_data->location_id = ($this->_admin_params->get('location_id') > 0 ? $this->_admin_params->get('location_id') : null);
			$this->_data->thumbnailm = ($admin[0]->study != '- JBS_CMN_NO_IMAGE -' ? $admin[0]->study : null);   // 2010-11-12 santon: need to be changed
			//$this->_data->thumbnailm = null;
			$this->_data->thumbhm = null;
			$this->_data->thumbwm = null;
			$this->_data->params = null;
			//dump ($this->_data);
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
   		$data['studytext'] = JRequest::getVar( 'studytext', '', 'post', 'string', JREQUEST_ALLOWRAW );
   		
   	//	$msg = $data['show_level'];

		foreach($data['scripture'] as $scripture) {
			if(!$data['text'][key($data['scripture'])] == ''){
				$scriptures[] = $scripture.' '.$data['text'][key($data['scripture'])];
			}
			next($data['scripture']);
		}
		$data['scripture'] = implode(';', $scriptures);
        // Added since Joomla 1.6 to implode show_level if array
        if (is_array($data['show_level']))
        {
            $data['show_level'] = implode(",", $data['show_level']);
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
		$row->studydate = date( 'Y-m-d H:i:s' );
		//if ($row->description) { $row->description = str_replace('"',"'",$row->description); }
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
			//$this->setError( $row->getErrorMsg() );
			//return false;
		}

        //Get Tags
   		$vTags = JRequest::getVar( 'topic_tags', '', 'post', 'string', JREQUEST_ALLOWRAW );
   		$iTags = explode(",", $vTags);
        
        JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_biblestudy'.DS.'tables');
        
        //$tagRow->load( 1 );
            
        foreach ($iTags as $aTag) {
            if (is_numeric($aTag)) {
                //It's an existing tag.  Add it
                if ($aTag != "") {
                
                    $tagRow =& JTable::getInstance('studytopics', 'Table');
                    
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
                $topicRow =& JTable::getInstance('topicsedit', 'Table');
                $tempText = $aTag;
                $tempText = str_replace("0_", "", $tempText);
                $topicRow->topic_text = $tempText;
                $topicRow->published = 1;
                if (!$topicRow->store()) {
		            $this->setError($this->_db->getErrorMsg());
		            return false;
	            }
                
                //Gotta somehow make sure this isn't a duplicate...
                $tagRow =& JTable::getInstance('studytopics', 'Table');
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
	
    function isDuplicate($study_id, $topic_id)
    {
        $db	=& JFactory::getDBO();
        $query = 'select * from #__bsms_studytopics where study_id = '.$study_id.' and topic_id = '.$topic_id;
    		
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

	function getBooks() {
		$query = 'SELECT booknumber AS value, bookname AS text'
		. ' FROM #__bsms_books'
		. ' WHERE published = 1'
		. ' ORDER BY booknumber';
		$this->_db->setQuery($query);
		return $this->_getList($query);
	}
	function getAdmin()
	{
		if (empty($this->_admin)) {
			$query = 'SELECT *'
			. ' FROM #__bsms_admin'
			. ' WHERE id = 1';
			$this->_admin = $this->_getList($query);
		}
		return $this->_admin;
	}
	
}
?>

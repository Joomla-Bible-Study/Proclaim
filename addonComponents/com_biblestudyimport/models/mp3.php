<?php
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class biblestudyimportModelmp3 extends JModel {
	var $db;
	var $id3Sample;
	var $id3Data;
	
	function __construct() {
		parent::__construct();
		$this->db =& $this->getDBO();
	}
	
	function getTeachers() {
		$query = 'SELECT DISTINCT id, teachername FROM #__bsms_teachers';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}
	
	function getLocations() {
		$query = 'SELECT DISTINCT id, location_text FROM #__bsms_locations';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();	
	}
	
	function getSeries() {
		$query = 'SELECT DISTINCT id, series_text FROM #__bsms_series';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();			
	}
	
	function getTopics() {
		$query = 'SELECT DISTINCT id, topic_text FROM #__bsms_topics';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();			
	}
	
	function getTypes() {
		$query = 'SELECT DISTINCT id, message_type FROM #__bsms_message_type';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();		
	}
	
	function getServers() {
		$query = 'SELECT DISTINCT id, server_name FROM #__bsms_servers';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}
	
	function getFolders() {
		$query = 'SELECT DISTINCT id, foldername FROM #__bsms_folders';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}
	
	function getPodcast() {
		$query = 'SELECT DISTINCT id, title FROM #__bsms_podcast';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();
	}
	
	function getMimeTypes() {
		$query = 'SELECT DISTINCT id, mimetext FROM #__bsms_mimetype';
		$this->db->setQuery($query);
		return $this->db->loadAssocList();	
	}
	
	//MP3 Processing
	
	function getId3Info() {
		if(isset($this->id3Data)) {
			return $this->id3Data;
		}
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'classes'.DS.'getid3.php');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		
		
		$id3 = new getID3();
		$files = new JFolder();
		$ext = new JFile();
		
		$folder = JRequest::getVar('directoryname');
		
		$files = $files->files($folder, '.', true, true);
		$allowedExtensions = array('mp3');
		if($files != false) {
			foreach($files as $file) {	
				if(in_array($ext->getExt($file), $allowedExtensions)) { 
					$analysis = $id3->analyze($file);
					 if(!isset($id3Sample)) {
						$this->id3Sample = $analysis;
					 }
					 $rows[] = $analysis;
				}
			}		
		}
		$this->id3Data = $rows;
		return $this->id3Data;		
	}
	
	function getId3Sample() {
		$this->getId3Info();
		return $this->id3Sample;
	}
}
?>
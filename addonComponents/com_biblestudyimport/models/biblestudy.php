<?php
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

class biblestudyimportModelbiblestudy extends JModel {
	var $db;
	var $studyId;
	var $teacherId;
	var $locationId; 
	var $seriesId;
	var $topicId;
	var $messageType;
	var $serverId;
	var $folderId;
	var $podcastId;
	var $mimeTypeId;
	
	
	function __construct() {
		parent::__construct();
		$this->db = $this->getDBO();
	}
	
	function addMediaFile($media) {
		$media->study_id = $this->studyId;
		$media->server = $this->serverId;
		$media->path = $this->folderId;
		$media->mime_type = $this->mimeTypeId;
		$media->podcast_id = $this->podcastId;
		$this->db->insertObject("#__bsms_mediafiles", $media);
	}
	
	function addStudy($study) {
		$study->teacher_id = $this->teacherId;
		$study->series_id = $this->seriesId;
		$study->topics_id = $this->topicId;
		$study->messagetype = $this->messageType;
		$study->location_id = $this->locationId;
		
		$this->db->insertObject("#__bsms_studies", $study);
		$this->studyId = $this->db->insertid();
	}
	
	function addTeacher($teacher) {
		if($this->_exists("bsms_teachers", "teachername", $teacher, $this->teacherId)) {
			return;
		}
		$this->db->insertObject("#__bsms_teachers", $teacher);
		$this->teacherId = $this->db->insertid();
	}
	
	function addLocation($location) {
		if($this->_exists("bsms_locations", "location_text", $location, $this->locationId)) {
			return;
		}		
		$this->db->insertObject("#__bsms_locations", $location);
		$this->locationId = $this->db->insertid();	
	}
	
	function addSeries($series) {
		if($this->_exists("bsms_series", "series_text", $series, $this->seriesId)) {
			return;
		}	
		$this->db->insertObject("#__bsms_series", $series);
		$this->seriesId = $this->db->insertid();		
	}
	
	function addTopic($topic) {
		if($this->_exists("bsms_topics", "topic_text", $topic, $this->topicId)) {
			return;
		}	
		$this->db->insertObject("#__bsms_topics", $topic);
		$this->topicId = $this->db->insertid();		
	}
	
	function addType($type) {
		if($this->_exists("bsms_message_type", "message_type", $type, $this->messageType)) {
			return;
		}
		$this->db->insertObject("#__bsms_message_type", $type);
		$this->messageType = $this->db->insertid();			
	}
	
	function addServer($server) {
		if($this->_exists("bsms_servers", "server_name", $server, $this->serverId)) {
			return;
		}
		$this->db->insertObject("#__bsms_servers", $server);
		$this->serverId = $this->db->insertid();				
	}
	
	function addFolder($folder) {
		if($this->_exists("bsms_folders", "foldername", $folder, $this->folderId)) {
			return;
		}
		$this->db->insertObject("#__bsms_folders", $folder);
		$this->folderId = $this->db->insertid();				
	}
	
	function addPodcast($podcast) {
		if($this->_exists("bsms_podcast", "title", $podcast, $this->podcastId)) {
			return;
		}
		$this->db->insertObject("#__bsms_podcast", $podcast);
		$this->podcastId = $this->db->insertid();				
	}
	
	function addMimeType($mimeType) {
		if($this->_exists("bsms_mimetype", "mimetype", $mimeType, $this->mimeTypeId)) {
			return;
		}
		$this->db->insertObject("#__bsms_mimetype", $mimeType);
		$this->mimeTypeId = $this->db->insertid();				
	}
	
	function getServer() {
		$query = "SELECT server_path FROM #__bsms_servers WHERE id = $this->serverId";
		$this->db->setQuery($query);
		return $this->db->loadObject();
	}
	
	function getFolder() {
		$query = "SELECT folderpath FROM #__bsms_folders WHERE id = $this->folderId";
		$this->db->setQuery($query);
		return $this->db->loadObject();		
	}
	
	function _exists($table, $field, &$objectRef, &$storeId) {
		$query = "SELECT id, $field FROM #__$table WHERE $field = '".$objectRef->$field."'";
		$this->db->setQuery($query, 0, 1);
		
		if($this->db->loadObject() == null) {
			return false;
		}else{
			$storeId = $this->db->loadObject()->id;
			return true;
		}
	}
}
?>
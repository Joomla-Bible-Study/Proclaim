<?php
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.model');

class biblestudyModelTemplateslist extends JModel {
	
	var $_templates;
	
	function getTemplates() {
		if(empty($this->_templates)) {
			$query = 'SELECT * FROM #__bsms_templates';
			$this->_templates = $this->_getList($query);
		}
		return $this->_templates;
	}
}
?>
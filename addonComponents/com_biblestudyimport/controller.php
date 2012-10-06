<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.controller');

class biblestudyImportController extends JController {
	function display() {
		$type = JRequest::getVar('view');
		if (!$type){
			JRequest::setVar('view', 'mp3');
		}
		parent::display();
	}
}
?>
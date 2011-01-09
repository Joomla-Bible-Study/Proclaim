<?php

/**
 * @version     $Id$
 * @package     com_biblestudy
 * @license     GNU/GPL
 */
//No Direct Access
defined('_JEXEC') or die();


//Joomla 1.6 <-> 1.5 Branch
try {
    jimport('joomla.application.component.modellist');

    abstract class modelClass extends JModelList {

    }

} catch (Exception $e) {
    jimport('joomla.application.component.model');

    abstract class modelClass extends JModel {

    }

}

class biblestudyModelTemplateslist extends modelClass {
	
	var $_templates;
	
	function getTemplates() {
		if(empty($this->_templates)) {
			$query = 'SELECT * FROM #__bsms_templates ORDER BY id ASC';
			$this->_templates = $this->_getList($query);
		}
		return $this->_templates;
	}
}
?>
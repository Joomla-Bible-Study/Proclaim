<?php
defined('_JEXEC') or dieI('Restricted Access');

class Tabletemplateedit extends JTable {
	
	var $id = null;
	var $type = null;
	var $tmpl = null;
	var $published = 1;
	
	function Tabletemplateedit(&$db) {
		parent::__construct('#__bsms_templates', 'id', $db);
	}
}
?>
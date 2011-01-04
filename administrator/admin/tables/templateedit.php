<?php
defined('_JEXEC') or die('Restricted Access');

class Tabletemplateedit extends JTable {
	
	var $id = null;
	var $type = null;
	var $tmpl = null;
	var $published = 1;
	var $params = null;
	var $title = null;
	var $text = null;
	var $pdf = null;
	
	function Tabletemplateedit(&$db) { //dump ($array, 'array: ');
		parent::__construct('#__bsms_templates', 'id', $db);
	}
//Not sure if this function belongs here, but is designed to save the params to their row in the database
function bind($array, $ignore = '')
{ 
        if (key_exists( 'params', $array ) && is_array( $array['params'] ))
        {
                $registry = new JRegistry();
                $registry->loadArray($array['params']);
                $array['params'] = $registry->toString();
        }
        return parent::bind($array, $ignore);
}

}
?>
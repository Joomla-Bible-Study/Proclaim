<?php
defined('JPATH_BASE') or die();

class JElementEditor extends JElement
{
	var $_name = 'editor';
	
	function fetchElement($name, $value, &$node, $control_name)
	{
		$value = str_replace('<br />', "\n", $value);
		$editor =& JFactory::getEditor();
		return $editor->display( $control_name.'['.$name.']', 
			$value, '80%', '400', '40', '15', null ) ;
	}
}

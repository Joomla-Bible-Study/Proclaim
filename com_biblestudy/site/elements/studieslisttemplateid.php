<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementstudieslisttemplateid extends JElement
{
	var	$_name = 'studieslisttemplateid';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT m.id, CONCAT(m.id,' - ',m.title) AS text"
		. "\n FROM #__bsms_templates AS m"
		. "\n ORDER BY m.title ASC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_LIST_TEMPLATE'), 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}

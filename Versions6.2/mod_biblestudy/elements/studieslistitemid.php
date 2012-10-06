<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementstudieslistitemid extends JElement
{
	var	$_name = 'studieslistitemid';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT m.id, CONCAT(m.id,' - ',m.name) AS text, m.link"
		. "\n FROM #__menu AS m"
		. "\n WHERE m.link LIKE '%studieslist%'"
		. "\n ORDER BY m.name ASC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('Select a Menu Item').' -', 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}

<?php
defined('_JEXEC') or die();

class JElementtemplate extends JElement {

	function fetchElement($name, $value, &$node, $control_name) {
		$db = &JFactory::getDBO();
		$language =& JFactory::getLanguage();
		$language->load('com_biblestudy');

		$query = 'SELECT * FROM #__bsms_templates'.
				' WHERE #__bsms_templates.published = 1'.
				' AND #__bsms_templates.type = \''.$node->_attributes['name'].'\'';
		$db->setQuery($query);
		$options = $db->loadObjectList();
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_SELECT_TEMPLATE').' -', 'id', 'id'));

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'id', $value, $control_name.$name );
	}
}
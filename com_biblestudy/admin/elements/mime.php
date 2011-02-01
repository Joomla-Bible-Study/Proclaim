<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementmime extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'mime';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT t.id, CONCAT(t.mimetype, ' - ', t.mimetext) AS text, t.published"
		. "\n FROM #__bsms_mimetype AS t"
		. "\n WHERE t.published = 1"
		. "\n ORDER BY t.mimetext ASC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', '- '.JText::_('JBS_CMN_SELECT_MIME_TYPE').' -', 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}
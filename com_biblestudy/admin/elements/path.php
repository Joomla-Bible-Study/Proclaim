<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class JElementpath extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'path';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		$query = "SELECT t.id, CONCAT(t.foldername, ' - ', t.folderpath) AS text, t.published"
		. "\n FROM #__bsms_folders AS t"
		. "\n WHERE t.published = 1"
		. "\n ORDER BY t.foldername ASC"
		;
		$db->setQuery( $query );
		$options = $db->loadObjectList( );
		array_unshift($options, JHTML::_('select.option', '0', JText::_('JBS_CMN_SELECT_FOLDER'), 'id', 'text'));
		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.']', 'class="inputbox"', 'id', 'text', $value, $control_name.$name );
	}
}